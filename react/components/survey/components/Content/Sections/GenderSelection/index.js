import React, {useEffect} from 'react';
import _ from 'lodash';
import './styles.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {connect} from 'react-redux'

import {Button} from "../../../common/ui/Button";
import {setValue, unsetFieldError} from "../../../../store/survey/actions";
import {useTranslation} from "react-i18next";
import {FormHelperText} from "@material-ui/core";

const GENDER_MALE = 2;
const GENDER_FEMALE = 1;

const Gender = ({gender, setGender, error, unsetError}) => {
  const {t} = useTranslation('main');
  const {t: tMessages} = useTranslation('messages');

  const isMale = gender === GENDER_MALE;
  const isFemale = gender === GENDER_FEMALE;
  useEffect(() => {
    if(gender) {
      unsetError();
    }
  }, [gender])
  const errorMessage = error ? tMessages(`errors.fields.${error.field}.${error.type}`) : null;
  return (
    <Section id={'gender-configuration'}>
      <SectionHeader>
        <SectionTitle>
          {t('gender.title')}
        </SectionTitle>
      </SectionHeader>
      <br/>
      <br/>
      <SectionBody>
        <div className="options">
          <Button
            variant={'primary'}
            inverse={!isMale}
            onClick={() => setGender(GENDER_MALE)}
          >
            {t('gender.options.male')}
          </Button>
          <Button
            variant={'primary'}
            inverse={!isFemale}
            onClick={() => setGender(GENDER_FEMALE)}
          >
            {t('gender.options.female')}
          </Button>
        </div>
        {!!errorMessage && <FormHelperText error={!!errorMessage} style={{textAlign: 'center'}}>{errorMessage}</FormHelperText>}
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => {
  const error = _.get(state.survey, 'error.errors', []).find(({field}) => field === 'gender');
  return {
    gender: _.get(state.survey, 'data.gender', null),
    error
  }
};

const mapDispatchToProps = dispatch => ({
  setGender: value => dispatch(setValue('gender', value)),
  unsetError: value => dispatch(unsetFieldError('gender'))
});

export default connect(mapStateToProps, mapDispatchToProps)(Gender);