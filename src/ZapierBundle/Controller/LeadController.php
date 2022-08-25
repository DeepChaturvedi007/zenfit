<?php

namespace ZapierBundle\Controller;

use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Services\LeadService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Lead;
use AppBundle\Services\ErrorHandlerService;
use AppBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/lead")
 */
class LeadController extends Controller
{
    private ValidationService $validationService;
    private ErrorHandlerService $errorHandlerService;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        ValidationService $validationService,
        ErrorHandlerService $errorHandlerService,
    ) {
        $this->validationService = $validationService;
        $this->errorHandlerService = $errorHandlerService;
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("", methods={"POST"})
     */
    public function createLeadAction(Request $request, LeadService $leadService): JsonResponse
    {
        try {
            $user = $this->getUserFromRequest($request);

            $name = $request->request->get('name');
            $email = $request->request->get('email');
            $phone = $request->request->get('phone');
            $dialog = $request->request->get('dialog');
            $contactTime = $request->request->getInt('contactTime');
            $utm = $request->request->get('utm');

            $validationService = $this->validationService;
            $validationService->checkEmptyString($name);
            $validationService->checkEmail($email);

            $leadService->addLead(
                $name,
                $email,
                $user,
                $phone,
                Lead::LEAD_NEW,
                null,
                $dialog,
                $utm,
                $contactTime
            );

            return new JsonResponse(['success' => true]);
        } catch (\Exception $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }
}
