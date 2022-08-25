<?php

namespace GymBundle\Services;

use AppBundle\Services\ValidationService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AppBundle\Entity\User;
use AppBundle\Entity\Language;
use GymBundle\Repository\GymRepository;
use AppBundle\Repository\WorkoutPlanRepository;
use AppBundle\Repository\ExerciseRepository;
use AppBundle\Services\WorkoutPlanService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TrainerService
{
    private EntityManagerInterface $em;
    private ValidationService $validationService;
    private UserManagerInterface $userManager;
    private GymRepository $gymRepository;
    private WorkoutPlanRepository $workoutPlanRepository;
    private ExerciseRepository $exerciseRepository;
    private WorkoutPlanService $workoutPlanService;

    public function __construct(
        EntityManagerInterface $em,
        UserManagerInterface $userManager,
        ValidationService $validationService,
        GymRepository $gymRepository,
        WorkoutPlanRepository $workoutPlanRepository,
        ExerciseRepository $exerciseRepository,
        WorkoutPlanService $workoutPlanService
    ) {
        $this->em = $em;
        $this->userManager = $userManager;
        $this->validationService = $validationService;
        $this->gymRepository = $gymRepository;
        $this->workoutPlanRepository = $workoutPlanRepository;
        $this->exerciseRepository = $exerciseRepository;
        $this->workoutPlanService = $workoutPlanService;
    }

    public function create(string $name, string $email, string $password, ?string $phone = null, ?User $admin = null, ?Language $language = null, bool $activated = true): User
    {
        $userManager = $this->userManager;
        $uid = hexdec(substr(uniqid(),0,8));
        $username = $name . '_' .  $uid;

        $validationService = $this->validationService;
        $validationService->checkEmail($email);
        $validationService->checkEmptyString($name, 'The name is empty');
        $validationService->checkEmptyString($password, 'Invalid password');

        if ($userManager->findUserByUsernameOrEmail($email)) {
            throw new HttpException(422, 'A user with this email already exists.');
        }

        if (strlen($password) < 6) {
            throw new HttpException(422, 'Your password needs to be at least 6 characters.');
        }

        $user = new User();
        $user
            ->setEnabled(true)
            ->setSignupDate(new \DateTime())
            ->setActivated($activated)
            ->setUsername($username)
            ->setName($name)
            ->setPhone($phone)
            ->setEmail($email)
            ->setLanguage($language)
            ->setPlainPassword($password);

        $token = md5(uniqid($user->getEmail(), true));
        $user->setInteractiveToken($token);

        $userManager->updateUser($user);

        if ($admin) {
            $this->addUserToGym($admin, $user);
        }

        return $user;
    }

    private function addUserToGym(User $admin, User $newUser): void
    {
        $gym = $this
            ->gymRepository
            ->findGymByAdmin($admin);

        $assignDataFromUser = $gym->getAssignDataFrom() !== null ?
            $gym->getAssignDataFrom() :
            $admin;

        $adminUserStripe = $assignDataFromUser->getUserStripe();
        if ($adminUserStripe !== null) {
            $userStripe = (clone $adminUserStripe)
                ->setUser($newUser);
            $this->em->persist($userStripe);
        }

        if ($assignDataFromUser->getUserSettings()) {
            $userSettings = (clone $assignDataFromUser->getUserSettings())
                ->setUser($newUser);
            $this->em->persist($userSettings);
        }

        /*
        if ($assignDataFromUser->getUserTerms()) {
            $userTerms = (clone $assignDataFromUser->getUserTerms($newUser));
            $this->em->persist($userTerms);
        }*/

        if ($assignDataFromUser->getUserApp()) {
            $userApp = (clone $assignDataFromUser->getUserApp())
                ->setUser($newUser);
            $this->em->persist($userApp);
        }

        //copy workout templates
        foreach ($this->workoutPlanRepository->getAllByUser($assignDataFromUser, true) as $t) {
            try {
                if (!is_array($t) || !array_key_exists('id', $t)) {
                    throw new \RuntimeException();
                }

                $template = $this
                    ->workoutPlanRepository
                    ->get((int) $t['id']);
            } catch (NotFoundHttpException) {
                continue;
            }

            $this
                ->workoutPlanService
                ->setName($template->getName())
                ->setExplaination($template->getExplaination())
                ->setComment($template->getComment())
                ->createPlan($newUser, null, $template);
        }

        //copy user exercises
        foreach ($this->exerciseRepository->findByUser($assignDataFromUser) as $exercise) {
            $newExercise = (clone $exercise)
                ->setUser($newUser);
            $this->em->persist($newExercise);
        }

        //copy default messages
        foreach ($assignDataFromUser->getDefaultMessages() as $defaultMessage) {
            $newDefaultMessage = (clone $defaultMessage)
                ->setUser($newUser);
            $this->em->persist($newDefaultMessage);
        }

        $gym->addUser($newUser);
        $this->em->flush();
    }
}
