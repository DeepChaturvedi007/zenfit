import './stylels.scss';
import React, {useEffect} from 'react';
import _ from "lodash";
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import Slider from '../../../common/inputs/Slider';
import {setValue} from "../../../../store/survey/actions";

const ACTIVITY_OPTIONS = [1,2,3,4,5];
const DEFAULT_ACTIVITY = 3;

const ActivityConfiguration = ({activity, setActivity}) => {
  const {t} = useTranslation('main');

  const handleActivity = (key) => {
    const option = activityOptions.find(({value}) => value === key);
    setActivity(option);
  };

  const activityOptions = ACTIVITY_OPTIONS.map((value) => {
    const name = t(`activity.intensity.options.${value}`);
    const selected = activity.value === value;
    return { name, value, selected };
  });

  useEffect(() => {
    if(!activity || !activity.value) {
      const option = activityOptions.find(({value}) => value === DEFAULT_ACTIVITY);
      setActivity(option)
    }
  }, [activity]);

  return (
    <Section id={'activity-configuration'}>
      <SectionHeader>
        <SectionTitle>
          {t('activity.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        <h6>{t('activity.intensity.title')}</h6>
        <Slider
          min={1}
          value={activity.value}
          max={activityOptions.length}
          onChange={handleActivity}
        />
        <p>{activity.name}</p>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  activity: _.get(state.survey.data, 'activity', {}),
});

const mapDispatchToProps = dispatch => ({
  setActivity: value => dispatch(setValue('activity', value)),
});

export default connect(mapStateToProps, mapDispatchToProps)(ActivityConfiguration);