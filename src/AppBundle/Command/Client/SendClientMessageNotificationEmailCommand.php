<?php

namespace AppBundle\Command\Client;

use AppBundle\Command\CommandBase;
use AppBundle\Entity\Queue;
use AppBundle\Services\TrainerAssetsService;
use ChatBundle\Entity\Message;
use ChatBundle\Entity\Conversation;
use AppBundle\Services\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendClientMessageNotificationEmailCommand extends CommandBase
{
    public function __construct(
        private MailService $mailService,
        private UrlGeneratorInterface $urlGenerator,
        private LoggerInterface $logger,
        private EntityManagerInterface $em,
        private string $clientMessageNotificationEmailTemplate,
        private string $s3beforeAfterImages,
        private string $mailerZfEmail,
        private string $appHostname,
        private TrainerAssetsService $appTrainerAssets
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('zf:client:message:notification:send')
            ->setDescription('Notify client that trainer sent message.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
  	    $em = $this->em;

        $service = $this->mailService;
        $recipients = $service->getClientsThatShouldHaveMessageNotificationSent();

        $template = $this->clientMessageNotificationEmailTemplate;
  	    $s3beforeAfterImages = $this->s3beforeAfterImages;
        $zfMailerEmail = $this->mailerZfEmail;
        $appHostname = $this->appHostname;

        foreach($recipients as $recipient) {
            try {
                /** @var $recipient Queue */
                $client = $recipient->getClient();
                $user = $client->getUser();

                $conversation = $em->getRepository(Conversation::class)->findOneBy([
                    'client' => $client
                ]);
                $message = $em->getRepository(Message::class)->findOneBy([
                    'conversation' => $conversation
                ],[
                    'id' => 'DESC'
                ]);

                if ($message === null) {
                    throw new NotFoundHttpException();
                }

                $trainerName = $user->getTrainerName();
                $fromName = $user->getUserSettings() && $user->getUserSettings()->getCompanyName() ? $user->getUserSettings()->getCompanyName() : $trainerName;

                $trainerPicture = $this->appTrainerAssets->getUserSettings($user)->getProfilePicture();
                $img = $trainerPicture
                    ? " <div style='width: 40px;height: 40px;border-radius: 50%;overflow: hidden;float: left;margin-right: 10px;'>
    							<img style='width: 100%' src=\"{$s3beforeAfterImages}trainers/{$trainerPicture}\" alt=\"\">
    						</div>	"
                    : '';

                $authToken = $client->getToken();
                $deepLink = $appHostname . $this->urlGenerator->generate('interactiveLoginClient', array('token' => $authToken));

                $url = '<a
    						style=" background: #1786E8;
    								color: #fff;
    								margin-bottom: 10px;
    								text-decoration: none;
    								display: inline-block;
    								width: 150px;
    								text-align: center;
    								padding: 10px 0;
    								border-radius: 5px;"
    						href="'.$deepLink.'">Open App</a>';

                $subject = $fromName . ' sent you a message!';

                $messageContent = "
    					<div class='main-body' style='border: 1px solid #E5E4E5'>
    					    <div class=\"header\" style='background: #F4F3F4; margin: 0;padding: 20px; border-top-left-radius: 5px;	border-top-right-radius: 5px;'>
    								$img
    					        <h3 style='margin: 0;line-height: 1;'>
    								$fromName
    							</h3>
    					        <p style='margin: 0; color: #8C8C8C;'>
    								{$message->getSentAt()->format('F d \a\t g:i a')}
    							</p>
    					    </div>
    					    <div class=\"body\" style='padding: 20px; color: #8C8C8C;'>
    							{$message->getContent()}
    						</div>
    					</div>
    				";

                $parameters = [
                    '-trainername-' => $fromName,
                    '-messagefromtrainer-' => $messageContent,
                    '-url-' => $url
                ];

                $email = $service->createMailWithTemplate(
                    $recipient->getEmail(),
                    $template,
                    $subject,
                    $parameters,
                    $zfMailerEmail,
                    $fromName,
                    $recipient->getId()
                );

                $service->send($recipient, $email);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $recipient->setStatus(Queue::STATUS_ERROR);
                $this->em->flush();
                continue;
            }
        }

        return 0;
    }
}
