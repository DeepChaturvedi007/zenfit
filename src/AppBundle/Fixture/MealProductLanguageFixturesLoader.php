<?php declare(strict_types=1);

namespace AppBundle\Fixture;

use AppBundle\Entity\MealProductLanguage;
use AppBundle\Repository\LanguageRepository;
use AppBundle\Repository\MealProductLanguageRepository;
use AppBundle\Repository\MealProductRepository;

class MealProductLanguageFixturesLoader
{
    private LanguageRepository $languageRepository;
    private MealProductLanguageRepository $mealProductLanguageRepository;
    private MealProductRepository $mealProductRepository;

    public function __construct(
        MealProductLanguageRepository $mealProductLanguageRepository,
        MealProductRepository $mealProductRepository,
        LanguageRepository $languageRepository
    ) {
        $this->mealProductLanguageRepository = $mealProductLanguageRepository;
        $this->languageRepository = $languageRepository;
        $this->mealProductRepository = $mealProductRepository;
    }

    public function __invoke(): void
    {
        foreach ($this->getData() as $item) {
            $product = $this->mealProductRepository->findOneBy(['name' => $item[0]]);
            $language = $this->languageRepository->findOneBy(['locale' => $item[1]]);
            if ($product === null || $language === null) {
                throw new \RuntimeException();
            }
            $object = $this->mealProductLanguageRepository->findOneBy(['language' => $language, 'mealProduct' => $product]);
            if ($object !== null) {
                continue;
            }

            $object = new MealProductLanguage($item[2], $language, $product);
            $object->setLanguage($language);
            $object->setMealProduct($product);

            $this->mealProductLanguageRepository->persist($object);
        }

        $this->mealProductLanguageRepository->flush();
    }

