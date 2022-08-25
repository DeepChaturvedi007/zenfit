<?php

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\ClientMacro;
use AppBundle\Entity\ClientSettings;
use AppBundle\Services\MyFitnessPalService;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;

class PopulateRecentWeekOfClientMacrosCommand extends CommandBase
{
    use LockableTrait;

    protected $input;
    protected $output;

    private MyFitnessPalService $myFitnessPalService;
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em, MyFitnessPalService $myFitnessPalService)
    {
        $this->myFitnessPalService = $myFitnessPalService;
        $this->em = $em;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:macros:populate:recent')
            ->setDescription('Populate client macros table');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }
        $em = $this->em;
        $repo = $em->getRepository(ClientSettings::class);
        $settings = $repo->getWithMfpAuthenticated();

        foreach ($settings as $setting) {
            $this->output->writeln('Updating data for the client #'.$setting->getClient()->getId());
            $updates = $this->getRecentUpdates($setting);
            $this->performStoringBySetting($setting, $updates);
        }

        $this->release();

        return 0;
    }

    protected function performStoringBySetting(ClientSettings $settings, array $updates)
    {
        $this->output->writeln('Storing...');
        $em = $this->em;
        $repo = $em->getRepository(ClientMacro::class);
        foreach ($updates as $update) {
            $date = Carbon::parse($update['date']);
            $macro = $repo->findByClientAndDate($settings->getClient(), $date);
            if(!$macro) {
                $macro = new ClientMacro($settings->getClient());
                $macro
                    ->setCarbs($update['carbs'])
                    ->setFat($update['fat'])
                    ->setKcal($update['kcal'])
                    ->setProtein($update['protein'])
                    ->setDate($date);
                $em->persist($macro);
                $em->flush();
            }
        }
    }

    protected function getRecentUpdates(ClientSettings $setting)
    {
        $dataList = [];
        $today = Carbon::now();
        $em = $this->em;
        $repo = $em->getRepository(ClientMacro::class);
        $mfpService = $this->myFitnessPalService;
        $mfpService->setClientSettings($setting);

        if (($setting->getMfpExpireDate()->getTimestamp() - (new \DateTime())->getTimestamp()) < MyFitnessPalService::REFRESH_TOKEN_THRESHOLD) {
            $mfpService->refreshToken();
        }

        for ($i = 1; $i <= 7; $i++) {
            try {
                /** @var Carbon $date */
                $date = $today->subDay();
                $macro = $repo->findByClientAndDate($setting->getClient(), $date);
                if ($macro) {
                    $this->output->writeln('Skip crawling for the date : ' . $date->format('Y-m-d'));
                    continue;
                }

                $dateStr = $date->format('Y-m-d');
                $this->output->writeln('Processing: ' . $dateStr);

                $meals = $mfpService->getDiaryMeals($date);

                if (empty($meals)) {
                    $this->output->writeln('No found data');
                    continue;
                }

                $data = [
                    'date' => $dateStr,
                    'kcal' => 0,
                    'carbs' => 0,
                    'fat' => 0,
                    'protein' => 0,
                ];

                foreach ($meals as $meal) {
                    $data['kcal'] += $meal['nutritional_contents']['energy']['value'] ?? 0;
                    $data['carbs'] += $meal['nutritional_contents']['carbohydrates'] ?? 0;
                    $data['fat'] += $meal['nutritional_contents']['fat'] ?? 0;
                    $data['protein'] += $meal['nutritional_contents']['protein'] ?? 0;
                }

                // Add data if at least one parameter greater that 0
                if ($data['kcal'] > 0 || $data['carbs'] > 0 || $data['fat'] > 0 || $data['protein'] > 0) {
                    $this->output->writeln('Found data');
                    $dataList[] = $data;
                } else {
                    $this->output->writeln('No found data');
                }
                $this->output->writeln('');
            }
            catch (\Exception $e) {
                $this->output->writeln($e->getMessage());
                $this->output->writeln($e->getTraceAsString());
            }
        }

        return array_reverse($dataList);
    }
}
