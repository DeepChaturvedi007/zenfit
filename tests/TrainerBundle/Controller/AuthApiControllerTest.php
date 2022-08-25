<?php declare(strict_types=1);

namespace Tests\TrainerBundle\Controller;

use AppBundle\Entity\Bundle;
use Symfony\Component\BrowserKit\Cookie;
use Tests\Context\BaseWebTestCase;

class AuthApiControllerTest extends BaseWebTestCase
{
    public function testSignUpAction(): void
    {
        $email = uniqid().'@email.com';
        $this->request('POST', '/auth/api/sign-up', [
            'name' => uniqid(),
            'email' => $email,
            'password' => uniqid(),
            'locale' => 'en',
        ]);

        $this->responseStatusShouldBe(200);

        $user = $this->userRepository->findOneBy(['email' => $email]);

        self::assertNotNull($user);

        $cookies = $this->client->getResponse()->headers->getCookies();
        $hasBearerCookie = false;
        $hasRefreshCookie = false;
        foreach ($cookies as $cookie) {
            if ($cookie->getName() === 'BEARER') {
                $hasBearerCookie = true;
            } elseif ($cookie->getName() === 'REFRESH_TOKEN') {
                $hasRefreshCookie = true;
            }
        }

        self::assertTrue($hasRefreshCookie);
        self::assertTrue($hasBearerCookie);
    }

    public function testSignUpDuplication(): void
    {
        $user = $this->getOrCreateDummyUser('user');

        $this->request('POST', '/auth/api/sign-up', [
            'name' => uniqid(),
            'email' => $user->getEmail(),
            'password' => uniqid(),
            'locale' => 'en',
        ]);

        $this->responseStatusShouldBe(422);

        self::assertStringContainsString('A user with this email already exists.', $this->getResponseContent());
    }

    public function testAccessPublicRouteWithNoCookies(): void
    {
        $user = $this->getOrCreateDummyUser('user');
        $bundle = new Bundle($user, uniqid());
        $this->em->flush();

        $this->request('GET', '/leads/survey/'.$user->getId().'/'.$bundle->getId());

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="survey-page-controller"', $this->getResponseContent());
    }