    private function getData(): array
    {
        return
            [
                ['Asparagus. green', 'en', 'Asparagus. green'],
                ['Banana', 'en', 'Banana'],
                ['Blueberry', 'en', 'Blueberry'],
                ['Mushroom', 'en', 'Mushroom'],
                ['Spring onion', 'en', 'Spring onion'],
                ['Bread. white', 'en', 'Bread. white'],
                ['Oats', 'en', 'Oats'],
                ['Peanut butter. low in sugar', 'en', 'Peanut butter. low in sugar'],
                ['Potato. raw', 'en', 'Potato. raw'],
                ['Chicken. cold cut', 'en', 'Chicken. cold cut'],
                ['Onion', 'en', 'Onion'],
                ['Bell pepper. sweet. green', 'en', 'Bell pepper. sweet. green'],
                ['Rye bread. dark. wholemeal', 'en', 'Rye bread. dark. wholemeal'],
                ['Pork. ham. boiled. sliced', 'en', 'Pork. ham. boiled. sliced'],
                ['Milk. skimmed. 0.5 % fat', 'en', 'Milk. skimmed. 0.5 % fat'],
                ['Spinach', 'en', 'Spinach'],
                ['Tomato', 'en', 'Tomato'],
                ['Egg', 'en', 'Egg'],
                ['Egg white', 'en', 'Egg white'],
                ['Icelandic yoghurt. skyr. reduced sugar', 'en', 'Icelandic yoghurt. skyr. reduced sugar'],
                ['Skyr. 0.2%. Vanilla', 'en', 'Skyr. 0.2%. Vanilla'],
                ['Protein powder', 'en', 'Protein powder'],
                ['Egg whites. pasteurized', 'en', 'Egg whites. pasteurized'],
                ['Egg whites', 'en', 'Egg whites'],
                ['Rice. white. long-grain. dry', 'en', 'Rice. white. long-grain. dry'],
                ['BLUEBERRIES', 'en', 'BLUEBERRIES'],
                ['CHIA SEEDS. DRIED', 'en', 'CHIA SEEDS. DRIED'],
                ['Chicken filet. natural', 'en', 'Chicken filet. natural'],
                ['Mixed greens', 'en', 'Mixed greens'],
                ['Cottage cheese. 1.5%', 'en', 'Cottage cheese. 1.5%'],
                ['Muesli. low in fat & sugar', 'en', 'Muesli. low in fat & sugar'],
                ['Coconut milk. light. canned', 'en', 'Coconut milk. light. canned'],
                ['Asparagus. green', 'da_DK', 'Asparges. gr??nne'],
                ['Banana', 'da_DK', 'Banan'],
                ['Blueberry', 'da_DK', 'Bl??b??r'],
                ['Mushroom', 'da_DK', 'Champignon'],
                ['Spring onion', 'da_DK', 'For??rsl??g'],
                ['Bread. white', 'da_DK', 'Franskbr??d'],
                ['Oats', 'da_DK', 'Havregryn'],
                ['Peanut butter. low in sugar', 'da_DK', 'Peanutbutter. lavt sukkerindhold'],
                ['Potato. raw', 'da_DK', 'Kartoffel. r??'],
                ['Chicken. cold cut', 'da_DK', 'Kylling. p??l??g'],
                ['Onion', 'da_DK', 'L??g'],
                ['Bell pepper. sweet. green', 'da_DK', 'Peberfrugt. gr??n'],
                ['Rye bread. dark. wholemeal', 'da_DK', 'Rugbr??d. fuldkorn'],
                ['Pork. ham. boiled. sliced', 'da_DK', 'Skinke. kogt. skivesk??ret'],
                ['Milk. skimmed. 0.5 % fat', 'da_DK', 'Skummetm??lk 0.5 % fedt (Minim??lk)'],
                ['Spinach', 'da_DK', 'Spinat'],
                ['Tomato', 'da_DK', 'Tomat'],
                ['Egg', 'da_DK', '??g. hele'],
                ['Egg white', 'da_DK', '??ggehvide'],
                ['Icelandic yoghurt. skyr. reduced sugar', 'da_DK', 'Skyr'],
                ['Skyr. 0.2%. Vanilla', 'da_DK', 'Skyr. 0.2%. vanilje'],
                ['Protein powder', 'da_DK', 'Proteinpulver'],
                ['Egg whites. pasteurized', 'da_DK', '??ggehvider. pasteuriserede'],
                ['Egg whites', 'da_DK', '??ggehvide'],
                ['Rice. white. long-grain. dry', 'da_DK', 'Jasmin ris. r??'],
                ['CHIA SEEDS. DRIED', 'da_DK', 'Chia fr??'],
                ['Chicken filet. natural', 'da_DK', 'Kylling. fersk filet'],
                ['Mixed greens', 'da_DK', 'Blandet gr??nt'],
                ['Cottage cheese. 1.5%', 'da_DK', 'Hytteost 1.5%'],
                ['Muesli. low in fat & sugar', 'da_DK', 'Mysli. lavt i fedt og tilsat sukker'],
                ['Coconut milk. light. canned', 'da_DK', 'Kokosm??lk. light. d??se'],
                ['Asparagus. green', 'sv_SE', 'Sparris. Gr??n'],
                ['Banana', 'sv_SE', 'Banan'],
                ['Blueberry', 'sv_SE', 'Bl??b??r'],
                ['Mushroom', 'sv_SE', 'Champinjon'],
                ['Spring onion', 'sv_SE', 'V??rl??k'],
                ['Bread. white', 'sv_SE', 'Br??d. vitt'],
                ['Oats', 'sv_SE', 'Havregryn'],
                ['Peanut butter. low in sugar', 'sv_SE', 'Jordn??tssm??r. l??gt sockerinneh??ll'],
                ['Potato. raw', 'sv_SE', 'Potatis. r??'],
                ['Chicken. cold cut', 'sv_SE', 'Kyckling. p??l??gg'],
                ['Onion', 'sv_SE', 'L??k'],
                ['Bell pepper. sweet. green', 'sv_SE', 'Paprika. gr??n'],
                ['Rye bread. dark. wholemeal', 'sv_SE', 'R??gbr??d. fullkorn'],
                ['Pork. ham. boiled. sliced', 'sv_SE', 'Skinka. kokt. skivad'],
                ['Milk. skimmed. 0.5 % fat', 'sv_SE', 'Mj??lk. l??tt. 0.5% fett'],
                ['Spinach', 'sv_SE', 'Spenat'],
                ['Tomato', 'sv_SE', 'Tomat'],
                ['Egg', 'sv_SE', '??gg'],
                ['Egg white', 'sv_SE', '??ggvita'],
                ['Icelandic yoghurt. skyr. reduced sugar', 'sv_SE', 'Skyr'],
                ['Skyr. 0.2%. Vanilla', 'sv_SE', 'Skyr. 0.2%. vanilj'],
                ['Protein powder', 'sv_SE', 'proteinpulver'],
                ['Egg whites. pasteurized', 'sv_SE', '??ggvita. past??riserad'],
                ['Egg whites', 'sv_SE', '??ggvita'],
                ['Rice. white. long-grain. dry', 'sv_SE', 'Jasminris. r??'],
                ['BLUEBERRIES', 'sv_SE', 'Bl??b??r'],
                ['CHIA SEEDS. DRIED', 'sv_SE', 'chiafr??'],
                ['Chicken filet. natural', 'sv_SE', 'Kycklingfil??. f??rsk'],
                ['Mixed greens', 'sv_SE', 'Blandade gr??nsaker'],
                ['Cottage cheese. 1.5%', 'sv_SE', 'Keso 1.5%'],
                ['Muesli. low in fat & sugar', 'sv_SE', 'M??sli. l??g fetthalt + tillsatt socker'],
                ['Coconut milk. light. canned', 'sv_SE', 'kokosmj??lk. light. Konserv'],
                ['Asparagus. green', 'nb_NO', 'Asparges. gr??nne'],
                ['Banana', 'nb_NO', 'Banan'],
                ['Blueberry', 'nb_NO', 'Bl??b??r'],
                ['Mushroom', 'nb_NO', 'Champignon'],
                ['Spring onion', 'nb_NO', 'V??rl??k'],
                ['Bread. white', 'nb_NO', 'Franskbr??d/baguette'],
                ['Oats', 'nb_NO', 'Havregryn'],
                ['Peanut butter. low in sugar', 'nb_NO', 'Pean??ttsm??r. lavt sukkerinnhold'],
                ['Potato. raw', 'nb_NO', 'Potet. r??'],
                ['Chicken. cold cut', 'nb_NO', 'Kylling. p??legg'],
                ['Onion', 'nb_NO', 'L??k'],
                ['Bell pepper. sweet. green', 'nb_NO', 'Paprika. gr??nn'],
                ['Rye bread. dark. wholemeal', 'nb_NO', 'Rugbr??d. fullkorn'],
                ['Pork. ham. boiled. sliced', 'nb_NO', 'Skinke. kokt. sk??ret i skiver'],
                ['Milk. skimmed. 0.5 % fat', 'nb_NO', 'Skummet melk 0.5% fett'],
                ['Spinach', 'nb_NO', 'Spinat'],
                ['Tomato', 'nb_NO', 'Tomat'],
                ['Egg', 'nb_NO', 'Egg. hel'],
                ['Egg white', 'nb_NO', 'Eggehvite'],
                ['Icelandic yoghurt. skyr. reduced sugar', 'nb_NO', 'Skyr'],
                ['Skyr. 0.2%. Vanilla', 'nb_NO', 'Skyr. 0.2%. vanilje'],
                ['Protein powder', 'nb_NO', 'Proteinpulver'],
                ['Egg whites. pasteurized', 'nb_NO', 'Eggehviter. pasteurisert'],
                ['Egg whites', 'nb_NO', 'Eggehvite'],
                ['Rice. white. long-grain. dry', 'nb_NO', 'Jasmin ris. r??'],
                ['CHIA SEEDS. DRIED', 'nb_NO', 'chiafr??'],
                ['Chicken filet. natural', 'nb_NO', 'Kylling. fersk filet'],
                ['Mixed greens', 'nb_NO', 'Blandet gr??nt'],
                ['Cottage cheese. 1.5%', 'nb_NO', 'Cottage cheese 1.5%'],
                ['Muesli. low in fat & sugar', 'nb_NO', 'Musli. lite fett og tilsatt sukker'],
                ['Coconut milk. light. canned', 'nb_NO', 'Kokosmelk . lett. boks'],
            ];
    }
}
