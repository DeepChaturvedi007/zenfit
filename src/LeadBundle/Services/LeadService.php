<?php

namespace LeadBundle\Services;

use AppBundle\Entity\Bundle;
use AppBundle\Entity\Lead;
use AppBundle\Entity\LeadTag;
use AppBundle\Entity\User;
use AppBundle\Entity\Client;
use AppBundle\Services\ClientImageService;
use AppBundle\Services\ClientService;
use AppBundle\Services\MailChimpService;
use AppBundle\Services\QueueService;
use AppBundle\Services\ValidationService;
use ChatBundle\Services\ChatService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Illuminate\Support\Collection as LaravelCollection;

class LeadService
{
    private ClientImageService $clientImageService;
    private EntityManagerInterface $em;
    private MailChimpService $mailChimpService;
    private QueueService $queueService;
    private ClientService $clientService;
    private ValidationService $validationService;
    private ChatService $chatService;
    private UrlGeneratorInterface $urlGenerator;
    private string $appHostname;

    public function __construct(
        ClientService $clientService,
        UrlGeneratorInterface $urlGenerator,
        string $appHostname,
        ValidationService $validationService,
        ChatService $chatService,
        EntityManagerInterface $em,
        ClientImageService $clientImageService,
        MailChimpService $mailChimpService,
        QueueService $queueService
    )
    {
        $this->urlGenerator = $urlGenerator;
        $this->clientService = $clientService;
        $this->chatService = $chatService;
        $this->validationService = $validationService;
        $this->em = $em;
        $this->clientImageService = $clientImageService;
        $this->mailChimpService = $mailChimpService;
        $this->queueService = $queueService;
        $this->appHostname = $appHostname;
    }

    /**
    * @param Request $request
    * @param User $user
    */
    public function submitSurvey($request, User $user)
    {
        //get variables from request
        $name = $request->request->get('name');
        $email = $request->request->get('email');

        //check if email and name are valid
        $validationService = $this->validationService;
        $validationService->checkEmptyString($name);
        $validationService->checkEmail($email);

        //check if client already exists at trainer
        $clientService = $this->clientService;
        $client = $clientService->clientExistsAtTrainer($email, $user);

        if (!$client) {
            $client = $clientService
                ->addClient($name, $email, $user);
        }

        //update client information && assign plans, videos, docs etc.
        $clientService->updateClientInformation($request, $client, true);

        //if client should receive a chat message upon creation
        if ($request->request->has('message') && $request->request->get('message') != "") {
            $chatService = $this->chatService;
            $conversation = $chatService->getConversation($client);
            $chatService->sendMessage($request->request->get('message'), null, $client->getUser(), $conversation);
        }

        //if client has uploaded pictures
        $files = $request->files->all();
        foreach ($files as $type => $file) {
            $this
                ->clientImageService
                ->upload($file, new \DateTime(), $client, $type);
        }

        $this->em->flush();

        //check if we should redirect to bundle checkout or not
        if ($bundle = $request->request->get('bundle')) {
            $client->setDeleted(true);
            $this->em->flush();
            $url = $this->generateUrlForBundleCheckout((int) $bundle, $client);
            return ['redirect' => $url];
        }

        return ['success' => true];
    }

    private function generateUrlForBundleCheckout(int $bundleId, Client $client): string
    {
        $bundle = $this->em
            ->getRepository(Bundle::class)
            ->find($bundleId);

        if ($bundle === null) {
            throw new NotFoundHttpException('Bundle not found');
        }

        return $this->appHostname . $this->urlGenerator
            ->generate('zenfit_stripe_bundle_checkout', [
                'bundle' => $bundle->getId(),
                'client' => $client->getId()
            ]);
    }

    public function addLead(
        ?string $name = null,
        ?string $email = null,
        User $user,
        ?string $phone = null,
        int $status = Lead::LEAD_NEW,
        Client $client = null,
        ?string $dialog = null,
        ?string $utm = null,
        int $contactTime = 0
    ): Lead
    {
        if($dialog && strpos($dialog, 'http') !== false) {
            throw new HttpException(422, 'Invalid dialog message.');
        }

        $lead = new Lead($user);
        $lead
           ->setEmail($email)
           ->setName($name)
           ->setPhone($phone)
           ->setCreatedAt(new \DateTime('now'))
           ->setUpdatedAt(new \DateTime('now'))
           ->setStatus($status)
           ->setClient($client)
           ->setInDialog($dialog ? true : false)
           ->setDialogMessage($dialog)
           ->setUtm($utm)
           ->setContactTime($contactTime);

        $this->em->persist($lead);
        $this->em->flush();

        if ($user->getUserSettings() !== null && $user->getUserSettings()->getReceiveEmailOnNewLead()) {
            $this->sendEmailOnNewLead($lead);
        }

        try {
            if ($email !== null && $name !== null) {
                $this->mailChimpService->addSubscriber($user, $email, $name, Lead::LEAD_STATUS[$status]);
            }
        } catch (\Exception $e) {}

        return $lead;
    }

    public function addTags(Lead $lead, array $tags = [])
    {
        $existingTags = $lead->tagsList();

        foreach ($tags as $tag) {
            if ($tag == '') continue;
            if (!in_array($tag, $existingTags, true)) {
                $leadTag = (new LeadTag($lead, trim($tag)));

                $this->em->persist($leadTag);
            }
        }

        //remove tags
        $tagsToDelete = array_diff($existingTags, $tags);
        foreach ($tagsToDelete as $tagToDelete) {
            $leadTag = $this
                ->em
                ->getRepository(LeadTag::class)
                ->findOneBy([
                    'title' => $tagToDelete,
                    'lead' => $lead
                ]);

            if ($leadTag) {
                $this->em->remove($leadTag);
            }
        }

        $this->em->flush();
    }

    private function sendEmailOnNewLead(Lead $lead)
    {
        //send email to trainer
        $queueService = $this->queueService;
        $url =
            $this->appHostname .
            $this->urlGenerator->generate('leads');

        $msg = "You've got a new lead!<br /><br />
            Name: {$lead->getName()}<br />
            Email: {$lead->getEmail()}<br />
            Phone: {$lead->getPhone()}<br />
            Message: {$lead->getDialogMessage()}<br /><br />
            Click <a href=$url>here</a> to check it out!";

        $queueService->sendEmailToTrainer(
            $msg,
            "You've got a new lead!",
            $lead->getUser()->getEmail(),
            $lead->getUser()->getName()
        );
    }
}
