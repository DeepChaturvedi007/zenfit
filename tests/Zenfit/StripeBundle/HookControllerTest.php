<?php declare(strict_types=1);

namespace Tests\Zenfit\StripeBundle;

use AppBundle\Entity\ClientStripe;
use AppBundle\Entity\PaymentsLog;
use AppBundle\Entity\UserStripe;
use AppBundle\Entity\UserSubscription;
use Tests\Context\BaseWebTestCase;

class HookControllerTest extends BaseWebTestCase
{
    public function testPayoutPaid(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.paid',
        ]);

        $this->responseStatusShouldBe(500);

        $stripeCustomerId = uniqid();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.paid',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, $stripeCustomerId, uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $arrivalDate = new \DateTime();
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.paid',
            'account' => $stripeCustomerId,
            'data' => [
                'object' => [
                    'currency' => 'dkk',
                    'amount' => 869,
                    'arrival_date' => $arrivalDate->getTimestamp(),
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('8.69', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('payout.paid', $payments[0]->getType());
        self::assertEquals($user, $payments[0]->getUser());
    }

    public function testPayoutCreated(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.created',
        ]);

        $this->responseStatusShouldBe(500);

        $stripeCustomerId = uniqid();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.created',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, $stripeCustomerId, uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $arrivalDate = new \DateTime();
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'payout.created',
            'account' => $stripeCustomerId,
            'data' => [
                'object' => [
                    'currency' => 'dkk',
                    'amount' => 869,
                    'arrival_date' => $arrivalDate->getTimestamp(),
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('8.69', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('payout.created', $payments[0]->getType());
        self::assertEquals($user, $payments[0]->getUser());
    }

    public function testChargeRefunded(): void
    {
        $this->request('POST', '/hook/handle', [
            'type' => 'charge.refunded',
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle', [
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $this->request('POST', '/hook/handle', [
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'some' => 'some',
                    'customer' => $stripeCustomerId,
                    'currency' => 'dkk',
                    'amount_refunded' => 567,
                    'application_fee_amount' => 100
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('5.67', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('charge.refunded', $payments[0]->getType());
        self::assertEquals($stripeCustomerId, $payments[0]->getCustomer());
    }

    public function testCustomerSubscriptionDeleted(): void
    {
        $this->request('POST', '/hook/handle', [
            'type' => 'customer.subscription.deleted',
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle', [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $userSubscription = new UserSubscription($user);
        $userSubscription->setStripeCustomer($stripeCustomerId);
        $userSubscription->setCanceled(false);
        $this->em->persist($userSubscription);
        $this->em->flush();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'customer.subscription.deleted',
            'data' => [
                'object' => [
                    'some' => 'some',
                    'customer' => $stripeCustomerId,
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);

        $this->em->refresh($userSubscription);
        self::assertFalse($userSubscription->getCanceled());
    }

    public function testSubscriptionCreated(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'customer.subscription.created',
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'some' => 'some',
                    'customer' => $stripeCustomerId,
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);
    }

    public function testSubscriptionCreatedAndActivated(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();

        $userSubscription = new UserSubscription($user);
        $userSubscription->setStripeCustomer($stripeCustomerId);
        $user->setActivated(false);
        $this->em->persist($userSubscription);
        $this->em->flush();

        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();


        $this->request('POST', '/hook/handle', [
            'type' => 'customer.subscription.created',
            'data' => [
                'object' => [
                    'some' => 'some',
                    'customer' => $stripeCustomerId,
                ],
            ],
        ]);

        $this->em->refresh($userSubscription);

        $user = $userSubscription->getUser();
        self::assertNotNull($user);
        $this->em->refresh($user);
        self::assertTrue($user->getActivated());
    }

    public function testPaymentSucceeded(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_succeeded',
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'some' => 'some',
                ],
            ],
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_succeeded',
            'data' => [
                'object' => [
                    'customer' => $stripeCustomerId,
                    'amount_paid' => 567,
                    'application_fee_amount' => 123,
                    'currency' => 'dkk',
                    'charge' => null,
                ],
            ],
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('5.67', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('invoice.payment_succeeded', $payments[0]->getType());
        self::assertEquals($stripeCustomerId, $payments[0]->getCustomer());

        $this->em->refresh($userStripe);
        self::assertEquals(1.23, $userStripe->getApplicationFeeRequired());
    }

    public function testPaymentFailed(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_failed',
        ]);

        $this->responseStatusShouldBe(500);

        $stripeCustomerId = uniqid();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'customer' => $stripeCustomerId
                ],
            ]
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'customer' => $stripeCustomerId,
                    'amount_due' => 123,
                ],
            ]
        ]);

        $this->responseStatusShouldBe(500);


        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $invoiceUrl = uniqid();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'invoice.payment_failed',
            'data' => [
                'object' => [
                    'customer' => $stripeCustomerId,
                    'amount_due' => 321,
                    'currency' => 'dkk',
                    'attempt_count' => 1,
                    'next_payment_attempt' => 2,
                    'hosted_invoice_url' => $invoiceUrl,
                    'account_country' => 'dk',
                ],
            ]
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('3.21', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('invoice.payment_failed', $payments[0]->getType());
        self::assertEquals($stripeCustomerId, $payments[0]->getCustomer());

        $this->em->refresh($clientStripe);
        self::assertEquals($invoiceUrl, $clientStripe->getInvoiceUrl());
        self::assertEquals(1, $clientStripe->getAttemptCount());
        self::assertEquals(2, $clientStripe->getNextPaymentAttempt());
    }

    public function testChargeSucceeded(): void
    {
        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
        ]);

        $this->responseStatusShouldBe(500);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'random' => 'field',
                    'source' => [
                        'some' => 'field'
                    ]
                ],
            ]
        ]);

        $this->responseStatusShouldBe(200);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'random' => 'field',
                    'source' => [
                        'some' => 'field'
                    ]
                ],
            ]
        ]);

        $this->responseStatusShouldBe(200);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'random' => 'field',
                    'source' => [
                        'type' => 'debit',
                        'some' => 'field'
                    ]
                ],
            ]
        ]);

        $this->responseStatusShouldBe(200);

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'random' => 'field',
                    'source' => [
                        'type' => 'klarna',
                        'some' => 'field'
                    ]
                ],
            ]
        ]);

        $this->responseStatusShouldBe(500);

        $user = $this->getOrCreateDummyUser('user');
        $userStripe = new UserStripe($user, uniqid(), uniqid());
        $user->setUserStripe($userStripe);
        $this->em->persist($userStripe);
        $this->em->flush();

        $client = $this->getOrCreateDummyClient('client', $user);

        $stripeCustomerId = uniqid();
        $clientStripe = new ClientStripe();
        $clientStripe->setClient($client);
        $clientStripe->setStripeCustomer($stripeCustomerId);
        $this->em->persist($clientStripe);
        $this->em->flush();

        $this->request('POST', '/hook/handle?acc=connect', [
            'type' => 'charge.succeeded',
            'data' => [
                'object' => [
                    'random' => 'field',
                    'source' => [
                        'type' => 'klarna',
                        'some' => 'field'
                    ],
                    'customer' => $stripeCustomerId,
                    'amount' => 123,
                    'application_fee_amount' => 2,
                    'currency' => 'dkk',
                ],
            ]
        ]);

        $this->responseStatusShouldBe(200);

        $payments = $this->paymentsLogRepository->findAll();
        self::assertCount(1, $payments);
        self::assertArrayHasKey(0, $payments);
        self::assertInstanceOf(PaymentsLog::class, $payments[0]);
        self::assertEquals('1.23', $payments[0]->getAmount());
        self::assertEquals('dkk', $payments[0]->getCurrency());
        self::assertEquals('charge.succeeded', $payments[0]->getType());
        self::assertEquals($stripeCustomerId, $payments[0]->getCustomer());
    }
}
