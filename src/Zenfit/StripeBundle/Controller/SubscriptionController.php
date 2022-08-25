<?php

namespace Zenfit\StripeBundle\Controller;

use AppBundle\Security\CurrentUserFetcher;
use Carbon\Carbon;
use AppBundle\Controller\Controller;
use AppBundle\Services\UserSubscriptionService;
use AppBundle\Services\ErrorHandlerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Zenfit\StripeBundle\Exceptions\SubscriptionCreationFailed;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints as Assert;

class SubscriptionController extends Controller
{
    public function __construct(
        private UserSubscriptionService $userSubscriptionService,
        private ErrorHandlerService $errorHandlerService,
        private CurrentUserFetcher $currentUserFetcher,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($em, $tokenStorage);
    }

    public function initiateAction(Request $request): JsonResponse
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();
            $userSub = $user->getUserSubscription();

            $taxArray = [
                'create_tax_id' => false,
                'tax_exempt' => $request->request->get('tax_exempt')
            ];

            if ($request->request->get('tax_id') && $userSub->getSubscription()->getCountry() === 'eu') {
                $taxArray['tax_id'] = (string) $request->request->get('tax_id');
                $taxArray['create_tax_id'] = true;
            }

            //create customer + setupIntent
            $customer = $this
                ->userSubscriptionService
                ->setUser($user)
                ->createCustomer(
                    (string) $user->getName(),
                    $user->getEmail(),
                    $taxArray
                );

            $setupIntent = $this
                ->userSubscriptionService
                ->createIntent();

            return new JsonResponse([
                'client_secret' => $setupIntent->client_secret,
                'customer' => $customer->id
            ]);
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
            $user = $this->currentUserFetcher->getCurrentUser();
            $userSub = $user->getUserSubscription();

            if (!$userSub) {
                throw new BadRequestHttpException('You need a subscription in order to continue.');
            }

            $constraint = new Assert\Collection(array(
                'trial' => null,
                'payment_method' => null,
                'tax_rate' => null,
                'customer' => null
            ));

            $data = [
                'payment_method' => trim((string) $request->request->get('payment_method_id')),
                'trial' => trim((string) $request->request->get('trial')),
                'tax_rate' => trim((string) $request->request->get('tax_rate')),
                'customer' => trim((string) $request->request->get('customer'))
            ];

            $validator = Validation::createValidator();
            $violations = collect($validator->validate($data, $constraint))->map(function (ConstraintViolation $violation) {
                return [
                    'message' => $violation->getMessage(),
                    'path' => str_replace(['[', ']'], '', $violation->getPropertyPath()),
                ];
            });

            if ($violations->isNotEmpty()) {
                return new JsonResponse([
                    'violations' => $violations->toArray(),
                ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
            }

            $trialEnd = $data['trial'] ? Carbon::now()->addDays(7)->getTimestamp() : 'now';

            try {
                $subscription = $this
                    ->userSubscriptionService
                    ->setCustomer($data['customer'], $data['payment_method'])
                    ->setTrialEnd($trialEnd)
                    ->setPrice($userSub->getSubscription()->getStripeNameMonth())
                    ->subscribe($userSub);
                return new JsonResponse([
                    'status' => 'complete',
                    'id' => $subscription->id,
                    'customer' => $data['customer']
                ]);
            } catch (SubscriptionCreationFailed $e) {
                $subscription = $e->getSubscription();
                $clientSecret = $this->userSubscriptionService->getPaymentIntentClientSecretFromInvoice($subscription->latest_invoice);
                return new JsonResponse([
                    'status' => 'incomplete',
                    'clientSecret' => $clientSecret,
                    'id' => $subscription->id,
                    'customer' => $data['customer']
                ]);
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
        $user = $this->currentUserFetcher->getCurrentUser();

        $this
            ->userSubscriptionService
            ->updateUserDetails(
                $request->request->get('subscription'),
                $request->request->get('customer'),
                $user,
            );

        return new JsonResponse(['success' => true]);
    }
}
