import React, {Suspense, useCallback, useEffect, useMemo, useState, useRef } from "react";
import { Formik } from "formik";
import MealPlansContainer from "../../containers/MealPlans";
import SettingTypes from "../../constants/SettingTypes";
import { ModalBody, ModalFooter } from "../../components/Modal";
import { Button, FlatButton, FlexFill } from "../../components/UI";
import { Switch } from "../../components/Form";
import DescriptionForm from "./DescriptionForm"
// const DescriptionForm = React.lazy(() => import("./DescriptionForm"));

const PRIMARY_ACTIONS = {
  [SettingTypes.SETTINGS_DESCRIPTION]: {
    title: "Save",
    type: "primary",
  },
  [SettingTypes.DELETE]: {
    title: "Yes, delete it!",
    type: "danger",
  },
  [SettingTypes.CLONE]: {
    title: "Yes, clone it!",
    type: "primary",
  }
};

const Settings = React.memo(({ plan, settingsType, onClose }) => {
  const action = PRIMARY_ACTIONS[settingsType];

  const { updateMealPlan, deleteMealPlan, cloneMealPlan, planById, submitDefaultMessage } = MealPlansContainer.useContainer();

  const [initialValues, setInitialValues] = useState({});

  const currentPlan = useMemo(() => planById(plan.id), [plan]);

  const onSubmit = useCallback(async (values, { setSubmitting }) => {
    switch (settingsType) {
      case SettingTypes.SETTINGS_DESCRIPTION:{
        const {
          templateId,
          templateName,
          freshTemplate,
          message
        } = values;

        if (templateId) {
          await submitDefaultMessage({
            id: !freshTemplate ? templateId : undefined,
            title: templateName,
            textarea: message,
          });
        } else if (freshTemplate) {
          await submitDefaultMessage({
            title: templateName,
            textarea: message,
          });
        }

        await updateMealPlan(plan.id, values, true);
        break;
      }
      case SettingTypes.DELETE: {
        await deleteMealPlan(plan.id);
        break;
      }
      case SettingTypes.CLONE: {
        const planName = "Copy of " + plan.name;
        await cloneMealPlan(plan.id, planName);
        break;
      }
    }

    setSubmitting(false);
    onClose();
  }, [plan.id, settingsType]);

  const isSubmitDisabled = useCallback(({ dirty, values, isSubmitting }) => {
    if (settingsType === SettingTypes.DELETE || settingsType === SettingTypes.CLONE) {
      return isSubmitting;
    }

    if (values.templateId && (values.templateName || '').trim().length === 0) {
      return true;
    }

    return !dirty || isSubmitting;
  }, [settingsType]);

  useEffect(() => {
    if (settingsType === SettingTypes.SETTINGS_DESCRIPTION) {
      setInitialValues({ message: currentPlan.explaination})
    }
  }, [settingsType]);

  return (
    <Formik
      initialValues={initialValues}
      onSubmit={onSubmit}
    >
      {({
        dirty,
        values,
        errors,
        touched,
        setFieldValue,
        setFieldTouched,
        handleChange,
        handleBlur,
        handleSubmit,
        isSubmitting,
      }) => (
        <form onSubmit={handleSubmit}>
          <ModalBody>
            {
              settingsType === SettingTypes.SETTINGS_DESCRIPTION && (
              <Suspense fallback={<div>Loading...</div>}>
                <DescriptionForm
                  plan={currentPlan}
                  values={values}
                  touched={touched}
                  errors={errors}
                  handleBlur={handleBlur}
                  setFieldValue={setFieldValue}
                  setFieldTouched={setFieldTouched}
                />
              </Suspense>
            )}
            {settingsType === SettingTypes.DELETE && (
              <h5>Are you sure you wish to delete this meal plan?</h5>
            )}
            {settingsType === SettingTypes.CLONE && (
              <h5>Are you sure you wish to clone this meal plan?</h5>
            )}
          </ModalBody>
          {action && (
            <ModalFooter>
              {settingsType === SettingTypes.SETTINGS_DESCRIPTION && (
                <FlexFill>
                  <Switch
                    single
                    nm
                    name="freshTemplate"
                    label="Save as template"
                    checked={!!values.freshTemplate}
                    value={true}
                    onChange={handleChange}
                    onBlur={handleBlur}
                  />
                </FlexFill>
              )}
              <FlatButton onClick={onClose} type="button">Cancel</FlatButton>
              <Button modifier={action.type} disabled={isSubmitDisabled({ dirty, values, isSubmitting })} type="submit">
                {action.title}
              </Button>
            </ModalFooter>
          )}
        </form>
      )}
    </Formik>
  );
});

export default Settings;
