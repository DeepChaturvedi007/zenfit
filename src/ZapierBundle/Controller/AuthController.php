<?php

namespace ZapierBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Controller\Controller;

#[Route("/auth")]
class AuthController extends Controller
{
    #[Route("/verify")]
    public function verifyAction(Request $request)
    {
        try {
            $user = $this->getUserFromRequest($request);
            return new JsonResponse(['user' => $user->getName()]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