    public function testAccessPublicRouteWithExpiredAccessToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $bundle = new Bundle($user, uniqid());
        $this->em->flush();
        $this->request('GET', '/leads/survey/'.$user->getId().'/'.$bundle->getId());

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="survey-page-controller"', $this->getResponseContent());
    }

    public function testAccessPublicRouteWithInvalidAccessToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $invalidToken = uniqid();

        $cookie = new Cookie('BEARER', $invalidToken);
        $this->client->getCookieJar()->set($cookie);

        $bundle = new Bundle($user, uniqid());
        $this->em->flush();
        $this->request('GET', '/leads/survey/'.$user->getId().'/'.$bundle->getId());

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="survey-page-controller"', $this->getResponseContent());
    }

    public function testAccessPublicRouteWithExpiredAccessTokenAndInvalidRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $cookieRefresh = new Cookie('REFRESH_TOKEN', uniqid());
        $this->client->getCookieJar()->set($cookieRefresh);

        $bundle = new Bundle($user, uniqid());
        $this->em->flush();
        $this->request('GET', '/leads/survey/'.$user->getId().'/'.$bundle->getId());

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="survey-page-controller"', $this->getResponseContent());
    }

    public function testAccessPublicRouteWithExpiredAccessTokenAndExpiredRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, -1);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookieRefresh);

        $bundle = new Bundle($user, uniqid());
        $this->em->flush();
        $this->request('GET', '/leads/survey/'.$user->getId().'/'.$bundle->getId());

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="survey-page-controller"', $this->getResponseContent());
    }

    public function testLoginPageWithNoCookies(): void
    {
        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginPageWithExpiredAccessToken(): void
    {
        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginPageWithInvalidAccessToken(): void
    {
        $cookie = new Cookie('BEARER', uniqid());
        $this->client->getCookieJar()->set($cookie);

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginPageWithInvalidAccessTokenAndValidRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 100);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $expiredToken = uniqid();

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginPageWithInvalidAccessTokenAndInvalidRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = uniqid();

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', uniqid());
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginPageWithAccessToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);
        $user->setActivated(true);
        $this->em->flush();

        $this->currentAuthedUserIs('user');

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="dashboard-page-controller"', $this->getResponseContent());
    }

    public function testLoginPageWithRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);
        $user->setActivated(true);
        $this->em->flush();

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 100);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="dashboard-page-controller"', $this->getResponseContent());
    }

    public function testLoginPageWithExpiredAccessTokenAndRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);
        $user->setActivated(true);
        $this->em->flush();

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 100);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);


        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        self::assertStringContainsString('id="dashboard-page-controller"', $this->getResponseContent());
    }

    public function testLoginPageWithInvalidAccessTokenAndRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);
        $user->setActivated(true);
        $this->em->flush();

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 100);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $expiredToken = uniqid();

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);


        $this->request('GET', '/login');

        $this->responseStatusShouldBe(200);

        //If access token is invalid, we ask user to relogin
        self::assertStringContainsString('<div id="auth" data-props="{&quot;view&quot;:&quot;login&quot;', $this->getResponseContent());
    }

    public function testLoginWithExpiredAccessTokenInCookieAndExpiredRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);
        $this->currentAuthedUserIs('user');

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, -1);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken->getRefreshToken());
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $this->responseStatusShouldBe(401);
    }

    public function testLoginWithExpiredAccessTokenInCookieAndInvalidRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);

        $refreshToken = uniqid();

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken);
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $this->responseStatusShouldBe(401);
    }

    public function testLoginWithExpiredAccessTokenInCookieAndValidRefreshToken(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $user = $this->getOrCreateDummyUser('user', $email, $password);

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl($user, 30);
        $this->em->persist($refreshToken);
        $this->em->flush();

        $refreshToken = $refreshToken->getRefreshToken();

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $cookieRefresh = new Cookie('REFRESH_TOKEN', $refreshToken);
        $this->client->getCookieJar()->set($cookie);
        $this->client->getCookieJar()->set($cookieRefresh);

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $this->responseStatusShouldBe(200);
    }

    public function testLoginWithInvalidAccessTokenInCookie(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = uniqid();

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $response = $this->getResponseArray();
        self::assertArrayHasKey('message', $response);
        self::assertEquals('Invalid JWT Token', $response['message']);

        $this->responseStatusShouldBe(401);
    }

    public function testLoginWithExpiredAccessTokenInCookie(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);

        $expiredToken = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJpYXQiOjE2MzYxMTI3MDUsImV4cCI6MTYzNjExMjcxMCwicm9sZXMiOlsiUk9MRV9VU0VSIiwiUk9MRV9UUkFJTkVSIl0sInVzZXJuYW1lIjoiNjE4NTE5NDA5MTFiY0BlbWFpbC5jb20ifQ.Q8CKqI-y09VDJhJHddULCUk9eYymzdwdOfW9ed4232V4j7Nqwe6RODAwqa0JrIZZ135elqQ_DNwn-rnczV-n89sm7Fzauhzd4dgAlFefGHH9HUCM8nU03DtWyNsXLoHzb9OMogFBvxvRL4LAM-Lz311daHpPPOle2qvTMUeJ5Apu7W71JBafqMHfctyKu5aA8Szxc2HtQYbsECxhqPSigclaD0FkGksnQOk4b4Y0BcqMpJhFpdeBsXYnE0olXcvmj-v0iER1zhYwhG4xR0K7rjkW5UaA3zHge_OEYW3UOIORIR49Y3pcxRTd817lEfmBcgzNR-IK6gSTfDklrwnQtQ';

        $cookie = new Cookie('BEARER', $expiredToken);
        $this->client->getCookieJar()->set($cookie);

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $response = $this->getResponseArray();
        self::assertArrayHasKey('message', $response);
        self::assertEquals('Expired JWT Token', $response['message']);

        $this->responseStatusShouldBe(401);
    }

    public function testLoginWithAccessTokenInCookie(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);
        $this->currentAuthedUserIs('user');

        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $this->responseStatusShouldBe(200);
    }

    public function testLoginWithNoCookies(): void
    {
        $this->request('GET', '/api/exercises?equipmentId=&limit=25&muscleId=&page=1');

        $this->responseStatusShouldBe(401);
    }

    public function testLoginCheckFail(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);
        $this->currentAuthedUserIs('user');
        $this->request('POST', '/login_check', [
            'username' => $email,
            'password' => uniqid(),
        ]);

        $this->responseStatusShouldBe(401);
        self::assertEquals('Invalid credentials.', $this->getResponseArray()['message']);
    }

    public function testLoginCheckSuccess(): void
    {
        $email = uniqid().'@email.com';
        $password = uniqid();
        $this->getOrCreateDummyUser('user', $email, $password);
        $this->currentAuthedUserIs('user');
        $this->request('POST', '/login_check', [
            'username' => $email,
            'password' => $password,
        ]);

        $response = $this->getResponseArray();
        self::assertArrayHasKey('token', $response);
        self::assertArrayHasKey('refresh_token', $response);
    }
}
