<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\Language;
use AppBundle\Repository\LanguageRepository;

class LanguagesFixturesLoader
{
    private LanguageRepository $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $object = $this->languageRepository->findOneBy(['locale' => $item['locale']]);
            if ($object !== null) {
                continue;
            }

            $object = new Language($item['name'], $item['locale']);
            $this->languageRepository->persist($object);
        }

        $this->languageRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                [
                    'name' => 'English',
                    'locale' => 'en',
                ],
                [
                    'name' => 'Danish',
                    'locale' => 'da_DK',
                ],
                [
                    'name' => 'Swedish',
                    'locale' => 'sv_SE',
                ],
                [
                    'name' => 'Norwegian',
                    'locale' => 'nb_NO',
                ],
            ];
    }
}
