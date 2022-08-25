import React, {memo, useCallback, useRef, useMemo, useState} from 'react';
import delay from 'lodash/delay';
import get from 'lodash/get';
import {validateAll} from 'indicative/validator';
import {Formik} from 'formik';
import {Alert, Badge, Button, FlatButton, Link} from '../../components/UI';
import MealPlansContainer from '../../containers/MealPlans';
import {Feedback, FormGroup, FormLabel, Input, Switch, Select} from '../../components/Form';
import Popup from '../../Popup';
import styled from 'styled-components';

const FormContainer = styled.div`
  width: 232px;
  padding: 0 5px;

  ${FormGroup} {
    margin-bottom: 16px;
  }
`;

const FormActions = styled.div`
  text-align: right;
`;

const PlanSettings = memo(({plan, type, title, position}) => {
  const formikRef = useRef();
  const popupRef = useRef();
  const [canClose, setCanClose] = useState(true);
  const [approveWaiting, setApproveWaiting] = useState(false);
  const [error, setError] = useState(null);

  const {updateMealPlan} = MealPlansContainer.useContainer();

  const handleClose = useCallback(() => {
    if (popupRef.current) {
      delay(popupRef.current.close, 50);
    }
  }, []);

  const onSubmit = useCallback(async (values, actions) => {
    setCanClose(false);
    setError(null);

    let syncPlans = false;
    let confirmed = true;

    if (type === PlanSettings.TYPE_KCALS && plan.desired_kcals !== values.kcals
      ||type === PlanSettings.TYPE_MACROS && plan.avg_totals !== values.totals) {
      if (!window.confirm('This will update all meals and erase any changes you may have made')) {
        confirmed = false;
      }

      syncPlans = true;
    }

    if (confirmed) {
      if (type === PlanSettings.TYPE_MACROS) {
        values = {macros: values};

        if (approveWaiting) {
          values.approved = true;
        }
      }

      try {
        await updateMealPlan(plan.id, values, syncPlans);
        setApproveWaiting(false);
        delay(popupRef.current.close, 50);
      } catch (e) {
        let errors = get(e.response.data, 'errors', 0);

        if (errors > 0) {
          setApproveWaiting(true);
          setError(`${errors} recipes could not be generated, do you wish to go ahead anyway?`);
        }
      } finally {
        setCanClose(true);
        actions.setSubmitting(false);
      }
    }
  }, [plan, type, approveWaiting]);

  const onClose = useCallback(() => {
    if (approveWaiting) {
      formikRef.current.setValues(initialValues);
    }
    setError('');
    setApproveWaiting(false);
  }, [initialValues, approveWaiting]);

  /**
   * @param {Object} values
   *
   * @return {Promise<Object>}
   */
  const validate = useCallback((values) => {
    const rules = {
      [PlanSettings.TYPE_NAME]: {
        name: 'required',
      },
      [PlanSettings.TYPE_KCALS]: {
        kcals: 'required|number',
      },
      [PlanSettings.TYPE_MACROS]: {
        carbohydrate: 'required',
        protein: 'required',
        fat: 'required'
      },
      [PlanSettings.TYPE_STATUS]: {
        active: 'boolean',
      },
    }[type];

    return validateAll(values, rules || {})
      .then(() => undefined)
      .catch(reasons => {
        return reasons.reduce((collection, reason) => {
          collection[reason.field] = reason.message;
          return collection;
        }, {});
      })
      .then((errors) => {
        if (errors) {
          throw errors;
        }
      });
  }, [plan, type]);

  const isType = useCallback((value) => value === type, [type]);

  const initialValues = useMemo(() => {
    return {
      [PlanSettings.TYPE_NAME]: {
        name: plan.name,
      },
      [PlanSettings.TYPE_KCALS]: {
        kcals: plan.desired_kcals,
      },
      [PlanSettings.TYPE_MACROS]: {
        carbohydrate: plan.avg_totals.carbohydrate,
        protein: plan.avg_totals.protein,
        fat: plan.avg_totals.fat
      },
      [PlanSettings.TYPE_STATUS]: {
        active: plan.active,
      },
      [PlanSettings.TYPE_DURATION]: {
        duration: plan.meta && plan.meta.duration ? plan.meta.duration : 4
      },
    }[type] || {};
  }, [plan, type]);

  const renderTrigger = useCallback(() => (
    <Link href="#">{title}</Link>
  ), [title]);

  const renderBody = useCallback(() => {
    return (
      <Formik
        initialValues={initialValues}
        validate={validate}
        onSubmit={onSubmit}
        ref={formikRef}
      >
        {({
          values,
          errors,
          touched,
          handleChange,
          handleBlur,
          handleSubmit,
          isSubmitting
        }) => (
          <form onSubmit={handleSubmit}>
            <FormContainer>
              {isType(PlanSettings.TYPE_NAME) && (
                <FormGroup>
                  <FormLabel required>
                    Title of meal plan
                  </FormLabel>
                  <Input
                    name="name"
                    value={values.name}
                    type="text"
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                  {errors.name && touched.name ? (
                    <Feedback type="invalid">{errors.name}</Feedback>
                  ) : null}
                </FormGroup>
              )}
              {isType(PlanSettings.TYPE_KCALS) && (
                <FormGroup>
                  <FormLabel required>
                    Target
                    <Badge>Kcals</Badge>
                  </FormLabel>
                  <Input
                    name="kcals"
                    type="number"
                    min={0}
                    step={1}
                    value={values.kcals}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                  {errors.desired_kcals && touched.desired_kcals ? (
                    <Feedback type="invalid">{errors.desired_kcals}</Feedback>
                  ) : null}
                </FormGroup>
              )}
              {isType(PlanSettings.TYPE_DURATION) && (
                <FormGroup>
                  <FormLabel required>
                    Duration of meal plan
                  </FormLabel>
                  <Select
                    name="duration"
                    value={values.duration}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  >
                    {[4,6,8,12,16].map((option) => (
                      <option value={option} key={`week_${option}`}>
                        {option}
                      </option>
                    ))}
                  </Select>
                  {errors.name && touched.name ? (
                    <Feedback type="invalid">{errors.name}</Feedback>
                  ) : null}
                </FormGroup>
              )}
              {isType(PlanSettings.TYPE_MACROS) && (
                <FormGroup>
                  <FormLabel required>
                    <Badge>Carbs</Badge>
                  </FormLabel>
                  <Input
                    name="carbohydrate"
                    type="number"
                    value={values.carbohydrate}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                  <FormLabel required>
                    <Badge>Protein</Badge>
                  </FormLabel>
                  <Input
                    name="protein"
                    type="number"
                    value={values.protein}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                  <FormLabel required>
                    <Badge>Fat</Badge>
                  </FormLabel>
                  <Input
                    name="fat"
                    type="number"
                    value={values.fat}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                </FormGroup>
              )}
              {isType(PlanSettings.TYPE_STATUS) && (
                <FormGroup>
                  <Switch
                    single
                    name="active"
                    label="Plan visible"
                    checked={values.active}
                    value={true}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                </FormGroup>
              )}
              {error && (
                <Alert type="danger" multiline>{error}</Alert>
              )}
              <FormActions>
                <FlatButton type="button" onClick={handleClose}>
                  Cancel
                </FlatButton>
                <Button
                  modifier={approveWaiting ? 'danger' : 'primary'}
                  disabled={isSubmitting}
                  onClick={handleSubmit}
                  type="button"
                >
                  {isSubmitting ? 'Saving...' : approveWaiting ? 'Yes, approve it!' : 'Save'}
                </Button>
              </FormActions>
            </FormContainer>
          </form>
        )}
      </Formik>
    );
  }, [plan, error, approveWaiting]);

  return (
    <Popup
      ref={popupRef}
      canClose={canClose}
      onClose={onClose}
      renderTrigger={renderTrigger}
      renderBody={renderBody}
      position={position}
    />
  )
});

PlanSettings.TYPE_NAME = 'name';
PlanSettings.TYPE_KCALS = 'kcals';
PlanSettings.TYPE_STATUS = 'status';
PlanSettings.TYPE_MACROS = 'macros';
PlanSettings.TYPE_DURATION = 'duration';

PlanSettings.defaultProps = {
  title: 'Edit',
};

export default PlanSettings;
