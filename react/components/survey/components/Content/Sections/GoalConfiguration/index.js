import React, {Fragment, useEffect} from 'react';
import './stylels.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import _ from 'lodash';
import {setValue} from "../../../../store/survey/actions";
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import {Button} from "../../../common/ui/Button";
import Slider from "../../../common/inputs/Slider";

const IMG_1 = 'data:image/png;base64,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';
const IMG_2 = 'data:image/png;base64,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';

const MODE_LOSE_FAT = 1;
const MODE_GAIN_MUSCLE = 2;

const GOAL_CHEST      = 'chest';
const GOAL_SHOULDERS  = 'shoulders';
const GOAL_BACK       = 'back';
const GOAL_LEGS       = 'legs';
const GOAL_ARMS       = 'arms';
const GOAL_CALFS      = 'calfs';

const GOAL_OPTIONS = [
  GOAL_CHEST,
  GOAL_SHOULDERS,
  GOAL_BACK,
  GOAL_LEGS,
  GOAL_ARMS,
  GOAL_CALFS
];

const PRIMARY_GOAL_OPTIONS = [1,2,3,4,5];
const DEFAULT_PRIMARY_GOAL = 3;


const GoalCard = ({title, selected, img, onSelect}) => {
  return (
    <div
      className={`goal-mode-card ${selected ? 'active' : ''}`}
      onClick={() => onSelect()}
    >
      <img src={img} alt={title} />
      <p>{title}</p>
    </div>
  )
};

const GoalSelection = (props) => {
  const {t} = useTranslation('main');
  const {
    goalType,
    goalParts,
    primaryGoal,
    setPrimaryGoal = () => null,
    setGoalType = () => null,
    setGoalParts = () => null
  } = props;

  const handleModeChange = (value) => {
    const type = goalType === value ? null : value;
    setGoalType(type);
  };

  useEffect(() => {
    if(goalType === MODE_GAIN_MUSCLE) {
      const option = primaryGoalOptions.find(({value}) => value === 4);
      setPrimaryGoal(option);
    } else if (goalType === MODE_LOSE_FAT) {
      const option = primaryGoalOptions.find(({value}) => value === 2);
      setPrimaryGoal(option);
    }
  }, [goalType]);
  const handlePrimaryGoal = (key) => {
    const option = primaryGoalOptions.find(({value}) => value === key);
    setPrimaryGoal(option);
  };

  const handleGoalPart = (option) => {
    let list = [];
    const isPresent = !!goalParts.find(part => part.value === option.value);
    if(isPresent) {
      list = goalParts.filter(part => part.value !== option.value);
    } else {
      list = [...goalParts, option];
    }
    setGoalParts(list.map(({name, value}) => ({name, value})));
  };

  const goalOptions = GOAL_OPTIONS.map((value) => {
    const name = t(`goal.extras.options.${value}`);
    const selected = !!goalParts.find(part => part.value === value);
    return { name, value, selected };
  });

  const primaryGoalOptions = PRIMARY_GOAL_OPTIONS.map((value) => {
    const name = t(`goal.intensity.options.${value}`);
    const selected = primaryGoal.value === value;
    return { name, value, selected };
  });

  return (
    <Section id="goal-configuration">
      <SectionHeader>
        <SectionTitle>
          {t('goal.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <div className={'goal-cards'}>
          <GoalCard
            img={IMG_1}
            title={t('goal.types.loseFat.title')}
            selected={goalType === MODE_LOSE_FAT}
            onSelect={() => handleModeChange(MODE_LOSE_FAT)}
          />
          <GoalCard
            img={IMG_2}
            title={t('goal.types.gainMuscle.title')}
            selected={goalType === MODE_GAIN_MUSCLE}
            onSelect={() => handleModeChange(MODE_GAIN_MUSCLE)}
          />
        </div>
        {
          !!goalType && (
            <Fragment>
              <h6>{t('goal.intensity.title')}</h6>
              <Slider
                min={1}
                value={primaryGoal.value || 3}
                max={primaryGoalOptions.length}
                onChange={handlePrimaryGoal}
              />
              <p>{primaryGoal.name}</p>
              <br/>
              <br/>
            </Fragment>
          )
        }
        <h5>{t('goal.extras.title')}</h5>
        <div className={'extra-parts'}>
          {
            goalOptions.map((option, i) => (
              <Button
                key={i}
                variant={'primary'}
                inverse={!option.selected}
                onClick={() => handleGoalPart(option)}
              >
                {option.name}
              </Button>
            ))
          }
        </div>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  goalType: _.get(state.survey, 'data.goalType', undefined),
  primaryGoal: _.get(state.survey, 'data.primaryGoal', {}),
  goalParts: _.get(state.survey, 'data.goalParts', []),
});

const mapDispatchToProps = dispatch => ({
  setGoalType: value => dispatch(setValue('goalType', value)),
  setPrimaryGoal: value => dispatch(setValue('primaryGoal', value)),
  setGoalParts: value => dispatch(setValue('goalParts', value)),
});

export default connect(mapStateToProps, mapDispatchToProps)(GoalSelection);