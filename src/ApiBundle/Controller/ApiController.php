<?php

namespace ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/")
 */
class ApiController extends AbstractController
{
    /**
     * @Route("/ping", methods={"GET", "POST"})
     */
    public function ping (Request $request)
    {
        return $this->successResponse();
    }

    protected function successResponse(array $data = [], $message = 'Success', $code = JsonResponse::HTTP_OK)
    {
        return new JsonResponse([
            'data' => $data,
            'message' => $message
        ], $code);
    }

    protected function errorResponse(array $data = [], $error = 'Success', $code = JsonResponse::HTTP_INTERNAL_SERVER_ERROR)
    {
        return new JsonResponse([
            'data' => $data,
            'error' => $error
        ], $code);
    }

    protected function forbiddenResponse(array $data = [], $error = 'Forbidden', $code = JsonResponse::HTTP_FORBIDDEN)
    {
        return new JsonResponse([
            'data' => $data,
            'error' => $error
        ], $code);
    }
}
