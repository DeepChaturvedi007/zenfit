<?php declare(strict_types=1);

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AppBundle\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AuthenticatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Guard\Token\GuardTokenInterface;
use Symfony\Component\Security\Http\AccessMapInterface;

class ZenfitJWTAuthenticator implements AuthenticatorInterface
{
    public function __construct(
        private JWTTokenAuthenticator $authenticator,
        private UrlGeneratorInterface $urlGenerator,
        private SessionInterface $session,
        private JWTTokenManagerInterface $JWTTokenManager,
        private UserRepository $userRepository,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private AccessMapInterface $accessMap,
    ) { }

    private function isApiRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() || str_contains($request->getRequestUri(), '/api/') || str_contains($request->getRequestUri(), '-api/');
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        if(!$this->isApiRequest($request)) {
            $loginUrl = $this->urlGenerator->generate('authLogin');

            return new RedirectResponse($loginUrl);
        }

        return $this->authenticator->start($request, $authException);
    }

    public function supports(Request $request): ?bool
    {
        if ($request->get('_route') === 'authLogin') {
            try {
                $this->getCredentials($request);
                return true;
            } catch (\Exception) {
                return false;
            }
        }

        [$attributes] = $this->accessMap->getPatterns($request);

        if (in_array(AuthenticatedVoter::PUBLIC_ACCESS, $attributes, true)) {
            return false;
        }

        $refreshToken = (string) $request->cookies->get('REFRESH_TOKEN');
        if ($refreshToken !== '') {
            $refreshTokenObject = $this->refreshTokenManager->get($refreshToken);
            if ($refreshTokenObject !== null && $refreshTokenObject->isValid()) {
                return true;
            }
        }

        return $this->authenticator->supports($request);
    }

    public function getCredentials(Request $request): PreAuthenticationJWTUserTokenInterface
    {
        $refreshToken = $request->cookies->get('REFRESH_TOKEN');

        try {
            /** @var ?PreAuthenticationJWTUserTokenInterface $credentials */
            $credentials = $this->authenticator->getCredentials($request);
            if ($credentials === null && $refreshToken !== null) {
                return $this->getPreAuthTokenFromRefreshToken($refreshToken);
            }

            if ($credentials === null) {
                throw new InvalidTokenException();
            }

            return $credentials;
        } catch (ExpiredTokenException $e) {
            $refreshToken = $request->cookies->get('REFRESH_TOKEN');
            if ($refreshToken !== null) {
                return $this->getPreAuthTokenFromRefreshToken($refreshToken);
            }

            throw $e;
        }
    }

    private function getPreAuthTokenFromRefreshToken(string $refreshToken): PreAuthenticationJWTUserTokenInterface
    {
        $refreshTokenObject = $this->refreshTokenManager->get($refreshToken);

        if ($refreshTokenObject === null) {
            throw new InvalidTokenException();
        }

        if (!$refreshTokenObject->isValid()) {
            throw new ExpiredTokenException();
        }

        $username = $refreshTokenObject->getUsername();
        $user = $this->userRepository->findOneBy(['username' => $username]);
        if ($user instanceof User) {
            $accessToken = $this->JWTTokenManager->create($user);
            $preAuthToken = new PreAuthenticationJWTUserToken($accessToken);
            if (!$payload = $this->JWTTokenManager->decode($preAuthToken)) {
                throw new InvalidTokenException('Invalid JWT Token');
            }

            $preAuthToken->setPayload($payload);

            $this->session->set('newAccessToken', $accessToken);

            return $preAuthToken;
        }

        throw new NotFoundHttpException('User not found');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        return $this->authenticator->getUser($credentials, $userProvider);
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->authenticator->checkCredentials($credentials, $user);
    }

    public function createAuthenticatedToken(UserInterface $user, string $providerKey): GuardTokenInterface
    {
        return $this->authenticator->createAuthenticatedToken($user, $providerKey);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        /** @var Response $response */
        $response = $this->authenticator->onAuthenticationFailure($request, $exception);

        if (!$this->isApiRequest($request)) {
            $loginUrl = $this->urlGenerator->generate('authLogin');

            $response = new RedirectResponse($loginUrl);
        }

        $response->headers->clearCookie('BEARER');

        return $response;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): ?Response
    {
        return $this->authenticator->onAuthenticationSuccess($request, $token, $providerKey);
    }

    public function supportsRememberMe(): bool
    {
        return $this->authenticator->supportsRememberMe();
    }
}
