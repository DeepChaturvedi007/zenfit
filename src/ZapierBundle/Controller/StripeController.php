<?php

namespace ZapierBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Services\PaymentService;
use AppBundle\Services\StripeHook\PaymentSucceededHookService;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\ClientStripe;
use Carbon\Carbon;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/stripe")
 */
class StripeController extends Controller
{
    private EntityManagerInterface $em;
    private PaymentSucceededHookService $paymentSucceededHookService;
    private PaymentService $paymentService;

    public function __construct(
        PaymentService $paymentService,
        PaymentSucceededHookService $paymentSucceededHookService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->em = $em;
        $this->paymentService = $paymentService;
        $this->paymentSucceededHookService = $paymentSucceededHookService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Method({"POST"})
     * @Route("")
     */
    public function createSubscriptionAction(Request $request)
    {
        try {
            $user = $this->getUserFromRequest($request);
            $email = $request->request->get('email');
            $customer = (string) $request->request->get('customer');
            $subscription = $request->request->get('subscription');
            $currentPeriodStart = $request->request->get('currentPeriodStart');
            $currentPeriodEnd = $request->request->get('currentPeriodEnd');
            $months = $request->request->getInt('months');
            $currency = $request->request->get('currency');
            $amount = (float) ((float) $request->request->get('amount') / 100);

            // convert months into periodEnd timestamp
            $periodEnd = (string) Carbon::now()->addMonths($months)->timestamp;

            $client = $this
                ->em
                ->getRepository(Client::class)
                ->findOneBy([
                    'email' => $email,
                    'user' => $user
                ]);

            if ($client) {
                //create Payment entity
                $payment = $this
                    ->paymentService
                    ->generatePayment(
                      $client,
                      (string) $currency,
                      0,
                      (float) $amount,
                      (int) $months
                    )
                    ->setCharged(true);

                //create clientStripe entity
                $clientStripe = (new ClientStripe())
                    ->setClient($client)
                    ->setStripeCustomer($customer)
                    ->setStripeSubscription($subscription)
                    ->setCurrentPeriodEnd($currentPeriodEnd === null ? null : (string) $currentPeriodEnd)
                    ->setCurrentPeriodStart($currentPeriodStart === null ? null : (string) $currentPeriodStart)
                    ->setPeriodEnd($periodEnd)
                    ->setPayment($payment);

                $this->em->persist($clientStripe);
                $this->em->flush();

                //execute invoice.payment_succeeded event
                $this
                    ->paymentSucceededHookService
                    ->setStripeAccount('connect')
                    ->setType('invoice.payment_succeeded')
                    ->setCustomer($customer)
                    ->setAmount($amount)
                    ->setCurrency($currency)
                    ->setApplicationFee(0)
                    ->insert();
            }

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
