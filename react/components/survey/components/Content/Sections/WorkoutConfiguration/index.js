import React, {useEffect} from 'react';
import './stylels.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import Slider from '../../../common/inputs/Slider';
import _ from "lodash";
import {setValue} from "../../../../store/survey/actions";
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import Text from "../../../common/inputs/Text";
import { normalizeText } from '../../../../helpers'
import {Button} from "../../../common/ui/Button";

const EXPERIENCE_OPTIONS = [1,2,3,4,5];
const PLACE_OPTIONS = [1,2,3];

const WorkoutConfiguration = (props) => {
  const {t} = useTranslation('main');
  const {
    workouts,
    experience,
    comment = '',
    place,
    setWorkouts = () => null,
    setExperience = () => null,
    setComment = () => null,
    setPlace = () => null,
  } = props;

  const handleExperience = (value) => {
    const item = experienceOptions.find(option => option.value === value);
    setExperience(item.name);
  };

  const handleCommentChange = (comment) => {
    const normalized = normalizeText(comment);
    setComment(normalized)
  };

  const handlePlace = option => {
    setPlace(option.value)
  };

  const experienceOptions = EXPERIENCE_OPTIONS.map((value) => ({
    name: t(`workout.experience.options.${value}`),
    value
  }));
  const placeOptions = PLACE_OPTIONS.map((value) => ({
    name: t(`workout.place.options.${value}`),
    value
  }));
  const currentExperience = experienceOptions.find(option => option.name === experience) || experienceOptions[2];
  const currentPlace = placeOptions.find(option => option.value === place) || {};

  useEffect(() => {
    if(!workouts)   setWorkouts(4);
    if(!experience) {
      const defaultExperience = experienceOptions.find(option => option.value === 3);
      setExperience(defaultExperience.name);
    }
  }, [workouts, experience]);

  return (
    <Section id={'workout-configuration'}>
      <SectionHeader>
        <SectionTitle>
          {t('workout.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <div className={'sub-wrapper'}>
          <br/>
          <br/>
          <h6>{t('workout.intensity.title')}</h6>
          <Slider
            value={workouts}
            min={1}
            max={7}
            step={1}
            onChange={(value) => setWorkouts(value)}
          />
          <p
            className={'slider-result'}
            dangerouslySetInnerHTML={{__html: t(`workout.intensity.options.general`, {value: workouts})}}
          />
          <br/>
          <br/>
          <h6>{t('workout.experience.title')}</h6>
          <Slider
            value={currentExperience.value}
            min={1}
            max={experienceOptions.length}
            step={1}
            onChange={handleExperience}
          />
          <p
            className={'slider-result'}
            dangerouslySetInnerHTML={{__html: currentExperience.name}}
          />
          <br/>
          <br/>
          <h6>{t('workout.place.title')}</h6>
          <div className={'extra-parts'}>
            {
              placeOptions.map((option, i) => (
                <Button
                  key={i}
                  variant={'primary'}
                  value={option.value}
                  inverse={option.value !== currentPlace.value}
                  onClick={() => handlePlace(option)}
                >
                  {option.name}
                </Button>
              ))
            }
          </div>
          <br/>
          <br/>
          <h6>{t('workout.comment.title')}</h6>
          <Text
            label={t('workout.comment.label')}
            value={comment}
            multiline
            helperText={t('workout.comment.description')}
            onChange={(comment) => handleCommentChange(comment)}
          />
        </div>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  workouts: _.get(state.survey, 'data.workoutsPerWeek', undefined),
  experience: _.get(state.survey, 'data.experience', undefined),
  place: _.get(state.survey, 'data.place', undefined),
  comment: _.get(state.survey, 'data.exercisePreferences', ''),
});

const mapDispatchToProps = dispatch => ({
  setWorkouts: (value) => dispatch(setValue('workoutsPerWeek', value)),
  setExperience: (value) => dispatch(setValue('experience', value)),
  setComment: (value) => dispatch(setValue('exercisePreferences', value)),
  setPlace: (value) => dispatch(setValue('place', value)),
});


export default connect(mapStateToProps, mapDispatchToProps)(WorkoutConfiguration);
