<?php

namespace GymBundle\Controller;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as sfController;
use Symfony\Component\HttpFoundation\Request;

class Controller extends sfController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @param Request $request
     * @param bool $assoc
     * @return mixed
     */
    public function requestInput(Request $request, $assoc = false)
    {
        return json_decode($request->getContent(), $assoc);
    }

    /**
     * @param Request $request
     * @return null|User
     */
    public function requestTrainer(Request $request)
    {
        $token = $request->headers->get('Authorization');
        $repo = $this->getEm()->getRepository(User::class);
        return $repo->findByToken($token);
    }
}
