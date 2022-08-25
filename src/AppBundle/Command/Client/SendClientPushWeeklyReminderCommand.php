<?php declare(strict_types=1);

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Client;
use AppBundle\Entity\PushMessage;
use AppBundle\Services\PushNotificationService;
use OneSignal\Exception\OneSignalExceptionInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\LockableTrait;
use AppBundle\PushMessages\PushNotificationServiceException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendClientPushWeeklyReminderCommand extends CommandBase
{
    use LockableTrait;

    private UrlGeneratorInterface $urlGenerator;
    private PushNotificationService $pushNotificationService;
    private EntityManagerInterface $em;
    private TranslatorInterface $translator;
    private string $appHostname;

    public function __construct(
        EntityManagerInterface $em,
        string $appHostname,
        UrlGeneratorInterface $urlGenerator,
        PushNotificationService $pushNotificationService,
        TranslatorInterface $translator
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->pushNotificationService = $pushNotificationService;
        $this->em = $em;
        $this->translator = $translator;
        $this->appHostname = $appHostname;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:push:weekly:reminder')
            ->setDescription('Notify client to track weekly progress');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('The command is already running in another process.');
            return 0;
        }

        $service = $this->pushNotificationService;
        $reminders = $this->getClientRemindersFor('now');

        $progress = new ProgressBar($output, $reminders->count());
        $progress::setFormatDefinition('custom', ' %current%/%max% -- %message%');
        $progress->setFormat('custom');
        $progress->start();

        $sleepRate = 1 * 1000000; // micro seconds

        foreach ($reminders as $reminder) {
            list($client, $pushMsg, $day) = $reminder;

            try {
                //send push
                /** @var Client $client */
                $service
                    ->sendToClient($client, $pushMsg, [], [], null, 'TrackProgressScreen');

                $progress->setMessage("Sending to {$client->getName()}...\n");
            } catch (PushNotificationServiceException $e) {
                echo$e->getMessage();
                $progress->setMessage($e->getMessage());
            } catch (OneSignalExceptionInterface $e) {
                echo$e->getMessage();
                $progress->setMessage($e->getMessage());
            } catch (\Exception $e) {
                echo$e->getMessage();
                $progress->setMessage($e->getMessage());
            }

            $progress->advance();
            usleep($sleepRate);
        }

        $progress->setMessage('Done');
        $progress->finish();
        $this->release();

        return 0;
    }

    /**
     * @param string $time
     * @return \Illuminate\Support\Collection
     */
    protected function getClientRemindersFor($time)
    {
        $date = new \DateTime($time);
        $service = $this->pushNotificationService;
        $day = $time === 'now' ? 'today' : $time;
        $translator = $this->translator;

        return collect($service->getClientsThatShouldReceiveWeeklyReminder((int) $date->format('N')))
            ->map(function (Client $client) use ($day, $translator) {
                if (method_exists($translator, 'setLocale')) {
                    $translator->setLocale($client->getLocale());
                }

                $pushMsg = $translator->trans('client.notifications.checkIn', [
                    '%name%' => $client->getFirstName()
                ]);
                return [$client, $pushMsg, $day];
            });
    }
}
