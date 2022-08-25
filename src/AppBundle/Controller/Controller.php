<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use AppBundle\Entity\WorkoutPlan;
use AppBundle\Entity\MasterMealPlan;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as sfController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use function Symfony\Component\String\u;

abstract class Controller extends sfController
{
    private EntityManagerInterface $em;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage
    ) {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    protected function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getUser(): ?User
    {
        $token = $this->tokenStorage->getToken();
        if ($token === null) {
            return null;
        }
        $user = $token->getUser();
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public function workoutEditorRedirect(WorkoutPlan $plan): RedirectResponse
    {
        if ($client = $plan->getClient()) {
            return $this->redirectToRoute('workout_client_edit', [
                'plan' => $plan->getId(),
                'client' => $client->getId(),
            ]);
        }

        return $this->redirectToRoute('workout_templates_edit', [
            'plan' => $plan->getId(),
        ]);
    }

    public function mealEditorRedirect(MasterMealPlan $plan): RedirectResponse
    {
        if ($client = $plan->getClient()) {
          return $this->redirectToRoute('meal_client_edit', [
              'plan' => $plan->getId(),
              'client' => $client->getId(),
          ]);
        }

        return $this->redirectToRoute('meal_templates_edit', [
            'plan' => $plan->getId(),
        ]);
    }

    public function clientBelongsToUser(Client $client, ?User $user = null): bool
    {
        if ($user === null) {
            $user = $this->getUser();

            if (!$user instanceof User) {
                return false;
            }
        }

        if ($user->isAssistant()) {
            $admin = $user->getGymAdmin();

            $userFirstName = $user->getFirstName();
            if ($userFirstName === null) {
                return false;
            }

            if ($client->getUser()->getId() === $admin->getId()) {
                foreach ($client->getTags() as $clientTag) {
                    if (
                        u($clientTag->getTitle())
                            ->ignoreCase()
                            ->equalsTo($userFirstName)
                    ) {
                        return true;
                    }
                }
            }
        } elseif($user->getId() === $client->getUser()->getId()) {
            return true;
        }


        return false;
    }

    public function goBack(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

    public function getUserFromRequest(Request $request): User
    {
        return $this
            ->getEm()
            ->getRepository(User::class)
            ->findByToken($request->headers->get('Authorization'));
    }

    /** @return array<mixed> */
    public function requestInput(Request $request): array
    {
        try {
            return json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Exception) {
            throw new BadRequestException('Please provide valid JSON');
        }
    }
}
