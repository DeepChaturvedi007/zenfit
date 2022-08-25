<?php declare(strict_types=1);

namespace Tests\ZapierBundle\Controller;

use Tests\Context\BaseWebTestCase;

class LeadControllerTest extends BaseWebTestCase
{
    public function testCreateLeadAction(): void
    {
        $leadName = uniqid();
        $leadEmail = uniqid().'@email.com';
        $phone = uniqid();
        $dialog = true;
        $utm = uniqid();
        $contactTime = rand(0, 4);

        $this->getOrCreateDummyUser('user');
        $this->currentAuthedUserIs('user', false, true);
        $this->request('POST', '/zapier/lead', [
            'name' => $leadName,
            'email' => $leadEmail,
            'phone' => $phone,
            'dialog' => $dialog,
            'utm' => $utm,
            'contactTime' => $contactTime
        ], false);

        $this->responseStatusShouldBe(200);

        $lead = $this->leadRepository->findOneBy(['email' => $leadEmail]);

        self::assertNotNull($lead);
        self::assertEquals($leadName, $lead->getName());
        self::assertEquals($phone, $lead->getPhone());
        self::assertEquals($dialog, $lead->getInDialog());
        self::assertEquals($utm, $lead->getUtm());
        self::assertEquals($contactTime, $contactTime);
    }
}
