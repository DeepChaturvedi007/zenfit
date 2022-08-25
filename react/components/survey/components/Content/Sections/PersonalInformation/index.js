import React, {useEffect, useState} from 'react';
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import _ from "lodash";
import './styles.scss';
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {Col, Row} from "../../../../../shared/components/Grid";
import Text from '../../../common/inputs/Text'
import {setFieldError, setValue, unsetFieldError} from "../../../../store/survey/actions";
import Select from "../../../common/inputs/Select";

const AGE_OPTIONS = [];
for(let i = 15; i<=85; i++) {
  AGE_OPTIONS.push(i)
}

const validateEmail = (val) => {
  const pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return pattern.test(String(val).toLowerCase())
};

const PersonalInformation = (props) => {
  const {t} = useTranslation('main');
  const {t: tMessages} = useTranslation('messages');
  const {
    name = '',
    age = '',
    phone = '',
    email = '',
    setValue = (key, value) => null,
    errors = [],
    setFieldError = () => null,
    unsetFieldError = () => null,
  } = props;

  const [errorsObj, setErrorsObj] = useState({});
  const handleChange = (key, value) => {
    setValue(key, value);
  };

  useEffect(() => {
    const obj = {};
    errors.forEach(({field, type, message}) => {
      if(message) {
        obj[field] = message;
      } else if(type) {
        obj[field] = tMessages(`errors.fields.${field}.${type}`)
      } else {
        obj[field] = tMessages(`errors.default`)
      }
    });
    setErrorsObj(obj)
  }, [errors]);

  useEffect(() => {
    if(!!age) validateAge();
    if(!!name) validateName();
    if(!!email) validateEmail();
  }, [age, name, email])

  const ageOptions = AGE_OPTIONS.map(value => ({value, name: `${value}`}));

  const validateName = () => {
    if(!name) {
      return setFieldError('name', tMessages('errors.fields.name.required'))
    }
    return unsetFieldError('name')
  };

  const validateEmail = () => {
    if(!email) {
      return setFieldError('email', tMessages('errors.fields.email.required'))
    }
    const pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if(!pattern.test(String(email).toLowerCase())){
      return setFieldError('email', tMessages('errors.fields.email.invalid'))
    }
    return unsetFieldError('email')
  };

  const validateAge = () => {
    if(!age) {
      return setFieldError('age', tMessages('errors.fields.age.required'))
    }
    return unsetFieldError('age')
  };

  return (
    <Section id="personal-information">
      <SectionHeader>
        <SectionTitle>
          {t('personInfo.title')}
        </SectionTitle>
      </SectionHeader>
      <SectionBody className={'flex a-end j-center'}>
        <Row>
          <Col size={6} mode={'sm'}>
            <Text
              label={t('personInfo.inputs.name.label')}
              value={name}
              onChange={(value) => handleChange('name', value)}
              onFocus={() => unsetFieldError('name')}
              onBlur={() => validateName()}
              error={errorsObj.name}
              required
            />
          </Col>
          <Col size={6} mode={'sm'}>
            <Text
              label={t('personInfo.inputs.email.label')}
              value={email}
              onChange={(value) => handleChange('email', value)}
              onFocus={() => unsetFieldError('email')}
              onBlur={() => validateEmail()}
              error={errorsObj.email}
              required
            />
          </Col>
          <Col size={6} mode={'sm'}>
            <Text
              type={'tel'}
              label={t('personInfo.inputs.phone.label')}
              value={phone}
              onChange={(value) => handleChange('phone', value)}
              required
            />
          </Col>
          <Col size={6} mode={'sm'}>
            <Select
              id='select-user-age'
              label={t('personInfo.inputs.age.label')}
              value={age}
              onChange={({value}) => handleChange('age', value)}
              options={ageOptions}
              error={errorsObj.age}
              onFocus={() => unsetFieldError('age')}
              onBlur={() => validateAge()}
            />
          </Col>
        </Row>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  name: _.get(state.survey, 'data.name', undefined),
  age: _.get(state.survey, 'data.age', undefined),
  email: _.get(state.survey, 'data.email', undefined),
  phone: _.get(state.survey, 'data.phone', undefined),
  errors: _.get(state.survey, 'error.errors', [])
});

const mapDispatchToProps = dispatch => ({
  setValue: (key, value) => dispatch(setValue(key, value)),
  setFieldError: (field, message) => dispatch(setFieldError(field, message)),
  unsetFieldError: (field) => dispatch(unsetFieldError(field)),
});

export default connect(mapStateToProps, mapDispatchToProps)(PersonalInformation);