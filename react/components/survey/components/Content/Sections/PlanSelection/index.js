import './styles.scss';
import React, {useEffect} from 'react';
import {connect} from "react-redux";
import { useTranslation } from 'react-i18next';

import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader,
} from "../../../common/ui/Section";
import Card, {
  Body as CardBody,
  Header as CardHeader,
  Title as CardTitle,
  Footer as CardFooter,
} from "../../../common/ui/Card";
import {Button} from "../../../common/ui/Button";
import _ from "lodash";
import {setValue, flushMealData, flushWorkoutData} from "../../../../store/survey/actions";
import {currencyCode} from "../../../../helpers";

export const PLAN_WORKOUT = 1;
export const PLAN_MEAL = 2;
export const PLAN_BOTH = 3;

const PlanCard = (props) => {
  const {
    plan,
    discount,
    isSelected,
    isPopular,
    onSelect
  } = props;

  const {t} = useTranslation('main');
  const classes = [];
  const {
    id,
    upfrontFee: amount,
    name: title,
    description,
    currency
  } = plan;
  const unit = currencyCode(currency);

  if(isSelected) classes.push('selected');
  if(isPopular) classes.push('popular');

  return (
    <Card className={classes.join(' ')}>
      {
        !!isPopular && (
          <CardHeader>
            <CardTitle>
              {t('plans.cards.general.label.popular')}
            </CardTitle>
          </CardHeader>
        )
      }
      <CardBody>
        <p className={'title'} dangerouslySetInnerHTML={{__html: t('plans.cards.title', {title})}} />
        <span className={'price'}>
          {t('plans.cards.general.price', { unit, amount })}
        </span>
        {
          !!discount && (
            <i className={'discount'}>
              { t('plans.cards.general.discount', { unit, amount: discount }) }
            </i>
          )
        }
        <br/>
        {
          !!description &&
          <p className={'description'} dangerouslySetInnerHTML={{__html: t('plans.cards.description', {description})}} />
        }
        <br/>
        <Button
          variant={'primary'}
          width={100}
          inverse={!isSelected}
          onClick={() => onSelect(id)}
        >
          {
            isSelected ?
              t('plans.cards.general.action.active') :
              t('plans.cards.general.action.default')
          }
        </Button>
      </CardBody>
      {
        !!isPopular && (
          <CardFooter />
        )
      }
    </Card>
  );
};
const PlanSection = ({plans = [], plan, setPlan, flushMealData, flushWorkoutData}) => {
  const {t} = useTranslation('main');

  const mealPlan    = plans.find(item => item.type === PLAN_MEAL) || {};
  const workoutPlan = plans.find(item => item.type === PLAN_WORKOUT) || {};
  const bothPlans   = plans.find(item => item.type === PLAN_BOTH) || {};

  const handlePlan = (id) => {
    setPlan(id);
    switch (id) {
      case workoutPlan.id: {
        flushMealData();
        break;
      }
      case mealPlan.id: {
        flushWorkoutData();
        break;
      }
    }
  };

  useEffect(() => {
    if(plans.length && !plan && !!bothPlans) {
      setPlan(bothPlans.id)
    }
  }, [plans, plan]);

  // Don't show a section if user's plans not exists
  if(!mealPlan || !workoutPlan || !bothPlans) return null;

  return (
    <Section id={'plan-selection'}>
      <SectionHeader style={{marginBottom: '40px'}}>
        <SectionTitle>
          {t('plans.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <div className={'plans-wrapper'}>
          <PlanCard
            plan={mealPlan}
            isSelected={mealPlan.id === plan}
            onSelect={handlePlan}
          />
          <PlanCard
            plan={bothPlans}
            discount={+workoutPlan.upfrontFee + +mealPlan.upfrontFee - +bothPlans.upfrontFee}
            isSelected={bothPlans.id === plan}
            onSelect={handlePlan}
            isPopular
          />
          <PlanCard
            plan={workoutPlan}
            isSelected={workoutPlan.id === plan}
            onSelect={handlePlan}
          />
        </div>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  plan: _.get(state.survey, 'data.bundle', undefined),
  plans: _.get(state.config, 'plans', [])
    .map(plan => ({
      ...plan,
      name: (plan.name || '').replace(/(?:\r\n|\r|\n)/g, '<br>'),
      description: (plan.description || '').replace(/(?:\r\n|\r|\n)/g, '<br>'),
    }))
});

const mapDispatchToProps = dispatch => ({
  setPlan: value => dispatch(setValue('bundle', value)),
  flushMealData: () => dispatch(flushMealData()),
  flushWorkoutData: () => dispatch(flushWorkoutData()),
});

export default connect(mapStateToProps, mapDispatchToProps)(PlanSection);