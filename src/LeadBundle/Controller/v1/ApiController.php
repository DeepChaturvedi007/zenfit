<?php

namespace LeadBundle\Controller\v1;

use AppBundle\Entity\LeadTag;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Controller\Controller as Controller;
use LeadBundle\Services\LeadService;
use AppBundle\Services\ClientService;
use AppBundle\Entity\Lead;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use ClientBundle\Transformer\ClientTransformer;
use AppBundle\Services\ValidationService;
use function Symfony\Component\String\u;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
    private LeadService $leadService;
    private EntityManagerInterface $em;
    private ClientService $clientService;
    private ValidationService $validationService;
    private ClientTransformer $clientTransformer;

    public function __construct(
        EntityManagerInterface $em,
        LeadService $leadService,
        ClientService $clientService,
        ValidationService $validationService,
        ClientTransformer $clientTransformer
    ) {
        $this->leadService = $leadService;
        $this->clientService = $clientService;
        $this->validationService = $validationService;
        $this->clientTransformer = $clientTransformer;
        $this->em = $em;
    }

    /**
     * @Route("", methods={"GET"})
     */
    public function getLeads(Request $request): JsonResponse
    {
        $q = $request->query->get('q');
        $status = $request->query->get('status');
        $limit = $request->query->getInt('limit');
        $asCount = $request->query->getBoolean('count', false);
        $offset = $request->query->getInt('offset');
        $tag = $request->query->get('tag', null);
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $userToFetchLeadsFrom = $this->getUser();
        if (!$userToFetchLeadsFrom instanceof User) {
            throw new AccessDeniedHttpException();
        }

        if ($currentUser->getUserType() === User::USER_TYPE_ASSISTANT) {
            $userToFetchLeadsFrom = $currentUser->getGymAdmin();

            $tag = $currentUser->getFirstName();
        }

        $leads = $this
            ->em
            ->getRepository(Lead::class)
            ->findAllLeadsByUser($userToFetchLeadsFrom, $q, $tag, $status, $limit, $offset, $asCount);

        return new JsonResponse($leads);
    }

    /**
     * @Route("/create", methods={"POST"})
     */
    public function createOrUpdateLead(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User) {
            throw new AccessDeniedHttpException();
        }

        $gymAdmin = null;
        if ($currentUser->isAssistant()) {
            $gymAdmin = $currentUser->getGymAdmin();
        }

        $name = $request->get('name');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $lead = $request->get('lead');
        $followUpAt = $request->get('followUpAt');
        $dialogMessage = $request->get('dialogMessage');
        $salesNotes = $request->get('salesNotes');
        $convertToClient = filter_var($request->get('convertToClient'), FILTER_VALIDATE_BOOLEAN);

        $inDialog = $request->get('inDialog') ?
            filter_var($request->get('inDialog'), FILTER_VALIDATE_BOOLEAN) :
            false;

        $tags = $request->get('tags', $currentUser->isAssistant() ? [$currentUser->getFirstName()] : []);
        if ($currentUser->isAssistant() && !in_array($currentUser->getFirstName(), $tags, true)) {
            $tags[] = $currentUser->getFirstName();
        }

        $followUp = $request->get('followUp') ?
            filter_var($request->get('followUp'), FILTER_VALIDATE_BOOLEAN) :
            false;

        $status = $request->get('status') ?
            $request->get('status') :
            Lead::LEAD_NEW;

        try {
            $validationService = $this->validationService;
            $validationService->checkEmptyString($name);
            $validationService->checkEmptyString($email);
            $validationService->checkEmail($email);

            if ($lead) {
                //we're updating an exist lead
                /** @var Lead $lead */
                $lead = $this
                    ->em
                    ->getRepository(Lead::class)
                    ->find($lead);

                $canEditLead = false;
                if ($lead->getUser() === $currentUser) {
                    $canEditLead = true;
                } elseif ($currentUser->isAssistant() && $lead->getUser() === $gymAdmin) {
                    $userFirstName = $currentUser->getFirstName();
                    if ($userFirstName !== null) {
                        /** @var LeadTag $savedLeadTag */
                        foreach ($lead->getTags() as $savedLeadTag) {
                            if (
                                u($savedLeadTag->getTitle())
                                    ->ignoreCase()
                                    ->equalsTo($userFirstName)
                            ) {
                                $canEditLead = true;
                                break;
                            }
                        }
                    }
                }

                if (!$canEditLead){
                    throw new AccessDeniedHttpException('Access Denied');
                }
            } else {
                if ($currentUser->isAssistant()) {
                    throw new AccessDeniedHttpException('Access Denied');
                }

                //we create a new lead
                $lead = (new Lead($currentUser))
                    ->setCreatedAt(new \DateTime('now'));
            }

            $client = null;

            $lead
               ->setEmail($email)
               ->setName($name)
               ->setPhone($phone)
               ->setStatus($status)
               ->setUpdatedAt(new \DateTime('now'))
               ->setInDialog($inDialog)
               ->setSalesNotes($salesNotes);

             if ($followUp) {
                 $lead->setFollowUpAt(new \DateTime($followUpAt));
             } else {
                 $lead->setFollowUpAt(null);
             }

             if ($inDialog) {
                 $lead->setDialogMessage($dialogMessage);
             }

             if ($convertToClient) {
                 $client = $lead->getClient();

                 if (!$client) {
                     $clientWillBelongTo = $currentUser;
                     if ($currentUser->isAssistant()) {
                         $currentUserGyms = $currentUser->getGyms();
                         if (array_key_exists(0, $currentUserGyms)) {
                            $clientWillBelongTo = $currentUserGyms[0]->getAdmin();
                         } else {
                            throw new \RuntimeException('User Assistant does not belong to any Gym');
                         }
                     }

                     $client = $this
                         ->clientService
                         ->addClient($name, $email, $clientWillBelongTo, $phone, true);
                 }

                 $this->clientService->addTags($client, $tags, true);
                 $lead->setClient($client);
             }

             $this->em->persist($lead);
             $this->em->flush();

             //add tags to lead
             $this
                ->leadService
                ->addTags($lead, $tags);

             return new JsonResponse([
                'lead' => $lead->getId(),
                'client' => $client ? $this->clientTransformer->transform($client) : null
             ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @Route("/delete", methods={"DELETE"})
     */
    public function deleteLead(Request $request): JsonResponse
    {
        $currentUser = $this->getUser();
        if (!$currentUser instanceof User || $currentUser->isAssistant()) {
            throw new AccessDeniedHttpException();
        }

        /** @var ?Lead $lead */
        $lead = $this
            ->em
            ->getRepository(Lead::class)
            ->find($request->get('lead'));

        if ($lead === null) {
            throw new NotFoundHttpException();
        }

        if ($lead->getUser() !== $currentUser) {
            throw new AccessDeniedHttpException();
        }

        $lead->setDeleted(true);
        $this->em->flush();
        return new JsonResponse('OK');
    }

}
