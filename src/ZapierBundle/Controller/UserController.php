<?php

namespace ZapierBundle\Controller;

use AppBundle\Controller\Controller;
use AppBundle\Services\TrainerAssetsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/user")
 */
class UserController extends Controller
{
    private TrainerAssetsService $trainerAssetsService;

    public function __construct(
        TrainerAssetsService $trainerAssetsService,
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
    ) {
        $this->trainerAssetsService = $trainerAssetsService;
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Method({"GET"})
     * @Route("")
     */
    public function userAction(Request $request)
    {
        try {
            $user = $this->getUserFromRequest($request);

            $userSettings = $this
                ->trainerAssetsService
                ->getUserSettings($user);

            $response = [
                'name' => $user->getName(),
                'company' => $userSettings->getCompanyName(),
                'logo' => $userSettings->getCompanyLogo(),
                'picture' => $userSettings->getProfilePicture()
            ];

            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
