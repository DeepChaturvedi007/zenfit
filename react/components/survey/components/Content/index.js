import React, {Fragment} from 'react';
import _ from "lodash";
import {
  PlanSelection as PlanSelectionSection,
  GenderSelection as GenderSelectionSection,
  PersonalInformation as PersonalInformationSection,
  GoalConfiguration as GoalConfigurationSection,
  BodyConfiguration as BodyConfigurationSection,
  ActivityConfiguration as ActivityConfigurationSection,
  MealConfiguration as MealConfigurationSection,
  WorkoutConfiguration as WorkoutConfigurationSection,
  AdditionalInfo as AdditionalInfoSection,
  ConfirmAndSubmit as ConfirmAndSubmitSection,
  Heading as HeaderSection,
  HowItWorks as HowItWorksSection,
  BodyPictures as BodyPicturesSection,
} from './Sections';
import {connect} from "react-redux";
import {
  PLAN_MEAL,
  PLAN_BOTH,
  PLAN_WORKOUT
} from './Sections/PlanSelection';

const WHITELISTED_TYPES = [PLAN_BOTH,4];
const MEAL_STACK = [PLAN_MEAL, ...WHITELISTED_TYPES];
const WORKOUT_STACK = [PLAN_WORKOUT, ...WHITELISTED_TYPES];

const Content = (props) => {
  const {
    shouldShowMealSection,
    shouldShowWorkoutSection,
    shouldShowPlanSelection,
    shouldShowHeading
  } = props;
  return (
    <Fragment>
      {!!shouldShowHeading && <HeaderSection />}
      {!!shouldShowPlanSelection && <HowItWorksSection />}
      {!!shouldShowPlanSelection && <PlanSelectionSection />}
      <GenderSelectionSection />
      <PersonalInformationSection />
      <GoalConfigurationSection />
      <BodyConfigurationSection />
      <BodyPicturesSection />
      <ActivityConfigurationSection />
      {
        !!shouldShowMealSection &&
        <MealConfigurationSection/>
      }
      {
        !!shouldShowWorkoutSection &&
        <WorkoutConfigurationSection />
      }
      <AdditionalInfoSection />
      <ConfirmAndSubmitSection />
    </Fragment>
  )
};

const mapStateToProps = (state) => {
  const chosenPlanId = _.get(state.config, 'bundle', undefined);
  const plans = _.get(state.config, 'plans');
  let { type } = plans.find(plan => +plan.id === +chosenPlanId) || {};
  const definedPlan = _.get(state.config, 'bundle');
  if(!type && definedPlan) {
    type = definedPlan.type
  }
  return {
    shouldShowMealSection: !!type && MEAL_STACK.includes(type),
    shouldShowWorkoutSection: !!type && WORKOUT_STACK.includes(type),
    shouldShowPlanSelection: !definedPlan && !!plans.length,
    shouldShowHeading: !definedPlan,
  }
};


export default connect(mapStateToProps)(Content);