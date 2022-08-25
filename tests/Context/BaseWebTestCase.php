<?php declare(strict_types=1);

namespace Tests\Context;

use AppBundle\Entity\Client;
use AppBundle\Entity\UserSettings;
use AppBundle\Repository\ClientRepository;
use AppBundle\Repository\LeadRepository;
use AppBundle\Repository\PaymentsLogRepository;
use AppBundle\Repository\QueueRepository;
use AppBundle\Repository\UserRepository;
use ChatBundle\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use FOS\UserBundle\Model\UserManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\EventListener\StopWorkerOnMessageLimitListener;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Worker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;

abstract class BaseWebTestCase extends WebTestCase
{
    protected TokenStorageInterface $tokenStorage;
    protected ?UserInterface $currentUser;
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected Application $application;
    protected PasswordHasherFactoryInterface $encoderFactory;
    protected EventDispatcherInterface $eventDispatcher;
    protected UserRepository $userRepository;
    protected UserManagerInterface $userManager;
    protected LeadRepository $leadRepository;
    protected QueueRepository $queueRepository;
    protected ClientRepository $clientRepository;
    protected MessageRepository $messageRepository;
    protected PaymentsLogRepository $paymentsLogRepository;
    protected JWTTokenManagerInterface $jwtTokenManager;
    protected RefreshTokenGeneratorInterface $refreshTokenGenerator;

    /**
     * Stores dummy objects for testing
     * @var mixed[]
     */
    private array $storage = [];

