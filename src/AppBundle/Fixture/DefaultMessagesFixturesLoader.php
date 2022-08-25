<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\DefaultMessage;
use AppBundle\Repository\DefaultMessageRepository;

class DefaultMessagesFixturesLoader
{
    private DefaultMessageRepository $defaultMessageRepository;

    public function __construct(DefaultMessageRepository $defaultMessageRepository)
    {
        $this->defaultMessageRepository = $defaultMessageRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->defaultMessageRepository->findOneBy(['subject' => $item[0]]);
            if ($object !== null) {
                continue;
            }

            $object = new DefaultMessage();
            $object->setTitle($item[0]);
            $object->setMessage($item[1]);
            $object->setType($item[2]);
            $object->setSubject($item[3]);
            $object->setLocale($item[4]);

            $this->defaultMessageRepository->persist($object);
        }

        $this->defaultMessageRepository->flush();
    }

    /* @phpstan-ignore-next-line */
    private function getData(): array
    {
        return
            [
                ['Welcome Template','<p>Hi [client],</p><p>Welcome to [trainer]</p><p>Click the link below to create a login for the Zenfit app:</p><p><a href=[url]>[url]</a></p><p>All the best, [trainer]</p>', 4, 'Welcome to [trainer]', 'en'],
                ['Payment Template','<p>Hi [client],</p><p>Welcome to [trainer]</p><p>Click the link below to pay:</p><p><a href=[checkout]>[checkout]</a></p><p>All the best, [trainer]</p>', 1, '[trainer] Payment', 'en']
            ];
    }
}
