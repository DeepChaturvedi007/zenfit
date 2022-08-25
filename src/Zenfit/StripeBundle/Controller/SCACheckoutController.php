<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\Lead;
use AppBundle\Entity\Queue;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Bundle as DocumentBundle;
use AppBundle\Repository\PaymentRepository;
use AppBundle\Services\BundleService;
use AppBundle\Services\ErrorHandlerService;
use AppBundle\Services\ClientService;
use AppBundle\Services\PaymentService;
use AppBundle\Services\QueueService;
use AppBundle\Services\StripeConnectService;
use AppBundle\Services\TrainerAssetsService;
use Doctrine\ORM\EntityManagerInterface;
use Carbon\Carbon;
use PlanBundle\Services\PlanService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenfit\StripeBundle\Exceptions\SubscriptionCreationFailed;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SCACheckoutController extends Controller
{
    public function __construct(
        private BundleService $bundleService,
        private ErrorHandlerService $errorHandlerService,
        private QueueService $queueService,
        private ClientService $clientService,
        private string $appHostname,
        private string $stripePublishableKey,
        private ?string $sentryDSN,
        string $env,
        private TranslatorInterface $translator,
        private UrlGeneratorInterface $urlGenerator,
        private StripeConnectService $stripeConnectService,
        private TrainerAssetsService $trainerAssetsService,
        private PaymentService $paymentService,
        private PlanService $planService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->sentryDSN = $env === 'prod' ? $sentryDSN : null;

        parent::__construct($em, $tokenStorage);
    }

    public function checkoutAction(Request $request, string $key): Response
    {
        /** @var PaymentRepository $paymentRepo */
        $paymentRepo = $this
            ->getEm()
            ->getRepository(Payment::class);

        $payment = $paymentRepo
            ->getPaymentByDatakey($key);

        if (!$payment) {
            $status = 'error';
            $message = 'No payment found';

            return $this->render('@ZenfitStripe/Checkout/success-checkout.html.twig', compact('message', 'status'));
        }

        $data = array_merge(
            $this->getCheckoutData($request, $payment, $payment->getClient()),
            [
                'datakey' => $payment->getDatakey(),
            ]
        );

        return $this->render('@ZenfitStripe/Checkout/checkout.html.twig', $data);
    }

    public function checkoutBundleAction(Request $request, DocumentBundle $bundle, ?Client $client = null): Response
    {
        $data = array_merge(
            $this->getCheckoutData($request, $bundle, $client),
            [
                'bundle' => $bundle->getId(),
            ]
        );

        return $this->render('@ZenfitStripe/Checkout/checkout.html.twig', $data);
    }

    public function initiateAction(Request $request): JsonResponse
    {
        $paymentType = $request->request->get('payment_type');

        try {
          $client = $this
              ->getEm()
              ->getRepository(Client::class)
              ->find($request->request->get('client'));

          if ($datakey = (string) $request->request->get('datakey')) {
              /** @var PaymentRepository $paymentRepo */
              $paymentRepo = $this
                  ->getEm()
                  ->getRepository(Payment::class);

              $payment = $paymentRepo
                  ->getPaymentByDatakey($datakey);
          } else {
              $bundle = $this
                  ->getEm()
                  ->getRepository(DocumentBundle::class)
                  ->find((int) $request->request->get('bundle'));

              if ($bundle === null) {
                  throw new NotFoundHttpException('Bundle not found');
              }

              $user = $bundle->getUser();
              if (!$client) {
                  $client = $this->clientService->addClient(
                      (string) $request->request->get('name'),
                      (string) $request->request->get('email'),
                      $user,
                      null,
                      true
                  );
              }

              $payment = $this
                  ->paymentService
                  ->generatePayment(
                      $client,
                      (string) $bundle->getCurrency(),
                      (float) $bundle->getUpfrontFee(),
                      (float) $bundle->getRecurringFee(),
                      (int) $bundle->getMonths()
                  );
          }

          if ($client === null) {
              throw new \RuntimeException('No client');
          }

          $user = $client->getUser();

          $userStripe = $user->getUserStripe();
          if ($userStripe === null) {
              throw new BadRequestHttpException('No user stripe found.');
          }

          if ($payment === null) {
              throw new \RuntimeException('No payment');
          }

          //create customer
          $customer = $this
              ->stripeConnectService
              ->setUserStripe($userStripe)
              ->setClient($client)
              ->setPayment($payment)
              ->createCustomer();

          $response = [
              'customer' => $customer->id,
              'client' => $client->getId(),
              'datakey' => $payment->getDatakey()
          ];

          $paymentTypes = ['card', 'sepa'];
          if (in_array($paymentType, $paymentTypes)) {
              $setupIntent = $this
                  ->stripeConnectService
                  ->createIntent();

              $response['client_secret'] = $setupIntent->client_secret;
          }

          return new JsonResponse($response);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function confirmAction(Request $request): JsonResponse
    {
        try {
            $datakey = (string) $request->request->get('datakey');
            $customer = $request->request->get('customer');
            $client = $request->request->get('client');
            $bundle = $request->request->get('bundle');
            $paymentMethod = (string) $request->request->get('payment_method_id');
            $source = (string) $request->request->get('source');
            $paymentType = $request->request->get('payment_type');

            $em = $this->getEm();
            $client = $em
                ->getRepository(Client::class)
                ->find($client);

            if ($client === null) {
                throw new NotFoundHttpException('Client not found');
            }

            $user = $client->getUser();

            $userStripe = $user->getUserStripe();
            if ($userStripe === null) {
                throw new BadRequestHttpException('No user stripe found.');
            }

            /** @var PaymentRepository $paymentRepository */
            $paymentRepository = $em->getRepository(Payment::class);
            $payment = $paymentRepository->getPaymentByDatakey($datakey);
            if ($payment === null) {
                throw new NotFoundHttpException('Could not find payment');
            }

            $service = $this
                ->stripeConnectService
                ->setUserStripe($userStripe);

            if ($coupon = (string) $request->request->get('coupon')) {
                $coupon = $service->retrieveCoupon($coupon);
            }

            $service
                ->setClient($client)
                ->setPayment($payment)
                ->setDefaultPaymentMethod($paymentMethod)
                ->setCustomer($customer);

            $response = [
                'datakey' => $datakey,
                'bundle' => $bundle
            ];

            if ($paymentType === 'klarna') {
                //payment is card or SEPA
                try {
                    $charge = $service->createCharge($source);
                    return new JsonResponse(array_merge([
                      'status' => 'complete'
                    ], $response));
                } catch (\Exception $e) {
                    return new JsonResponse(array_merge([
                      'status' => 'failed'
                    ], $response), 422);
                }
            } else {
                try {
                    $subscription = $service->initiateSubscription();
                    return new JsonResponse(array_merge([
                      'status' => 'complete',
                      'subscription' => $subscription->id
                    ], $response));
                } catch (SubscriptionCreationFailed $e) {
                    $subscription = $e->getSubscription();
                    $clientSecret = $service->getPaymentIntentClientSecretFromInvoice($subscription->latest_invoice);
                    return new JsonResponse(array_merge([
                      'status' => 'incomplete',
                      'clientSecret' => $clientSecret,
                      'id' => $subscription->id
                    ], $response));
                }
            }
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage()
                ],
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function confirmedAction(Request $request): JsonResponse
    {
        $datakey = (string) $request->request->get('datakey');
        $em = $this->getEm();

        /** @var PaymentRepository $paymentRepo */
        $paymentRepo = $em
            ->getRepository(Payment::class);

        $payment = $paymentRepo
            ->getPaymentByDatakey($datakey);

        if (!$payment) {
            return new JsonResponse([
                'error' => [
                    'message' => 'No payment found',
                ],
            ], JsonResponse::HTTP_NOT_FOUND);
        }

        if ($bundle = $request->request->get('bundle')) {
            $bundle = $em->getRepository(DocumentBundle::class)->find($bundle);
        }

        $client = $payment->getClient();
        $user = $client->getUser();

        $userStripe = $user->getUserStripe();
        if ($userStripe === null) {
            throw new BadRequestHttpException('No user stripe found.');
        }

        try {
            $this
                ->stripeConnectService
                ->setUserStripe($userStripe)
                ->setClient($client)
                ->setPayment($payment)
                ->handleClientPaymentSuccessful();

            if ($bundle) {
                $redirect = $this->appHostname .
                    $this->urlGenerator->generate('zenfit_stripe_checkout_success_bundle', [
                        'bundle' => $bundle->getId(),
                        'client' => $client->getId(),
                        'payment' => $payment->getId()
                    ]);

                $bundleService = $this->bundleService;

                if (!$bundle->getTrainerNeedsToCreate()) {
                    $trainerAssets = $this->trainerAssetsService;
                    $companyName = $trainerAssets->getUserSettings($user)->getCompanyName() ?? $user->getName();
                    $bundleService->sendPlansEmailToClient($companyName, $redirect, $client);
                }

                $bundleService->sendEmailToTrainerOfPurchase($client);
            } else {
                $this->configUponSuccessfulPayment($payment);
                $redirect = $this->urlGenerator->generate('zenfit_stripe_checkout_success', ['key' => $datakey]);
            }

            return new JsonResponse(compact('redirect'));
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'error' => [
                    'message' => $e->getMessage(),
                ],
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function successAction(string $key): Response
    {
        /** @var PaymentRepository $paymentRepo */
        $paymentRepo = $this
            ->getEm()
            ->getRepository(Payment::class);

        $payment = $paymentRepo
            ->getPaymentByDatakey($key, true);

        if (!$payment) {
            $status = 'error';
            $message = 'No payment found';

            return $this->render('@ZenfitStripe/Checkout/success-checkout.html.twig', compact('message', 'status'));
        }

        $trainerAssets = $this->trainerAssetsService;

        $client = $payment->getClient();
        $email = $client->getEmail();
        $companyLogo = $trainerAssets->getUserSettings($client->getUser())->getCompanyLogo();
        $activationUrl = null;

        if ($queue = $payment->getQueue()) {
            $activationUrl = $queue->getClientCreationLink($this->appHostname);
        }

        return $this->render('@ZenfitStripe/Checkout/success.html.twig', compact('email', 'companyLogo', 'activationUrl', 'client'));
    }

    public function successBundleAction(DocumentBundle $bundle, Client $client, Payment $payment): Response
    {
        $activationUrl = null;
        $trainerAssets = $this->trainerAssetsService;
        $em = $this->getEm();

        if ($bundle->getTrainerNeedsToCreate()) {
            $queue = $em->getRepository(Queue::class)->findOneBy([
                'client' => $client->getId(),
                'type' => Queue::TYPE_CLIENT_EMAIL,
            ]);

            if (!$queue) {
                /**
                 * @var Queue $queue
                 */
                $queueService = $this->queueService;
                $queue = $queueService->createClientCreationEmailQueueEntity($client, Queue::STATUS_CONFIRMED);

                if (!$client->getAnsweredQuestionnaire()) {
                    $queue->setSurvey(true);
                }

                $em->persist($queue);
                $em->flush();
            }

            $activationUrl = $queue->getClientCreationLink($this->appHostname);
        }

        $documents = $bundle->getDocuments();
        $companyLogo = $trainerAssets->getUserSettings($client->getUser())->getCompanyLogo();
        $trainer = $bundle->getUser()->getTrainerName();

        //enable client and create plan entity if client is not an online client
        if (DocumentBundle::TYPE_ONLINE_CLIENT === $bundle->getType()) {
            $client->setDeleted(false);
        } else {
            $client->setAccessApp(true);
            //create the plan entity
            $this->planService
                ->createPlan(
                    $client,
                    $bundle->getType(),
                    $bundle->getName(),
                    $bundle,
                    $payment
                );
        }

        $em->flush();

        return $this->render('@ZenfitStripe/Checkout/success.html.twig', compact('companyLogo', 'documents', 'trainer', 'activationUrl', 'client'));
    }

    private function configUponSuccessfulPayment(Payment $payment): void
    {
        $client = $payment->getClient();
        $client->setDeleted(false);
        $lead = $client->getLead();

        if ($lead) {
            $lead
                ->setStatus(Lead::LEAD_WON)
                ->setDeleted(false);
        }

        //send email to trainer
        $queueService = $this->queueService;
        $url =
            $this->appHostname .
            $this->urlGenerator->generate('clientInfo', ['client' => $client->getId()]);

        $currency = strtoupper($payment->getCurrency());

        $msg = "You've got a new paying client!<br /><br />
            Name: {$client->getName()}<br />
            Upfront: {$currency} {$payment->getUpfrontFee()}<br />
            Recurring: {$currency} {$payment->getRecurringFee()}<br />
            Duration: {$payment->getMonths()} months<br /><br />
            Click <a href=$url>here</a> to visit the client!";

        $queueService->sendEmailToTrainer(
            $msg,
            "You've got a new paying client!",
            $client->getUser()->getEmail(),
            $client->getUser()->getName()
        );
    }

    /** @return array<string, mixed> */
    private function getCheckoutData(Request $request, Payment|DocumentBundle $entity, Client $client = null): array
    {
        $trainerAssets = $this->trainerAssetsService;
        $clientId = $client ? $client->getId() : null;
        $name = $client ? $client->getName() : null;
        $email = $client ? $client->getEmail() : null;
        $currencySign = $this->getCurrencySign($entity->getCurrency());
        $currency = $entity->getCurrency();
        $upfrontFee = $entity->getUpfrontFee();
        $recurring = $entity->getRecurringFee();
        $checkoutTerms = $entity->getTerms();
        $translator = $this->translator;
        $trialEnd = null;
        $delayUpfront = false;
        $sentryDSN = $this->sentryDSN;

        $months = (int)$entity->getMonths();
        $period = $months === 13 ?
          $translator->trans('client.checkout.periodUntilUnsubscribe') :
          $translator->trans('client.checkout.periodFixed', ['%months%' => $months]);

        $stripeKey = $this->stripePublishableKey;

        if ($entity instanceof Payment) {
            $datakey = $entity->getDatakey();
            $type = 'regular';
            $user = $entity->getClient()->getUser();
            $trialEnd = $entity->getTrialEnd() ? (new \DateTime())->setTimestamp((int) $entity->getTrialEnd()) : null;
            $trialEnd = $trialEnd && $trialEnd->getTimestamp() > Carbon::now()->timestamp ? $trialEnd : null;
            $period = $trialEnd ? $period . " (Starting {$trialEnd->format('F j, Y')})" : $period;
            $delayUpfront = $entity->getDelayUpfront();
        } else {
            $datakey = null;
            $user = $entity->getUser();
            $type = 'bundle';
        }

        $terms = $this->trainerAssetsService->getUserTerms($user);
        $companyLogo = $trainerAssets->getUserSettings($user)->getCompanyLogo();
        $couponCode = $request->query->get('coupon');
        $token = $user->getInteractiveToken();
        $coupon = null;

        if ($user->getUserStripe() === null) {
            throw new BadRequestHttpException('No user stripe found.');
        }

        $userStripe = $user->getUserStripe();

        if ($couponCode) {
            $service = $this
                ->stripeConnectService
                ->setUserStripe($userStripe);

            $coupon = $service->retrieveCoupon($couponCode);

            if ($coupon) {
                $service->applyCoupon($coupon, $amount, $recurring, $upfrontFee);
            }
        }

        $recurring = (int)$recurring;
        $upfrontFee = (int)$upfrontFee;
        $amount = $delayUpfront ? 0 : $recurring + $upfrontFee;

        $stripeUserId = $userStripe->getStripeUserId();

        //sepa stuff
        $sepaEnabled = $userStripe->getSepaEnabled() && strtolower($currency) === 'eur';

        //klarna stuff (we don't support klarna for packages / bundles)
        $klarnaEnabled = $userStripe->getKlarnaEnabled() && $type === 'regular' && $months != 13;
        $klarnaCountry = $userStripe->getKlarnaCountry();
        $klarnaAmount = ((int) $recurring * $months + $upfrontFee) * 100;

        return compact(
            'companyLogo', 'coupon', 'couponCode', 'currencySign', 'datakey', 'terms', 'amount', 'klarnaAmount', 'period',
            'stripeKey', 'currency', 'upfrontFee', 'recurring', 'klarnaCountry', 'klarnaEnabled', 'sepaEnabled',
            'user', 'clientId', 'stripeUserId', 'type', 'checkoutTerms', 'token', 'name', 'email', 'sentryDSN'
        );
    }

    private function getCurrencySign(string $currency): string
    {
        $a = strtoupper($currency);
        $b = Currencies::getSymbol($a);

        return strtoupper($b) === $a ? '' : $b;
    }
}
