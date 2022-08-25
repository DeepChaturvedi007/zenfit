<?php

namespace AppBundle\Services;

use AppBundle\Entity\Client;
use AppBundle\Entity\MealPlan;
use AppBundle\Entity\User;
use AppBundle\Entity\Document;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Transformer\WorkoutDayTransformer;
use AppBundle\Transformer\Serializer\SimpleArraySerializer;
use Doctrine\ORM\EntityManagerInterface;
use League\Fractal;
use MealBundle\Helper\MealHelper;
use Stringy\StaticStringy;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class PdfService
{
    const TYPE_WORKOUT = 0;
    const TYPE_MEAL = 1;
    const TYPE_RECEIPT = 2;

    const V1 = 1;
    const V2 = 2;

    protected EntityManagerInterface $em;
    private Environment $view;
    protected AwsService $aws;
    private TrainerAssetsService $trainerAssetsService;
    private MealHelper $mealHelper;
    private TranslatorInterface $translator;
    private WorkoutPlanService $workoutPlanService;
    private ?Profiler $profiler = null;
    private string $s3documents;
    private string $appHostname;

    public function __construct(
        EntityManagerInterface $em,
        Environment $twig,
        TrainerAssetsService $trainerAssetsService,
        AwsService $aws,
        MealHelper $mealHelper,
        WorkoutPlanService $workoutPlanService,
        TranslatorInterface $translator,
        string $appHostname,
        string $s3documents
    ) {
        $this->em = $em;
        $this->view = $twig;
        $this->trainerAssetsService = $trainerAssetsService;
        $this->aws = $aws;
        $this->s3documents = $s3documents;
        $this->appHostname = $appHostname;
        $this->mealHelper = $mealHelper;
        $this->translator = $translator;
        $this->workoutPlanService = $workoutPlanService;
    }

    public function setProfiler(Profiler $profiler): void
    {
        $this->profiler = $profiler;
    }

    public function exportWorkout(WorkoutPlan $plan): string
    {
        $client = $plan->getClient();
        $prefix = $client ? $client->getId() : $plan->getId();
        $html = $this->renderWorkout($plan);

        return $this->compileWithWkHtmlToPdf((string) $prefix, $html);
    }

    public function exportMeal(MasterMealPlan $plan, int $version = 1): string
    {
        $client = $plan->getClient();
        $prefix = $client ? $client->getId() : $plan->getId();

        if ($version === 2) {
            $html = $this->renderMealV2($plan);

            return $this->compileWithWeasyPrint((string) $prefix, $html);
        }

        $html = $this->renderMealV1($plan);

        return $this->compileWithWkHtmlToPdf((string) $prefix, $html);
    }

    /** @param array<string, mixed> $fees */
    public function exportReceipt(array $fees, \DateTime $periodStart, \DateTime $periodEnd, User $user): string
    {
        $prefix = (new \DateTime('now'))->format('Y-m-d');
        $html = $this->renderReceipt($fees, $periodStart, $periodEnd, $user);
        return $this->compileWithWeasyPrint((string) $prefix, $html);
    }

    /**
     * @param string $content
     * @param User $user
     * @param string $name
     * @param string $comment
     * @param Client|null $client
     *
     * @return Document
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function uploadDocument($content, User $user, $name, $comment = '', Client $client = null)
    {
        $url = $this->uploadToAws($content);
        $document = new Document($name, $url);
        $document
            ->setUser($user)
            ->setComment($comment)
            ->setDeleted(true);

        /*
        if ($client) {
            $document->addClient($client);
        }*/

        $this->em->persist($document);
        $this->em->flush();

        return $document;
    }

    /**
     * @param Document $document
     * @return Response
     */
    public function downloadDocument(Document $document)
    {
        $content = (string) file_get_contents($document->getFileName());
        $name = trim($document->getName()) . '.pdf';
        $response = new Response($content);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            StaticStringy::toAscii($name)
        );

        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Type', 'application/download');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    public function renderMealV1(MasterMealPlan $plan): string
    {
        $this->disableProfiler();

        $plans = $this->em->getRepository(MealPlan::class)
            ->createQueryBuilder('mp')
            ->where('mp.masterMealPlan = :id')
            ->andWhere('mp.parent IS NULL')
            ->setParameter('id', $plan->getId())
            ->orderBy('mp.order', 'ASC')
            ->getQuery()
            ->getResult();

        $locale = $plan->getLocale();
        $updated = $plan->getLastUpdated();
        $user = $plan->getUser();

        return $this->view->render('@App/default/pdf/mealPlanPdf.html.twig',
            compact('plans', 'locale', 'user', 'updated')
        );
    }

    /** @param array<string, mixed> $connectFees */
    public function renderReceipt(array $connectFees, \DateTime $periodStart, \DateTime $periodEnd, User $user): string
    {
        $this->disableProfiler();

        list (
            $fees,
            $totalFees,
            $countFees,
            $currency,
            $totalRefunds,
            $countRefunds,
            $total
        ) = $this->parseConnectData($connectFees);

        $today = (new \DateTime('now'))->format('d F, Y');
        $periodStart = $periodStart->format('d F, Y');
        $periodEnd = $periodEnd->format('d F, Y');
        $isDK = $currency === 'dkk';
        $userSettings = $user->getUserSettings();
        $userSubscription = $user->getUserSubscription();
        $name = $userSettings !== null ? $userSettings->getCompanyName() : '';
        $email = $user->getEmail();
        $cvr = $userSubscription !== null ? $userSubscription->getVat() : '';
        $uniqid = uniqid();
        $receiptId = "ZF_{$uniqid}";
        $address = $userSettings !== null ? $userSettings->getAddress() : '';

        return $this->view->render('@App/default/pdf/receipt/body.html.twig',
            compact(
                'fees',
                'connectFees',
                'totalFees',
                'countFees',
                'totalRefunds',
                'countRefunds',
                'total',
                'today',
                'currency',
                'total',
                'periodStart',
                'periodEnd',
                'isDK',
                'name',
                'email',
                'cvr',
                'receiptId',
                'address'
            )
        );
    }

    /**
    * @param array<string, mixed> $connectFees
    * @return array<int, mixed>
    */
    private function parseConnectData(array $connectFees): array
    {
        $collection = collect($connectFees);

        $fees = collect($collection->get('fees'))->map(function($item) {
            $collection = collect($item);
            return [
                'connectFees' => $collection->sum('amount'),
                'commission' => $collection->sum('salesPersonCommission'),
                'count' => $collection->count(),
                'currency' => collect($collection->first())->get('currency')
            ];
        });

        $totalFees = $fees->reduce(function($carry, $item) {
            return $carry + $item['connectFees'];
        }, 0);

        $countFees = $fees->reduce(function($carry, $item) {
            return $carry + $item['count'];
        }, 0);

        $currency = collect($fees->first())->get('currency');

        $refunds = collect($collection->get('refunds'))->map(function($item) {
            $collection = collect($item);
            return [
                'connectFees' => $collection->sum('amount'),
                'commission' => $collection->sum('salesPersonCommission'),
                'count' => $collection->count()
            ];
        });

        $totalRefunds = $refunds->reduce(function($carry, $item) {
            return $carry + $item['connectFees'];
        }, 0);

        $countRefunds = $refunds->reduce(function($carry, $item) {
            return $carry + $item['count'];
        }, 0);

        $total = $totalFees - $totalRefunds;

        return [$fees, $totalFees, $countFees, $currency, $totalRefunds, $countRefunds, $total];
    }

    public function renderMealV2(MasterMealPlan $plan): string
    {
        $this->disableProfiler();
        $trainerAssets = $this->trainerAssetsService;

        $client = $plan->getClient();
        $user = $plan->getUser();
        $logo = $trainerAssets->getUserSettings($user)->getCompanyLogo();
        $colorPDF = $trainerAssets->getUserSettings($user)->getPrimaryColor();

        $mealHelper = $this->mealHelper;
        $mealPlans = $mealHelper->serializeMealPlans($plan);
        $meals = $mealHelper->transformParentMealPlans($mealPlans);

        $this->translator->setLocale($plan->getLocale());

        return $this->view->render('@App/default/pdf/meal_plan/body.html.twig',
            compact('client', 'plan', 'meals', 'logo', 'colorPDF')
        );
    }

    public function renderWorkout(WorkoutPlan $plan): string
    {
        $this->disableProfiler();

        $service = $this->workoutPlanService;
        $updated = $plan->isTemplate() ? null : $plan->getLastUpdated();
        $user = $plan->getUser();

        $workoutDays = $plan->getWorkoutDays();
        $days = [];

        if (count($workoutDays) > 0) {
            $workouts = $service->getWorkoutsByPlan($plan);

            $fractal = new Fractal\Manager();
            $resource = new Fractal\Resource\Collection($workoutDays, new WorkoutDayTransformer($workouts));

            $days = $fractal
                ->setSerializer(new SimpleArraySerializer)
                ->createData($resource)
                ->toArray();

            $days = array_filter($days, function ($day) {
                return count($day['workouts']) > 0;
            });
        }

        return $this->view->render('@App/default/pdf/workoutPlanPdf.html.twig',
            compact('days', 'user', 'updated', 'plan')
        );
    }

    /**
     * @param string $content
     *
     * @return string
     * @throws \Exception
     */
    public function uploadToAws($content)
    {
        $key = bin2hex(random_bytes(18)) . '.pdf';
        $contentType = 'application/pdf';
        $s3 = $this->aws->getClient();
        $s3->putObject([
            'Bucket' => 'zf-documents',
            'Key' => $key,
            'Body' => $content,
            'ContentType' => $contentType
        ]);

        return $this->s3documents . $key;
    }

    protected function compileWithWkHtmlToPdf(string $prefix, string $html): string
    {
        [$content, $output] = $this->createTempFiles($prefix, $html);

        $hostName = $this->appHostname;
        $footer = $hostName . '/pdf-footer.html';
        $header = $hostName . '/pdf-header.html';
        #$footer = 'https://app.zenfitapp.com/pdf-footer.html';
        #$header = 'https://app.zenfitapp.com/pdf-header.html';

        $process = Process::fromShellCommandline('cat $CONTENT | /usr/local/bin/wkhtmltopdf --load-error-handling ignore --footer-html $FOOTER --header-html $HEADER - $OUTPUT');
        $process->setTimeout(3600);
        $process->mustRun(null, ['CONTENT' => $content, 'FOOTER' => $footer, 'HEADER' => $header, 'OUTPUT' => $output]);

        $result = file_get_contents($output);
        if ($result === false) {
            throw new \RuntimeException();
        }

        return $result;
    }

    protected function compileWithWeasyPrint(string $prefix, string $html): string
    {
        [$content, $output] = $this->createTempFiles($prefix, $html);

        $output .= '.pdf';
        $process = Process::fromShellCommandline('export LC_ALL=en_US.UTF-8; export LANG=en_US.UTF-8; weasyprint ' . $content . ' ' . $output);
        $process->setTimeout(3600);
        $process->mustRun();

        $result = file_get_contents($output);
        if ($result === false) {
            throw new \RuntimeException();
        }

        return $result;
    }

    protected function createTempFiles(string $prefix, string $html): array
    {
        $name = $prefix . '_' . time();
        $content = tempnam('/tmp', $name);
        $output = tempnam('/tmp', $name);
        if ($content === false) {
            throw new \RuntimeException('Error while tmp filename generation');
        }

        $handle = fopen($content, 'w');
        if ($handle === false) {
            throw new \RuntimeException();
        }

        fwrite($handle, $html);
        fclose($handle);

        return [$content, $output];
    }

    public function disableProfiler(): void
    {
        if (isset($this->profiler)) {
            $this->profiler->disable();
        }
    }
}
