<?php declare(strict_types=1);

namespace AppBundle\Services;

use AppBundle\Entity\BodyProgress;
use AppBundle\Entity\ClientImage;
use AppBundle\Entity\DocumentClient;
use AppBundle\Entity\MasterMealPlan;
use AppBundle\Entity\ProgressFeedback;
use AppBundle\Entity\Payment;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\ClientStatus;
use AppBundle\Entity\VideoClient;
use AppBundle\Entity\WorkoutPlan;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DemoClientService
{
    public function __construct(
        private WorkoutPlanService $workoutPlanService,
        private ChatService $chatService,
        private MealPlanService $mealPlanService,
        private EntityManagerInterface $em,
        private ClientService $clientService,
        private UrlGeneratorInterface $router
    ) {}

    private function generateDemoClientEmail(User $user, int $i = 0): string
    {
        $service = $this->clientService;
        $email = $i == 0 ? explode('@', $user->getEmail())[0] . '@zenfitapp.com'
            : explode('@', $user->getEmail())[0] . $i . '@zenfitapp.com';

        try {
            $service->clientEmailExist($email);
            return $email;
        } catch (\Exception $e) {
            return $this->generateDemoClientEmail($user, ++$i);
        }
    }

    public function createDemoClient(User $user): void
    {
        $demoClient = $this->em->getRepository(Client::class)->findOneBy([
            'lasseDemoClient' => 1
        ]);

        if ($demoClient === null) {
            throw new \RuntimeException('No Lasse demo client in DB');
        }

        $email = $this->generateDemoClientEmail($user);

        $client = clone $demoClient;
        $client
            ->setUser($user)
            ->setEmail($email)
            ->setPassword('12345')
            ->setStartDate(new \DateTime('-1 month'))
            ->setLasseDemoClient(false)
            ->setBodyProgressUpdated(new \DateTime('now'))
            ->setEndDate(new \DateTime('+2 months'))
            ->setDemoClient(true);

        $this->em->persist($client);

        $this->createClientDemoWorkoutPlan($demoClient, $client, $user);
        $this->createClientDemoMealPlan($demoClient, $client, $user);
        $this->createClientDemoMessage($client, $user);

        $this->createClientDemoBodyProgress($demoClient, $client);
        $this->createClientDemoCheckIns($demoClient, $client);
        $this->createClientProgressPictures($demoClient, $client);
        $this->createClientDocuments($demoClient, $client);
        $this->createClientVideos($demoClient, $client);
        $this->createClientPayment($demoClient, $client);
        $this->createClientPaymentLogs($demoClient, $client);
        $this->createClientTasks($demoClient, $client);

        $this->em->flush();
    }

    private function createClientDemoWorkoutPlan(Client $demoClient, Client $client, User $user): void
    {
        $plans = $this->em->getRepository(WorkoutPlan::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var WorkoutPlan $plan */
        foreach ($plans as $plan) {
            $date = $this->getDate($latestDate, '-1 month');
            $wp = $this
                ->workoutPlanService
                ->setName($plan->getName())
                ->setExplaination($plan->getExplaination())
                ->setComment($plan->getComment())
                ->createPlan($user, $client, $plan);
            $wp->setCreatedAt($date);
            $latestDate = $date;
        }
    }

    private function createClientDemoMealPlan(Client $demoClient, Client $client, User $user): void
    {
        $service = $this->mealPlanService;
        $plans = $this->em->getRepository(MasterMealPlan::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var MasterMealPlan $plan */
        foreach ($plans as $plan) {
            $date = $this->getDate($latestDate, '-1 month');
            $mmp = $service->createMasterPlan(
                $plan->getName(),
                $plan->getExplaination(),
                $user,
                $client,
                $plan,
                [],
                $plan->getDesiredKcals(),
                null,
                $plan->getLocale(),
                $plan->getContainsAlternatives(),
                [],
                $plan->getType(),
                false
            );
            $mmp->setCreatedAt($date);
            $latestDate = $date;
        }
    }

    private function createClientDemoMessage(Client $client, User $user): void
    {
        $url = $this->router->generate('clientInfo', array('client' => $client->getId()));
        $service = $this->chatService;
        $conversation = $service->getConversation($client);

        //send message from demo client to trainer
        $msg = "Hi {$user->getFirstName()},
        <br />
        I'm Thor, your demo client 😎
        <br /><br />
        You can create workouts, meal plans and keep track of my body progress by <a href=$url>visiting my profile here.</a>
        <br /><br />
        If you want to see how it looks from my side, you can download the 'Zenfit - for clients' app on App store or Google Play, and login with:
        <br /><br />
        Email: {$client->getEmail()}
        <br />
        Password: 12345
        <br /><br />
        Have a great day,
        <br />
        Thor";
        $service->sendMessage($msg, $client, $user, $conversation);

        //send video message from trainer to client
        $video = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/trainers/video-messages/welcome-chat.mp4';
        $msg = "Hi {$client->getFirstName()},
        <br />
        Thanks for your message. I'm just gonna reply you back with a video message :)";

        $message = $service->sendMessage($msg, null, $user, $conversation)['msg'];
        $message->setVideo($video);
    }

    private function createClientDemoBodyProgress(Client $demoClient, Client $client): void
    {
        $bodyProgress = $this->em->getRepository(BodyProgress::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var BodyProgress $bp */
        foreach ($bodyProgress as $bp) {
            $newBp = clone $bp;
            $newBp->setClient($client);
            $date = $this->getDate($latestDate);
            $newBp->setDate($date);
            $this->em->persist($newBp);
            $latestDate = $date;
        }
    }

    private function createClientDemoCheckIns(Client $demoClient, Client $client): void
    {
        $checkIns = $this->em->getRepository(ProgressFeedback::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var ProgressFeedback $checkIn */
        foreach ($checkIns as $checkIn) {
            $newCheckIn = clone $checkIn;
            $newCheckIn->setClient($client);
            $date = $this->getDate($latestDate);
            $newCheckIn->setCreatedAt($date);
            $this->em->persist($newCheckIn);
            $latestDate = $date;
        }
    }

    private function createClientProgressPictures(Client $demoClient, Client $client): void
    {
        $images = $this->em->getRepository(ClientImage::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var ClientImage $image */
        foreach ($images as $image) {
            $newImg = clone $image;
            $newImg->setClient($client);
            $date = $this->getDate($latestDate);
            $newImg->setDate($date);
            $this->em->persist($newImg);
            $latestDate = $date;
        }
    }

    private function createClientVideos(Client $demoClient, Client $client): void
    {
        $entries = $this->em->getRepository(VideoClient::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        /** @var VideoClient $entry */
        foreach ($entries as $entry) {
            $newEntry = new VideoClient($entry->getVideo(), $client);
            $this->em->persist($newEntry);
        }
    }

    private function createClientDocuments(Client $demoClient, Client $client): void
    {
        $entries = $this->em->getRepository(DocumentClient::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        /** @var DocumentClient $entry */
        foreach ($entries as $entry) {
            $newEntry = new DocumentClient($entry->getDocument(), $client);
            $this->em->persist($newEntry);
        }
    }

    private function createClientTasks(Client $demoClient, Client $client): void
    {
        $clientStatus = $this->em->getRepository(ClientStatus::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        /** @var ClientStatus $cs */
        foreach ($clientStatus as $cs) {
            $newCs = clone $cs;
            $newCs->setClient($client);
            $this->em->persist($newCs);
        }
    }

    private function createClientPaymentLogs(Client $demoClient, Client $client): void
    {
        $logs = $this->em->getRepository(PaymentsLog::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        /** @var PaymentsLog $log */
        foreach ($logs as $log) {
            $newLog = clone $log;
            $newLog->setClient($client);
            $date = $this->getDate($latestDate, '-1 month');
            $this->em->persist($newLog);
            $newLog->setCreatedAt($date);
            $latestDate = $date;
        }
    }

    private function createClientPayment(Client $demoClient, Client $client): void
    {
        $payments = $this->em->getRepository(Payment::class)->findBy([
            'client' => $demoClient
        ], [
            'id' => 'DESC'
        ]);

        $latestDate = null;
        foreach ($payments as $payment) {
            $newPayment = clone $payment;
            $newPayment->setClient($client);
            $date = $this->getDate($latestDate, '-1 month');
            $this->em->persist($newPayment);
            $newPayment->setSentAt($date);
            $latestDate = $date;
        }
    }

    private function getDate(?\DateTime $date, string $modifier = '-1 week'): \DateTime
    {
        //if its the most recent entry, just return today's date
        //else return 1 modifier prior to the previous entry
        if ($date === null) {
            return new \DateTime('now');
        }

        $date = clone $date;

        return $date->modify($modifier);
    }
}
