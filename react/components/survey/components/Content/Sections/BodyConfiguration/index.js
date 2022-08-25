import './stylels.scss';
import React, { useEffect, useState, useMemo } from 'react';
import {connect} from "react-redux";
import {useTranslation} from "react-i18next";
import _ from "lodash";
import {
  Section,
  Title as SectionTitle,
  Body as SectionBody,
  Header as SectionHeader
} from "../../../common/ui/Section";
import {Col, Row} from "../../../../../shared/components/Grid";
import Select from "../../../common/inputs/Select";
import {heightToFeetAndInches} from '../../../../helpers';
import {setValue, setFieldError, unsetFieldError} from "../../../../store/survey/actions";
import Radio from '@material-ui/core/Radio';
import RadioGroup from '@material-ui/core/RadioGroup';
import FormControlLabel from '@material-ui/core/FormControlLabel';
import FormControl from '@material-ui/core/FormControl';
import { createMuiTheme, ThemeProvider } from '@material-ui/core/styles';

const kgToLbs = (kg) => Math.round(Number(kg) * 2.20462262185);
const cmToFeetAndInches = (cm) => {
  const {
    feet,
    inches
  } = heightToFeetAndInches(Number(cm));
  return `${feet}' ${inches}"`
};

const heightOptions = [];
for(let i = 100; i <= 240; i++) {
  heightOptions.push({
    value: i,
    name: `${i} cm (${cmToFeetAndInches(i)})`
  })
}

const fatPercentageOptions = [
  {
    name: `I don't know my fat %`,
    value: 0
  }
];
for(let i = 1; i <= 100; i++) {
  fatPercentageOptions.push({
    value: i,
    name: `${i} %`
  })
}

const MeasurementSelect = ({value, onChange}) => {
  const {t} = useTranslation('main');

  const theme = createMuiTheme({
    overrides: {
      MuiFormControl: {
        root: {
          margin: '20px 0',
          width: "100%",
        }
      },
      MuiFormGroup: {
        root: {
          display: 'flex',
          flexDirection: 'row',
          justifyContent: 'center',
          alignItems: 'center'
        }
      },
      MuiFormControlLabel: {
        root: {
          display: 'flex',
        },
        label: {
          fontWeight: 'bold',
          fontSize: "12px",
          textTransform: "uppercase",
          marginLeft: 0,
          marginRight: 0,
        }
      },
      MuiSvgIcon: {
        root: {
          fontSize: '20px'
        }
      }
    }
  });
  const handleMeasurement = (val) => {
    onChange(val.target.value)
  };

  return (
    <ThemeProvider theme={theme}>
      <FormControl component="fieldset">
        <RadioGroup aria-label="measurement" name="measurement" value={`${value}`} onChange={handleMeasurement}>
          <FormControlLabel value={'2'} control={<Radio color={'primary'}/>} label={t('body.measurement.options.us')} />
          <FormControlLabel value={'1'} control={<Radio color={'primary'}/>} label={t('body.measurement.options.metric')} />
        </RadioGroup>
      </FormControl>
    </ThemeProvider>
  );
};

