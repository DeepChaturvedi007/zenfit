<?php declare(strict_types=1);

namespace Tests\Zenfit\StripeBundle;

use AppBundle\Entity\Payment;
use AppBundle\Entity\UserStripe;
use Tests\Context\BaseWebTestCase;

class SCACheckoutControllerTest extends BaseWebTestCase
{
    public function testCheckoutAction(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $stripeUserId = 'acct_1AZhDEB65NcwNf1O';
        $stripeRefreshToken = '123';
        $user->setUserStripe(new UserStripe($user, $stripeUserId, $stripeRefreshToken));

        if ($user->getUserStripe() !== null) {
            $this->em->persist($user->getUserStripe());
        }

        $client = $this->getOrCreateDummyClient('client', $user);
        $datakey = 'test';

        $payment = new Payment($client, $datakey);
        $payment->setCurrency('dkk');
        $payment->setRecurringFee(1223);
        $payment->setUpfrontFee(111);
        $payment->setCharged(false);
        $this->em->persist($payment);
        $this->em->flush();

        $this->request('GET', "/checkout/${datakey}");

        $this->responseStatusShouldBe(200);

        self::assertStringNotContainsString('No payment found', $this->getResponseContent());
        self::assertStringContainsString('<span id="amount">1334</span>', $this->getResponseContent());

        //initiate payment
        $this->request('POST', '/checkout/initiate', [
            'client' => $client->getId(),
            'datakey' => $datakey,
            'payment_type' => 'card'
        ], false);

        $this->responseStatusShouldBe(200);
        $responseData = $this->getResponseArray();

        self::assertArrayHasKey('datakey', $responseData);
        self::assertArrayHasKey('client_secret', $responseData);
        self::assertArrayHasKey('customer', $responseData);
        self::assertArrayHasKey('client', $responseData);
        self::assertEquals($client->getId(), $responseData['client']);
        self::assertEquals($datakey, $responseData['datakey']);
    }

    public function testCheckoutActionNotFound(): void
    {
        $this->request('GET', '/checkout/test');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('No payment found', $this->getResponseContent());
    }
}
