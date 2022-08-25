<?php

namespace AppBundle\Services;

use AppBundle\PlaceholderProviderInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Entity\DefaultMessage;
use Doctrine\ORM\EntityManagerInterface;
use ProgressBundle\Services\ClientProgressService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DefaultMessageService implements PlaceholderProviderInterface
{
    const PLACEHOLDER_CLIENT_NAME = 'client';
    const PLACEHOLDER_TRAINER_NAME = 'trainer';

    const DEFAULT_MESSAGE_CONFIG = [
        DefaultMessage::TYPE_PAYMENT_MESSAGE_EMAIL => [
            'title' => 'Client Payment Email #',
            'placeholders' => ['[checkout]']
        ],
        DefaultMessage::TYPE_WELCOME_EMAIL => [
            'title' => 'Client Welcome Email #',
            'placeholders' => ['[url]']
        ],
        DefaultMessage::TYPE_PLANS_READY_EMAIL => [
            'title' => 'Plans Ready Email #',
            'placeholders' => []
        ],
        DefaultMessage::TYPE_PDF_MEAL_PLANS_INTRO => [
            'title' => 'Meal Plan Intro #',
            'placeholders' => []
        ]
    ];

    private EntityManagerInterface $em;
    private ClientProgressService $clientProgressService;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $urlGenerator;
    private string $appHostname;

    public function __construct(
        TranslatorInterface $translator,
        EntityManagerInterface $em,
        ClientProgressService $clientProgressService,
        UrlGeneratorInterface $urlGenerator,
        string $appHostname
    ) {
        $this->translator = $translator;
        $this->em = $em;
        $this->clientProgressService = $clientProgressService;
        $this->urlGenerator = $urlGenerator;
        $this->appHostname = $appHostname;
    }

    public function getPlaceholderLabels(): array
    {
        return [
            self::PLACEHOLDER_CLIENT_NAME,
        ];
    }

    public function create(User $user, $message, $type, $title, $subject)
    {
        if (empty($title)) {
            $defaultMessages = $user
                ->getDefaultMessageByType($type);

            $count = 1;
            if ($defaultMessages !== null) {
                $count = collect($defaultMessages)->count() + 1;
            }

            $title = isset(self::DEFAULT_MESSAGE_CONFIG[$type]) ?
                self::DEFAULT_MESSAGE_CONFIG[$type]['title'] . $count :
                'Template #' . $count;
        }

        //validate messages to make sure they don't contain hardcoded values
        $requiredPlaceholders = isset(self::DEFAULT_MESSAGE_CONFIG[$type]) ?
            self::DEFAULT_MESSAGE_CONFIG[$type]['placeholders'] :
            [];

        foreach ($requiredPlaceholders as $requiredPlaceholder) {
            if (strpos($message, $requiredPlaceholder) === false) {
                throw new HttpException(422, 'The correct placeholders could not be identified. Zenfit has been notified.');
            }
        }

        if (strpos($message, 'http') !== false) {
            throw new HttpException(422, 'Your template contains a hard-coded link. Zenfit has been notified.');
        }

        $defaultMessage = (new DefaultMessage())
            ->setUser($user)
            ->setMessage($message)
            ->setType($type)
            ->setTitle($title)
            ->setSubject($subject);

        $this->em->persist($defaultMessage);
        $this->em->flush();

        return $defaultMessage;
    }

    public function update(User $user, DefaultMessage $defaultMessage, $message, $type, $title, $subject)
    {
        if ($defaultMessage->getUser() !== $user) {
            throw new AccessDeniedHttpException('Default message doesn\'t belong to current user');
        }

        $defaultMessage
            ->setUser($user)
            ->setMessage($message)
            ->setType($type)
            ->setTitle($title)
            ->setSubject($subject);

        $this->em->persist($defaultMessage);
        $this->em->flush();

        return $defaultMessage;
    }

    /** @param array<mixed> $placeholders */
    public function replaceMessageWithActualValues(string $message, array $placeholders = []): string
    {
        foreach ($placeholders as $placeholder => $val) {
            $message = str_replace($placeholder, $val, $message);
        }
        return $message;
    }

    /** @return array<string, mixed> */
    public function getPlaceholders(int $type, Client $client, ?string $datakey = null): array
    {
        $placeholders = [
            self::PLACEHOLDER_CLIENT_NAME => $client->getFirstName(),
            self::PLACEHOLDER_TRAINER_NAME => $client->getUser()->getTrainerName()
        ];

        switch ($type) {
            case DefaultMessage::TYPE_CHAT_MESSAGE_PROGRESS:
                $progress = $this
                    ->clientProgressService
                    ->setClient($client)
                    ->setProgressValues()
                    ->setUnits()
                    ->getProgressPlaceholders();

                $placeholders = array_merge($placeholders, $progress);
                break;
            case DefaultMessage::TYPE_PAYMENT_MESSAGE_EMAIL:
                $url = $this->appHostname . $this->urlGenerator->generate('zenfit_stripe_checkout', ['key' => $datakey]);
                $placeholder = ['checkout' => $url];
                $placeholders = array_merge($placeholders, $placeholder);
                break;
            case DefaultMessage::TYPE_WELCOME_EMAIL:
                $url = $this->appHostname . $this->urlGenerator->generate('clientActivation', ['datakey' => $datakey]);
                $placeholder = ['url' => $url];
                $placeholders = array_merge($placeholders, $placeholder);
                break;
            case DefaultMessage::TYPE_PAYMENT_FAILED_MESSAGE:
                $clientStripe = $client->getClientStripe();
                if ($clientStripe === null) {
                    throw new \RuntimeException('No ClientStripe');
                }
                $invoice = ['invoice' => $clientStripe->getInvoiceUrl()];
                $placeholders = array_merge($placeholders, $invoice);
                break;
        }

        return $placeholders;
    }

    public function getPlaceholderLabelsByType($type): array
    {
        $commonPlaceholders = $this->getPlaceholderLabels();

        $typePlaceholders = match ($type) {
            DefaultMessage::TYPE_CHAT_MESSAGE_PROGRESS => $this
                ->clientProgressService
                ->getPlaceholderLabels(),
            default => [],
        };


        $placeholders = array_merge($commonPlaceholders, $typePlaceholders);
        $placeholderLabels = [];
        foreach ($placeholders as $placeholder) {
            $placeholderLabels[$placeholder] = $this->translator->trans("defaultMessages.placeholders.{$placeholder}");
        }

        return $placeholderLabels;
    }
}