    final protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->client->followRedirects();
        $this->encoderFactory = self::getContainer()->get(PasswordHasherFactoryInterface::class);
        $this->eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $this->tokenStorage = self::getContainer()->get(TokenStorageInterface::class);;
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->application = new Application(self::$kernel);
        $this->currentUser = null;
        $this->userRepository = self::getContainer()->get(UserRepository::class);
        $this->userManager = self::getContainer()->get(UserManagerInterface::class);
        $this->leadRepository = self::getContainer()->get(LeadRepository::class);
        $this->queueRepository = self::getContainer()->get(QueueRepository::class);
        $this->clientRepository = self::getContainer()->get(ClientRepository::class);
        $this->messageRepository = self::getContainer()->get(MessageRepository::class);
        $this->paymentsLogRepository = self::getContainer()->get(PaymentsLogRepository::class);
        $this->jwtTokenManager = self::getContainer()->get(JWTTokenManagerInterface::class);
        $this->refreshTokenGenerator = self::getContainer()->get(RefreshTokenGeneratorInterface::class);
    }

    public function getOrCreateDummyUser(string $keyToStore = null, string $email = null, string $password = '123'): User
    {
        if ($keyToStore !== null) {
            $user = $this->getDummy($keyToStore);
        }
        if (!isset($user)) {
            if ($email === null) {
                $email = uniqid().'@email.com';
            }
            $user = new User();
            $user->setEmail($email);
            $user->setName(uniqid() . ' ' . uniqid());
            $user->setUsername($email);
            $user->setPlainPassword($password);
            $user->setEnabled(true);
            $user->setInteractiveToken(uniqid());

            $userSettings = new UserSettings($user);
            $this->em->persist($userSettings);
            $user->setUserSettings($userSettings);

            $this->userManager->updateUser($user);

            if ($keyToStore !== null) {
                $this->storeDummy($keyToStore, $user);
            }
        }

        return $user;
    }

    public function getOrCreateDummyClient(string $keyToStore = null, User $trainer = null, string $email = null, string $password = '123'): Client
    {
        if ($keyToStore !== null) {
            $client = $this->getDummy($keyToStore);
        }
        if (!isset($client)) {
            if ($email === null) {
                $email = uniqid().'@email.com';
            }
            if ($trainer === null) {
                $trainer = $this->getOrCreateDummyUser();
            }

            $client = new Client($trainer, uniqid() . ' ' . uniqid(), $email);

            $client->setPassword($password);
            $client->setToken(uniqid());

            $client->setUser($trainer);

            $this->clientRepository->persist($client);
            $this->clientRepository->flush();

            if ($keyToStore !== null) {
                $this->storeDummy($keyToStore, $client);
            }
        }

        return $client;
    }

    /**
     * @param mixed[] $content
     * @param UploadedFile[] $files
     * @param array<string, string> $headers
     */
    final protected function request(string $method, string $url, array $content = [], bool $isJson = true, array $files = [], array $headers = []): void
    {
        $this->resetQueues();
        $parameters = [];
        if ($isJson) {
            $content = json_encode($content, JSON_THROW_ON_ERROR, 512);
            $headers['CONTENT_TYPE'] = 'application/json';
        } else {
            $parameters = $content;
            $content = null;
        }

        $this->client->request($method, $url, $parameters, $files, $headers, $content, true);
    }


    final public function currentAuthedUserIs(
        string $keyFromStorage,
        bool $accessTokenInCookie = true,
        bool $useInteractiveToken = false
    ): void
    {
        /** @var object|string|null $user */
        $user = $this->getDummy($keyFromStorage);
        if (!$user instanceof User) {
            throw new \RuntimeException();
        }

        $email = $user->getEmail();

        $this->currentUserIs($email);

        if ($accessTokenInCookie) {
            $accessToken = $this->jwtTokenManager->create($user);
            $cookie = new Cookie('BEARER', $accessToken);
            $this->client->getCookieJar()->set($cookie);
        }

        if ($useInteractiveToken && $user->getInteractiveToken() !== null) {
            $this->client->setServerParameter('HTTP_Authorization', $user->getInteractiveToken());
        }

    }

    final public function currentAuthedClientIs(string $keyFromStorage): void
    {
        /** @var object|string|null $client */
        $client = $this->getDummy($keyFromStorage);
        if (!$client instanceof Client) {
            throw new \RuntimeException();
        }

        $email = $client->getEmail();

        $this->currentClientIs($email);

        if ($client->getToken() !== null) {
            $this->client->setServerParameter('HTTP_Authorization', $client->getToken());
        }
    }

    final public function currentAuthedUserIsAnon(): void
    {
        $this->currentUserIs(null);

        $this->tokenStorage->setToken(null);

        $this->request('GET', '/logout');
    }

    private function currentUserIs(?string $email): void
    {
        if ($email !== null) {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if (!$user instanceof User) {
                throw new NotFoundHttpException();
            }

            $this->currentUser = $user;
        } else {
            $this->currentUser = null;
        }
    }

    private function currentClientIs(?string $email): void
    {
        if ($email !== null) {
            $client = $this->clientRepository->findOneBy(['email' => $email]);
            if ($client === null) {
                throw new NotFoundHttpException();
            }
        }
    }

    /** @param mixed $value */
    final public function storeDummy(string $keyToStore, $value): void
    {
        if (array_key_exists($keyToStore, $this->storage)) {
            throw new \RuntimeException('Such key already exists: '.$keyToStore);
        }

        $this->storage[$keyToStore] = $value;
    }

    /** @return ?mixed */
    final public function getDummy(string $key)
    {
        /** @var object|string|null $storedValue */
        $storedValue = $this->storage[$key] ?? null;
        if ($storedValue !== null && is_object($storedValue) && method_exists($storedValue, 'getId') && $storedValue->getId()) {
            if (!$this->em->getUnitOfWork()->isInIdentityMap($storedValue)) {
                /** @var ObjectRepository $repo */
                /** @phpstan-ignore-next-line */
                $repo = $this->em->getRepository(get_class($storedValue));
                $objectFromDb = $repo->find($storedValue->getId());
            } else {
                $objectFromDb = $storedValue;
            }
            if ($objectFromDb !== null) {
                $this->em->refresh($objectFromDb);

                return $objectFromDb;
            }
        }

        return $storedValue;
    }

    final protected function resetQueues(): void
    {
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.pdf_generation');
        $transport->reset();
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.video_compress');
        $transport->reset();
    }

    final protected function getMessagesCount(string $queueName): int
    {
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.'.$queueName);

        return count($transport->getSent());
    }

    /** @return Envelope[] */
    final protected function getQueueMessages(string $queueName): array
    {
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.'.$queueName);

        return $transport->getSent();
    }

    final protected function iConsumeAllMessagesInQueue(string $queueName, int $expectedAmount): void
    {
        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.'.$queueName);

        self::assertCount($expectedAmount, $transport->getSent());
        if ($expectedAmount === 0) {
            return;
        }

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $stopListener = new StopWorkerOnMessageLimitListener(count($transport->getSent()));
        $eventDispatcher->addSubscriber($stopListener);
        /** @var MessageBusInterface $messageBus */
        $messageBus = self::getContainer()->get(MessageBusInterface::class);
        $worker = new Worker([$transport], $messageBus, $eventDispatcher);
        $worker->run();
        $eventDispatcher->removeSubscriber($stopListener);
    }

    final protected function responseStatusShouldBe(int $code): void
    {
        self::assertEquals($code, $this->client->getResponse()->getStatusCode());
    }

    final public function getResponseContent(): string
    {
        return (string) $this->client->getResponse()->getContent();
    }

    /** @return array<mixed> */
    final public function getResponseArray(): array
    {
        /** @var array<string> $responseArray */
        $responseArray = json_decode($this->getResponseContent(), true, 512, JSON_THROW_ON_ERROR);

        return $responseArray;
    }
}
