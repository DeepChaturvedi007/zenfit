<?php

namespace AppBundle\Consumer;

use AppBundle\Repository\MasterMealPlanRepository;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Services\ErrorHandlerService;
use AppBundle\Services\PdfService;
use AppBundle\Services\MailService;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class PdfGenerationHandler implements MessageHandlerInterface
{
    private LoggerInterface $logger;
    private PdfService $pdfService;
    private MailService $mailService;
    private TranslatorInterface $translator;
    private WorkoutPlanRepository $workoutPlanRepository;
    private MasterMealPlanRepository $masterMealPlanRepository;
    private string $mailerZfEmail;
    private string $mailerZfName;
    private ErrorHandlerService $errorHandlerService;

    public function __construct(
        TranslatorInterface $translator,
        string $mailerZfEmail,
        string $mailerZfName,
        PdfService $pdfService,
        ErrorHandlerService $errorHandlerService,
        WorkoutPlanRepository $workoutPlanRepository,
        MasterMealPlanRepository $masterMealPlanRepository,
        LoggerInterface $logger,
        MailService $mailService
    ) {
        $this->pdfService = $pdfService;
        $this->logger = $logger;
        $this->mailService = $mailService;
        $this->translator = $translator;
        $this->mailerZfName = $mailerZfName;
        $this->mailerZfEmail = $mailerZfEmail;
        $this->errorHandlerService = $errorHandlerService;
        $this->workoutPlanRepository = $workoutPlanRepository;
        $this->masterMealPlanRepository = $masterMealPlanRepository;
    }

    public function __invoke(PdfGenerationEvent $event): void
    {
        try {
            // Extract provided data
            $name       = $event->getName();
            $email      = $event->getClientEmail();
            $type       = $event->getType();
            $planId     = $event->getPlanId();
            $version    = $event->getVersion();

            $this->logger->info('!BODY!', (array) $event);
            $this->logger->info("Starting PDF generating for the {$type} plan (ID: {$planId})");

            $translator = $this->translator;

            switch ($type) {
                case PdfService::TYPE_MEAL: {
                    $plan = $this->masterMealPlanRepository->get($planId);
                    $output = $this->pdfService->exportMeal($plan, $version);

                    $client = $plan->getClient();
                    if ($client !== null && $client->getLocale() !== null) {
                        $locale = $client->getLocale();
                    } else {
                        $locale = 'en';
                    }

                    $content = $name ? $translator->trans('emails.client.mealPlanPdf.body', ['%client%' => $name], null, $locale) : 'Here you go :)';
                    $subject = $translator->trans('emails.client.mealPlanPdf.subject', [], null, $locale);
                    $this->sendEmail($output, $email, $subject, $content, 'meal_plan.pdf');
                    break;
                }
                case PdfService::TYPE_WORKOUT: {
                    $plan = $this->workoutPlanRepository->get($planId);
                    $client = $plan->getClient();
                    if ($client !== null && $client->getLocale() !== null) {
                        $locale = $client->getLocale();
                    } else {
                        $locale = 'en';
                    }
                    $output = $this->pdfService->exportWorkout($plan);

                    $content = $name ? $translator->trans('emails.client.workoutPlanPdf.body', ['%client%' => $name], null, $locale) : 'Here you go :)';
                    $subject = $translator->trans('emails.client.workoutPlanPdf.subject', [], null, $locale);
                    $this->sendEmail($output, $email, $subject, $content, 'workout_plan.pdf');
                    break;
                }
                default: {
                    $this->logError($event, "Unknown pdf type {$type}");
                    echo sprintf('ERROR: PDF Generation - Unknown pdf type, got:, type:%s, date:%s ...', $type, date('Y-m-d H:i:s')).PHP_EOL;
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            $this->logError($event, $e->getTraceAsString());
            echo sprintf('ERROR: PDF Generation - ERROR:, error:%s, date:%s ...', $e->getMessage(), date('Y-m-d H:i:s')).PHP_EOL;
        }

        echo sprintf('PDF Generation - plan:%s, type:%s, date:%s ...', $planId, $type, date('Y-m-d H:i:s')).PHP_EOL;
    }

    private function logError(PdfGenerationEvent $event, string $error): void
    {
        $data = [
            'error' => $error,
            'class' => __CLASS__,
            'event' => $event,
        ];

        $this->logger->error(json_encode($data, JSON_THROW_ON_ERROR));
    }

    private function sendEmail(string $attachment, string $to, string $subject, string $content, string $filename): void
    {
        $sgEmail = $this->mailService
            ->createPlainTextEmail(
                $to,
                $subject,
                $this->mailerZfEmail,
                $this->mailerZfName,
                null,
                $content,
                true
            );

        $sgEmail->addAttachment(
            base64_encode($attachment),
            'application/pdf',
            $filename,
            'attachment');

        $this->mailService->send(null, $sgEmail);
    }
}
