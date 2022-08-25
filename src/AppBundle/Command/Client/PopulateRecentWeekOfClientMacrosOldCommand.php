<?php

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\ClientMacro;
use AppBundle\Entity\ClientSettings;
use Carbon\Carbon;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\DomCrawler\Crawler;

class PopulateRecentWeekOfClientMacrosOldCommand extends CommandBase
{
    use LockableTrait;

    protected $input;
    protected $output;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:macros:populate:old:recent')
            ->setDescription('Populate client macros table (old)');
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
        $settings = $repo->getWithMfpUrl();

        /** @var ClientSettings $setting */
        foreach ($settings as $setting) {
            $this->output->writeln('Updating data for the client #'.$setting->getClient()->getId());
            $updates = $this->getRecentUpdates($setting);
            $this->performStoringBySetting($setting, $updates);
        }

        $this->release();

        return 0;
    }

    protected function performStoringBySetting (ClientSettings $settings, array $updates)
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

    protected function getRecentUpdates (ClientSettings $setting)
    {
        $dataList = [];
        $today = Carbon::now();
        $em = $this->em;
        $repo = $em->getRepository(ClientMacro::class);
        for ($i = 1; $i <= 7; $i++) {
            try {
                /** @var Carbon $date */
                $date = $today->subDay();
                $macro = $repo->findByClientAndDate($setting->getClient(), $date);
                if($macro) {
                    $this->output->writeln('Skip crawling for the date : ' . $date->format('Y-m-d'));
                    continue;
                }
                $date = $date->format('Y-m-d');
                $this->output->writeln('Processing: ' . $date);
                $data = [
                    'date' => $date
                ];
                $curl = curl_init($setting->getMfpUrl());
                if (!$curl instanceof \CurlHandle) {
                    throw new \RuntimeException('Could not init curl');
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);

                if($info['http_code'] !== 200) {
                    $this->output->writeln('No found data');
                    continue;
                }
                $this->output->writeln('Parsing...');
                $data = $this->parseDocument($response);
                $data['date'] = $date;
                // Add data if at least one parameter greater that 0
                if($data['kcal'] > 0 || $data['carbs'] > 0 || $data['fat'] > 0 || $data['protein'] > 0) {
                    $this->output->writeln('Found data');
                    $dataList[] = $data;
                } else {
                    $this->output->writeln('No found data');
                }
                $this->output->writeln('');
            }
            catch (ParsingException $e) {
                $this->output->writeln('ERROR: ' . $e->getMessage());
            }
            catch (\Exception $e) {
                $this->output->writeln($e->getMessage());
                $this->output->writeln($e->getTraceAsString());
            }
        }
        return array_reverse($dataList);
    }

    protected function parseDocument ($html)
    {
        try {
            $crawler = new Crawler($html);
            $totalKcal = $crawler->filter('body tr.total td')->eq(1)->text();
            $carbsVal = $crawler->filter('body tr.total td .macro-value')->eq(0)->text();
            $fatVal = $crawler->filter('body tr.total td .macro-value')->eq(1)->text();
            $proteinVal = $crawler->filter('body tr.total td .macro-value')->eq(2)->text();
            return [
                'kcal' => (int) str_replace(',', '', $totalKcal),
                'carbs' => (int) $carbsVal,
                'fat' => (int) $fatVal,
                'protein' => (int) $proteinVal
            ];
        } catch (\InvalidArgumentException $e) {
            if($e->getMessage() === 'The current node list is empty.') {
                throw new ParsingException('Unable to parse page');
            }
        }
    }
}
