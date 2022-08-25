<?php declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Client;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class FrontpageController extends Controller
{
    public function __construct(
        EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGenerator,
        private EventDispatcherInterface $eventDispatcher,
        private JWTTokenManagerInterface $JWTTokenManager,
        private TokenStorageInterface $tokenStorage
    ) {
        parent::__construct($em, $tokenStorage);
    }

    /**
     * @Route("/login/interactive/{token}/{route}/{client}", name="interactiveLogin")
     * @param string $token
     * @param Request $request
     * @return Response
     */
    public function interactiveLogin($token, Request $request, $route = 'dashboardOverview', $client = null)
    {
        $em = $this->getEm();
        $user = $em->getRepository(User::class)->findByToken($token);

        $accessToken = $this->JWTTokenManager->create($user);

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        //now dispatch the login event
        $event = new InteractiveLoginEvent($request, $token);
        $this->eventDispatcher
            ->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

        $url = $this->urlGenerator->generate($route,array('client' => $client));
        $url = $url . "?" .http_build_query($request->query->all(),'', '&');

        $response = new RedirectResponse($url);
        $response->headers->setCookie(Cookie::create('BEARER', $accessToken));

        return $response;
    }

    /**
     * @Route("/login/client/interactive/{token}", name="interactiveLoginClient")
     * @param string $token
     * @param Request $request
     * @return Response
     */
    public function interactiveLoginClient($token, Request $request)
    {
        $em = $this->getEm();
        $repo = $em->getRepository(Client::class);
        $client = $repo->findOneBy(['token' => $token]);

        if($client) {
          return $this->redirect("zenfit://auth/$token");
        }

        return $this->redirect('https://zenfitapp.com/clients');
    }

}
