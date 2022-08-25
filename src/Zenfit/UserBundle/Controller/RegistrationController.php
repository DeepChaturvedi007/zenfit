<?php

namespace Zenfit\UserBundle\Controller;

use AppBundle\EventListener\RegistrationCompletedListener;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use FOS\UserBundle\Controller\RegistrationController as BaseController;
use AppBundle\Entity\User;

class RegistrationController extends BaseController
{
    private EventDispatcherInterface $eventDispatcher;
    private FactoryInterface $formFactory;
    private UserManagerInterface $userManager;
    private TokenStorageInterface $tokenStorage;
    private AuthorizationCheckerInterface $authorizationChecker;
    private SessionInterface $session;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FactoryInterface $formFactory,
        SessionInterface $session,
        AuthorizationCheckerInterface $authorizationChecker,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory = $formFactory;
        $this->userManager = $userManager;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker  = $authorizationChecker;
        $this->session = $session;

        parent::__construct($this->eventDispatcher, $this->formFactory, $this->userManager, $this->tokenStorage);
    }

    public function registerAction(Request $request)
    {
        if ($this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $this->redirectToRoute('dashboardOverview');
        }

        $user = new User();
        $user->setEnabled(true);
        $user->setSignupDate(new \DateTime());

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::REGISTRATION_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var mixed $postData */
        $postData = $request->request->get('fos_user_registration_form');
        $username = null;
        $fullName = null;
        $phone = null;

        if ($postData) {
            $username = $postData['username'];
            $fullName = $username;

            $phone = $postData['phone'];


            if ($this->userManager->findUserByUsernameOrEmail($username)) {
                $uid = hexdec(substr(uniqid(),0,8));
                $username = $username . '_' .  $uid;

                $postData['username'] = $username;
                $request->request->set('fos_user_registration_form', $postData);
            }
        }

        $form = $this->formFactory->createForm();
        $form->setData($user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $user->setUsername($username);
            $user->setName($fullName);
            $user->setPhone($phone);

            $namePieces = explode(' ', $fullName);
            $user->setFirstName($namePieces[0]);

            if (isset($namePieces[1])) {
                $user->setLastName($namePieces[1]);
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, FOSUserEvents::REGISTRATION_SUCCESS);

            $this->userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_registration_confirmed');
                $response = new RedirectResponse($url);
            }

            $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), RegistrationCompletedListener::EVENT_NAME);
            return $this->redirectToRoute('intro');
        }

        return $this->render('@ZenfitUser/Registration/register-new.html.twig', array(
            'form' => $form->createView(),
            'view' => "signUp"
        ));
    }

    /**
     * Tell the user to check his email provider
     */
    public function checkEmailAction(Request $request)
    {
        $email = $this->session->get('fos_user_send_confirmation_email/email');
        $this->session->remove('fos_user_send_confirmation_email/email');
        $user = $this->userManager->findUserByEmail($email);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with email "%s" does not exist', $email));
        }

        return $this->render('@FOSUser/Registration/check_email.html.twig', array(
            'user' => $user,
        ));
    }

    /**
     * Receive the confirmation token from user email provider, login the user
     */
    public function confirmAction(Request $request, $token)
    {
        $userManager = $this->userManager;

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with confirmation token "%s" does not exist', $token));
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::REGISTRATION_CONFIRM);

        $userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            $url = $this->generateUrl('fos_user_registration_confirmed');
            $response = new RedirectResponse($url);
        }

        $this->eventDispatcher->dispatch(new FilterUserResponseEvent($user, $request, $response), FOSUserEvents::REGISTRATION_CONFIRMED);

        return $response;
    }

    /**
     * Tell the user his account is now confirmed
     */
    public function confirmedAction(Request $request): Response
    {
        $user = $this->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        return $this->render('@FOSUser/Registration/confirmed.html.twig', array(
            'user' => $user,
            'targetUrl' => $this->getTargetUrlFromSession(),
        ));
    }

    private function getTargetUrlFromSession(): ?string
    {
        $token = $this->tokenStorage->getToken();

        if ($token !== null && method_exists($token, 'getFirewallName')) {
            $key = sprintf('_security.%s.target_path', $token->getFirewallName());
            if ($this->session->has($key)) {
                return $this->session->get($key);
            }
        }

        return null;
    }
}
