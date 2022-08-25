<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Security\CurrentUserFetcher;
use AppBundle\Services\ErrorHandlerService;
use AppBundle\Services\TrainerAssetsService;
use AppBundle\Services\UserSubscriptionService;
use AppBundle\Services\SettingsService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[Route("/settings")]
class AccountController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        private CurrentUserFetcher $currentUserFetcher,
        private string $stripePublishableKey,
        private TrainerAssetsService $trainerAssetsService,
        private ErrorHandlerService $errorHandlerService,
        private UserSubscriptionService $userSubscriptionService,
        private SettingsService $settingsService
    ) {
        parent::__construct($em, $tokenStorage);
    }

    #[Route("/change-password", methods: ["POST"])]
    public function changePassword(Request $request): JsonResponse
    {
        $user = $this->currentUserFetcher->getCurrentUser();
        $body = $this->requestInput($request);
        try {
            if (
                !isset($body['password'], $body['password1'], $body['password2']) ||
                !is_scalar($body['password']) ||
                !is_scalar($body['password1']) ||
                !is_scalar($body['password2'])
            ) {
                throw new \RuntimeException('Please provide valid password, password1, password2 fields');
            }

            $currentPass = (string) $body['password'];
            $password1 = (string) $body['password1'];
            $password2 = (string) $body['password2'];

            $this
                ->settingsService
                ->changePassword($user, $password1, $password2, $currentPass);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse('OK');
    }

    #[Route("/profile-picture", name: "profilePictureUpload", methods: ["POST"])]
    public function uploadProfilePicture(Request $request): Response
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();
            $userSettings = $this->trainerAssetsService->getUserSettings($user);

            $uploadedFile = null;
            $files = $request->files->getIterator();
            if (method_exists($files, 'current')) {
                /** @var mixed $uploadedFile */
                $uploadedFile = $files->current();
            }

            if (!$uploadedFile instanceof UploadedFile) {
                return new JsonResponse(['error' => 'Please provide file'], Response::HTTP_BAD_REQUEST);
            }

            if ($uploadedFile->getError() !== 0) {
                throw new \RuntimeException("Could not upload a file");
            }

            $this->trainerAssetsService->uploadProfilePicture($uploadedFile, $user);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['url' => $userSettings->getProfilePicture()], Response::HTTP_CREATED);
    }

    #[Route("/company-logo", name: "companyLogoUpload", methods: ["POST"])]
    public function uploadCompanyLogo(Request $request): Response
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();
            $userSettings = $this->trainerAssetsService->getUserSettings($user);

            $uploadedFile = null;
            $files = $request->files->getIterator();
            if (method_exists($files, 'current')) {
                /** @var mixed $uploadedFile */
                $uploadedFile = $files->current();
            }

            if (!$uploadedFile instanceof UploadedFile) {
                return new JsonResponse(['error' => 'Please provide file'], Response::HTTP_BAD_REQUEST);
            }

            if ($uploadedFile->getError() !== 0) {
                throw new \RuntimeException("Could not upload a file");
            }

            $this->trainerAssetsService->uploadCompanyLogo($uploadedFile, $user);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['url' => $userSettings->getCompanyLogo()], Response::HTTP_CREATED);
    }

    #[Route("", name: "settings", methods: ["GET"])]
    public function settingsAction(Request $request): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        $settings = $this
            ->settingsService
            ->getSettings($user);

        return $this->render('@App/default/settings.html.twig', [
            'user' => $user,
            'settings' => $settings,
            'stripeKey' => $this->stripePublishableKey,
        ]);
    }

    #[Route("/update-card-success", methods: ["GET"])]
    public function updateCardSuccessAction(Request $request): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            throw new AccessDeniedHttpException();
        }

        if ($request->query->has('session_id') && $request->query->get('session_id') !== null) {
            //client is updating his card
            $this
                ->userSubscriptionService
                ->setUser($user)
                ->updateCard($request->query->get('session_id'));
        }

        return $this->redirectToRoute('settings');
    }

    #[Route("/create-stripe-session", methods: ["POST"])]
    public function createSessionAction(Request $request): JsonResponse
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();

            $session = $this
                ->userSubscriptionService
                ->setUser($user)
                ->createSession();

            return new JsonResponse(['url' => $session->url]);
        } catch (\Throwable $e) {
            $this->errorHandlerService->captureException($e);
            return new JsonResponse([], 422);
        }
    }

    #[Route("/save", methods: ["POST"])]
    public function saveSettings(Request $request): JsonResponse
    {
        try {
            $user = $this->currentUserFetcher->getCurrentUser();
            $body = $this->requestInput($request);

            $this->settingsService->saveSettings($body, $user);
            return new JsonResponse($this->settingsService->getSettings($user));
        } catch (ValidationFailedException $e) {
            $errors = [];
            /** @var ConstraintViolation $violation */
            foreach ($e->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            return new JsonResponse($errors, 422);
        } catch (\Throwable $e) {
            return new JsonResponse($e->getMessage(), 422);
        }
    }
}
