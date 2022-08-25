<?php

namespace ReactApiBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Repository\ClientRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;

abstract class Controller extends AbstractController
{
    protected EntityManagerInterface $em;
    private ClientRepository $clientRepository;

    public function __construct(
        EntityManagerInterface $em,
        ClientRepository $clientRepository
    ) {
        $this->em = $em;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param Request $request
     * @param bool $assoc
     */
    public function requestInput(Request $request, $assoc = false) /** @phpstan-ignore-line */
    {
        return json_decode($request->getContent(), $assoc);
    }

    public function requestClientByToken(Request $request): ?Client
    {
        $token = $request->headers->get('Authorization');
        if ($token) {
            $client = $this->clientRepository->findOneBy(['token' => $token]);
            if ($client) {
                return $client;
            }
        }

        return null;
    }

    //deprecated!
    public function requestClient(Request $request): ?Client
    {
        $token = $request->headers->get('Authorization');
        if ($token) {
            $client = $this->clientRepository->findOneBy(['token' => $token]);
            if ($client) {
                return $client;
            } else {
                return $this->clientRepository->find($token);
            }
        }

        $clientId = null;
        if ($request->getMethod() === Request::METHOD_GET) {
            $clientId = $request->query->get('client');
        } else {
            $input = $this->requestInput($request);

            if (isset($input->client)) {
                $clientId = $input->client;
            }
        }

        if ($clientId) {
            return $this->clientRepository->find((int) $clientId);
        }

        return null;
    }

    public function convertCommaToDot(mixed $value): ?float
    {
        if (is_string($value)) {
            return (float) str_replace(",", ".", $value);
        }

        return is_numeric($value) ? (float) $value : null;
    }

}
