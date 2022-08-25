<?php

namespace AppBundle\Command;

use AppBundle\Fixture\DefaultMessagesFixturesLoader;
use AppBundle\Fixture\DemoClientFixturesLoader;
use AppBundle\Fixture\EquipmentsFixturesLoader;
use AppBundle\Fixture\EventsFixturesLoader;
use AppBundle\Fixture\ExercisesFixturesLoader;
use AppBundle\Fixture\ExerciseTypeFixturesLoader;
use AppBundle\Fixture\LanguagesFixturesLoader;
use AppBundle\Fixture\SubscriptionsFixturesLoader;
use AppBundle\Fixture\WorkoutPlanFixturesLoader;
use AppBundle\Fixture\MealProductFixturesLoader;
use AppBundle\Fixture\MealProductLanguageFixturesLoader;
use AppBundle\Fixture\MealProductsWeightsFixturesLoader;
use AppBundle\Fixture\MuscleGroupsFixturesLoader;
use AppBundle\Fixture\RecipeFixturesLoader;
use AppBundle\Fixture\RecipeProductFixturesLoader;
use AppBundle\Fixture\WorkoutTypeFixturesLoader;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadDevFixturesCommand extends CommandBase
{
    /** @var string */
    protected static $defaultName = 'zf:fixtures:load';

    public function __construct(
        private RecipeFixturesLoader $recipeFixturesLoader,
        private MealProductFixturesLoader $mealProductFixturesLoader,
        private MealProductsWeightsFixturesLoader $mealProductsWeightsFixturesLoader,
        private LanguagesFixturesLoader $languageFixturesLoader,
        private EquipmentsFixturesLoader $equipmentsFixturesLoader,
        private MuscleGroupsFixturesLoader $muscleGroupFixturesLoader,
        private MealProductLanguageFixturesLoader $mealProductLanguageFixturesLoader,
        private RecipeProductFixturesLoader $recipeProductFixturesLoader,
        private ExerciseTypeFixturesLoader $exerciseTypeFixturesLoader,
        private ExercisesFixturesLoader $exercisesFixturesLoader,
        private DemoClientFixturesLoader $demoClientFixturesLoader,
        private WorkoutTypeFixturesLoader $workoutTypeFixturesLoader,
        private EventsFixturesLoader $eventsFixturesLoader,
        private WorkoutPlanFixturesLoader $workoutPlanFixturesLoader,
        private DefaultMessagesFixturesLoader $defaultMessagesFixturesLoader,
        private SubscriptionsFixturesLoader $subscriptionsFixturesLoader
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->subscriptionsFixturesLoader)();
        ($this->equipmentsFixturesLoader)();
        ($this->recipeFixturesLoader)();
        ($this->mealProductFixturesLoader)();
        ($this->mealProductsWeightsFixturesLoader)();
        ($this->languageFixturesLoader)();
        ($this->muscleGroupFixturesLoader)();
        ($this->eventsFixturesLoader)();
        ($this->mealProductLanguageFixturesLoader)();
        ($this->recipeProductFixturesLoader)();
        ($this->exerciseTypeFixturesLoader)();
        ($this->workoutTypeFixturesLoader)();
        ($this->exercisesFixturesLoader)();
        ($this->demoClientFixturesLoader)();
        ($this->workoutPlanFixturesLoader)();
        ($this->defaultMessagesFixturesLoader)();

        return 0;
    }
}