const BodyConfiguration = (props) => {

  const {t} = useTranslation('main');
  const {t: tMessages} = useTranslation('messages');

  const {
    startWeight,
    goalWeight,
    height,
    fatPercentage,
    measuringSystem,
    setStartWeight = () => null,
    setGoalWeight = () => null,
    setHeight = () => null,
    setFatPercentage = () => null,
    setMeasuringSystem = () => null,
    errors = [],
    setFieldError = () => null,
    unsetFieldError = () => null,
  } = props;
  const [errorsObj, setErrorsObj] = useState({});
  const handleStartWeight = (option) => {
    setStartWeight(option.value)
  };

  const handleGoalWeight = (option) => {
    setGoalWeight(option.value)
  };

  const handleHeight = (option) => {
    setHeight(option.value)
  };

  const handleFatPercentage = (option) => {
    setFatPercentage(option.value)
  };

  const handleMeasurement = (value) => {
    setMeasuringSystem(+value)
  };

  useEffect(() => {
    if(!measuringSystem) setMeasuringSystem(1);
  }, [measuringSystem]);

  useEffect(() => {
    const obj = {};
    errors.forEach(({field, type}) => {
      obj[field] = tMessages(`errors.fields.${field}.${type}`)
    });
    setErrorsObj(obj)
  }, [errors]);

  const weightOptions = useMemo(() => {
    let options = [];
    if(+measuringSystem === 1) {
      for(let i = 45; i <= 200; i++) {
        options.push({
          value: i,
          name: `${i} kg (${kgToLbs(i)} lbs)`
        })
      }
    } else {
      for(let i = 45; i <= 200; i++) {
        options.push({
          value: kgToLbs(i),
          name: `${kgToLbs(i)} lbs (${i} kg)`
        })
      }
    }
    return options;
  }, [measuringSystem]);

  return (
    <Section id={'body-configuration'}>
      <SectionHeader>
        <SectionTitle>
          {t('body.title')}
        </SectionTitle>
      </SectionHeader>
      <br/>
      <SectionBody>
        <h6>{t('body.measurement.title')}</h6>
        <MeasurementSelect value={measuringSystem} onChange={handleMeasurement}/>
        <Row>
          <Col size={6}>
            <Select
              id='select-user-start-weight'
              label={t('body.personParams.inputs.startWeight.label')}
              value={startWeight}
              onChange={handleStartWeight}
              required
              options={weightOptions}
              error={errorsObj.startWeight}
              onFocus={() => unsetFieldError('startWeight')}
            />
          </Col>
          <Col size={6}>
            <Select
              id='select-user-goal-weight'
              label={t('body.personParams.inputs.goalWeight.label')}
              value={goalWeight}
              onChange={handleGoalWeight}
              required
              options={weightOptions}
              error={errorsObj.goalWeight}
              onFocus={() => unsetFieldError('goalWeight')}
            />
          </Col>
          <Col size={6}>
            <Select
              id='select-user-height'
              label={t('body.personParams.inputs.height.label')}
              value={height}
              onChange={handleHeight}
              required
              options={heightOptions}
              error={errorsObj.height}
              onFocus={() => unsetFieldError('height')}
            />
          </Col>
          <Col size={6}>
            <Select
              id='select-user-fat-percentage'
              label={t('body.personParams.inputs.fat.label')}
              value={fatPercentage}
              onChange={handleFatPercentage}
              options={fatPercentageOptions}
              error={errorsObj.fatPercentage}
              onFocus={() => unsetFieldError('fatPercentage')}
            />
          </Col>
        </Row>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = (state) => ({
  startWeight: _.get(state.survey, 'data.startWeight', undefined),
  goalWeight: _.get(state.survey, 'data.goalWeight', undefined),
  height: _.get(state.survey, 'data.height', undefined),
  fatPercentage: _.get(state.survey, 'data.fatPercentage', undefined),
  measuringSystem: _.get(state.survey, 'data.measuringSystem', undefined),
  errors: _.get(state.survey, 'error.errors', [])
});

const mapDispatchToProps = dispatch => ({
  setStartWeight: (value) => dispatch(setValue('startWeight', Number(value))),
  setGoalWeight: (value) => dispatch(setValue('goalWeight', Number(value))),
  setHeight: (value) => dispatch(setValue('height', Number(value))),
  setFatPercentage: (value) => dispatch(setValue('fatPercentage', value)),
  setMeasuringSystem: (value) => dispatch(setValue('measuringSystem', value)),
  setFieldError: (field, message) => dispatch(setFieldError(field, message)),
  unsetFieldError: (field) => dispatch(unsetFieldError(field)),
});


export default connect(mapStateToProps, mapDispatchToProps)(BodyConfiguration);