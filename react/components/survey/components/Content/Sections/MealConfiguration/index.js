import React, {useEffect} from 'react';
import './stylels.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {Button} from "../../../common/ui/Button";
import _ from "lodash";
import {setValue} from "../../../../store/survey/actions";
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import Slider from "../../../common/inputs/Slider";
import Text from "../../../common/inputs/Text";
import IngredientsAutocomplete from './IngredientsAutocomplete'
import { normalizeText } from '../../../../helpers'

const NO_LACTOSE        = 'avoidLactose';
const NO_GLUTEN         = 'avoidGluten';
const NO_NUTS           = 'avoidNuts';
const NO_EGGS           = 'avoidEggs';
const NO_PIG            = 'avoidPig';
const NO_SHELLFISH      = 'avoidShellfish';
const NO_FISH           = 'avoidFish';
const IS_VEGETARIAN     = 'isVegetarian';
const IS_VEGAN          = 'isVegan';
const IS_PESCETARIAN    = 'isPescetarian';

const OPTIONS = [
  NO_FISH,
  NO_NUTS,
  NO_EGGS,
  NO_SHELLFISH,
  NO_GLUTEN,
  NO_PIG,
  NO_LACTOSE,
  IS_VEGETARIAN,
  IS_VEGAN,
  IS_PESCETARIAN
];

const BUDGET_OPTIONS = [1,2];
const COOKING_TIME_OPTIONS = [1,2];
const MEALS_COUNT_OPTIONS = [3,4,5,6];

const MealConfiguration = (props) => {
  const {t} = useTranslation('main');
  const {
    allergens = [],
    comment = '',
    cookingTime,
    budget,
    excludeIngredients = [],
    mealsCount,
    setExcludeIngredients = () => null,
    setBudget = () => null,
    setCookingTime = () => null,
    setAllergens = () => null,
    setComment = () => null,
    setMealsCount = () => null
  } = props;

  const handlePreference = (value) => {
    if(allergens.includes(value)) {
      return setAllergens(allergens.filter(item => item !== value));
    }
    return setAllergens([...allergens, value]);
  };

  const handleCommentChange = (value) => {
    const normalized = normalizeText(value);
    setComment(normalized)
  };

  const handleBudget = (value) => {
    const item = budgetOptions.find(option => option.value === value);
    setBudget(item.name);
  };

  const handleCookingTime = (value) => {
    const item = cookingTimeOptions.find(option => option.value === value);
    setCookingTime(item.name);
  };

  const handleMealsCount = (value) => {
    const item = mealsCountOptions.find(option => option.value === value);
    setMealsCount(item.value);
  };

  const budgetOptions = BUDGET_OPTIONS.map((value) => ({
    name: t(`meal.budget.options.${value}`),
    value
  }));
  const cookingTimeOptions = COOKING_TIME_OPTIONS.map((value) => ({
    name: t(`meal.cookingTime.options.${value}`),
    value
  }));
  const mealsCountOptions = MEALS_COUNT_OPTIONS.map((value) => ({
    name: t(`meal.count.options.${value}`),
    value
  }));

  const currentBudget = budgetOptions
    .find(option => option.name === budget) ||
    budgetOptions[budgetOptions.length - 1];

  const currentCookingTime = cookingTimeOptions
    .find(option => option.name === cookingTime) ||
    cookingTimeOptions[cookingTimeOptions.length - 1];

  const currentMealsCount = mealsCountOptions
      .find(option => option.value === mealsCount) ||
    mealsCountOptions[0];

  useEffect(() => {
    if(!budget) {
      const item = budgetOptions.find(option => option.value === 2);
      setBudget(item.name);
    }
    if(!cookingTime) {
      const item = cookingTimeOptions.find(option => option.value === 2);
      setCookingTime(item.name);
    }
    if(!mealsCount) {
      const item = mealsCountOptions.find(option => option.value === 3);
      setMealsCount(item.value);
    }
  }, [budget, cookingTime]);
  return (
    <Section id={'meal-configuration'}>
      <SectionHeader>
        <SectionTitle>
          {t('meal.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <div className={'extra-parts'}>
          {
            OPTIONS.map((option, i) => (
              <Button
                key={i}
                variant={'primary'}
                inverse={!allergens.includes(option)}
                onClick={() => handlePreference(option)}
              >
                {t(`meal.allergens.options.${option}`)}
              </Button>
            ))
          }
        </div>
        <div className={'sub-wrapper'}>
          <br/>
          <br/>
          <h6>{t('meal.exclusion.title')}</h6>
          <IngredientsAutocomplete
            values={excludeIngredients}
            onChange={setExcludeIngredients}
          />
          <br/>
          <br/>
          <h6>{t('meal.count.title')}</h6>
          <Slider
            value={currentMealsCount.value}
            min={3}
            max={6}
            step={1}
            onChange={handleMealsCount}
          />
          <div className={'clarifications'}>
            <p />
            <p dangerouslySetInnerHTML={{__html: currentMealsCount.name}} />
            <p />
          </div>
          <br/>
          <br/>
          <h6>{t('meal.cookingTime.title')}</h6>
          <Slider
            value={currentCookingTime.value}
            min={1}
            max={2}
            step={1}
            onChange={handleCookingTime}
          />
          <div className={'clarifications'}>
            <p dangerouslySetInnerHTML={{__html: t('meal.cookingTime.clarifications.prefix')}} />
            <p dangerouslySetInnerHTML={{__html: t('meal.cookingTime.clarifications.suffix')}} />
          </div>
          <br/>
          <br/>
          <h6>{t('meal.comment.title')}</h6>
          <Text
            label={t('meal.comment.input.label')}
            value={comment}
            multiline
            helperText={t('meal.comment.description')}
            onChange={(comment) => handleCommentChange(comment)}
          />
        </div>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  allergens: _.get(state.survey, 'data.foodPreferences', []),
  comment: _.get(state.survey, 'data.dietStyle', ''),
  budget: _.get(state.survey, 'data.budget', undefined),
  cookingTime: _.get(state.survey, 'data.cookingTime', undefined),
  excludeIngredients: _.get(state.survey, 'data.excludeIngredients', []),
  mealsCount: _.get(state.survey, 'data.numberOfMeals', undefined),
});

const mapDispatchToProps = dispatch => ({
  setAllergens: (value = []) => dispatch(setValue('foodPreferences', value)),
  setComment: (value) => dispatch(setValue('dietStyle', value)),
  setCookingTime: (value) => dispatch(setValue('cookingTime', value)),
  setBudget: (value) => dispatch(setValue('budget', value)),
  setExcludeIngredients: (values = []) => dispatch(setValue('excludeIngredients', values)),
  setMealsCount: (values = []) => dispatch(setValue('numberOfMeals', values))
});

export default connect(mapStateToProps, mapDispatchToProps)(MealConfiguration);
