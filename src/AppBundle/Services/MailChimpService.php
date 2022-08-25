<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use \DrewM\MailChimp\MailChimp;

class MailChimpService
{
    private function getMailChimpInstance(User $user): ?MailChimp
    {
        $userSettings = $user->getUserSettings();
        if ($userSettings === null) {
            return null;
        }
        $mcApiKey = $userSettings->getMailChimpApiKey();
        if ($mcApiKey === null) {
            return null;
        }

        return new MailChimp($mcApiKey);
    }

    private function getListId(User $user): string
    {
        $userSettings = $user->getUserSettings();
        if ($userSettings === null) {
            throw new \RuntimeException('No UserSettings object');
        }

        $mc = $this->getMailChimpInstance($user);
        if ($mc === null) {
            throw new \RuntimeException('No mailchimp api key');
        }

        $result = $mc->get('lists');
        if ($result === false) {
            throw new \RuntimeException('Bad mailchimp request');
        }

        $mcListId = $result['lists'][0]['id'];

        $mcListWebId = $userSettings->getMailChimpListId();
        if ($mcListWebId !== null) {
            foreach ($result['lists'] as $item) {
                if (isset($item['web_id']) && $item['web_id'] === (int) $mcListWebId) {
                    $mcListId = $item['id'];
                }
            }
        }

        return $mcListId;
    }

    public function addSubscriber(User $user, string $email, string $name, ?string $tag = null): void
    {
        $mc = $this->getMailChimpInstance($user);
        if ($mc === null) {
            return;
        }

        $namePieces = explode(' ', $name);
        $fname = $namePieces[0];
        $lname = '';

        if (isset($namePieces[1])) {
            $lname = $namePieces[1];
        }

        $mcListId = $this->getListId($user);

        $mcPayload = [
            'email_address' => $email,
            'status' => 'subscribed',
            'merge_fields' => [
                'FNAME' => $fname,
                'LNAME' => $lname
            ]
        ];

        if ($tag !== null) {
            $tag = mb_strtolower($tag);
            $tag = preg_replace('/\s+/', '_', $tag);
            $mcPayload['tags'] = [$tag];
        }

        $mc->post("lists/$mcListId/members", $mcPayload);

        if (!$mc->success()) {
            $lastError = $mc->getLastError();
            throw new HttpException(422, $lastError === false ? null : $lastError);
        }
    }

    public function tagLeadAsWonIfItExists(User $user, string $email): void
    {
        $mc = $this->getMailChimpInstance($user);
        if ($mc === null) {
            return;
        }

        $mcListId = $this->getListId($user);
        $result = $mc->get("search-members?list_id=$mcListId&query=".urlencode($email));
        if ($result === false) {
            throw new \RuntimeException('Bad mailchimp request');
        }
        $leadId = null;
        if (isset($result['full_search']['members'][0]['email_address'])
            && $result['full_search']['members'][0]['email_address'] === $email
        ) {
           $leadId = $result['full_search']['members'][0]['id'];
        }

        if ($leadId !== null) {
            $result = $mc->post("lists/$mcListId/members/$leadId/tags", [
                'tags' => [
                    ['name' => 'won', 'status' => 'active'],
                ],
            ]);

            if (!is_bool($result)) {
                throw new \RuntimeException(json_encode($result, JSON_THROW_ON_ERROR));
            }
        }
    }
}
