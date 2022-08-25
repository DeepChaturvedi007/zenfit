<?php

namespace TrainerBundle\Controller;

use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use LeadBundle\Services\LeadService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use AppBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use AppBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\OptimisticLockException;

/**
 * @Route("/trainer")
 */
class LandingPageController extends Controller
{
    private LeadService $leadService;
    private ValidationService $validationService;

    public function __construct(
        LeadService $leadService,
        ValidationService $validationService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->leadService = $leadService;
        $this->validationService = $validationService;

        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/leadCreateQuery/{user}/{locale}", name="leadCreateQuery", defaults={"user" = ""})
     */
    public function leadCreateQueryAction(Request $request, ?User $user = null, string $locale = 'en'): JsonResponse
    {
        $em = $this->getEm();
        $leadService = $this->leadService;
        $validationService = $this->validationService;

        $email = $request->query->get('email', $request->request->get('email'));
        $name = $request->query->get('Name', $request->request->get('Name'));
        $phone = $request->query->get('phone', $request->request->get('phone'));
        $dialog = $request->query->get('dialog', $request->request->get('dialog'));
        $token = $request->query->get('token', $request->request->get('token'));
        $utm = $request->query->get('utm', $request->request->get('utm'));

        $utm = $utm ? urldecode($utm) : '';
        $utm = trim($utm) === '' ? null : $utm;

        if($token) {
            $user = $em->getRepository(User::class)->findByToken($token);
        }

        if (!$user) {
            return new JsonResponse([
                'status' => 'error',
                'message' => 'No user was found for this request',
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $validationService->checkEmptyString($name);
            $validationService->checkEmail($email);

            $lead = $leadService->addLead(
                $name, $email, $user, $phone,
                Lead::LEAD_NEW, null, $dialog, $utm
            );
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(['success' => true]);
    }

}
