import React, {memo, useMemo, useCallback, useState} from 'react';
import styled from 'styled-components';
import {Formik} from "formik";
import {Alert, Button} from "../../components/UI";
import {
    Form as BaseForm,
    FormGroup as BaseFormGroup,
    FormLabel as BaseFormLabel,
} from '../../components/Form';
import {ModalBody} from "../../components/Modal";

const Form = styled(BaseForm)``;
const FormLabel = styled(BaseFormLabel)``;
const FormGroup = styled(BaseFormGroup)`
  display: flex;
  flex-direction: column;
`;

const FormActions = styled.div`
  display: flex;
  width: 100%;
  justify-content: flex-end;
  >button {
    margin-left: 5px;
    margin-right: 5px;
    &:first-of-type {
      margin-left: 0;
    }
    &:last-of-type {
      margin-right: 0;
    }
  }
`;

const initialValues =  {
    type: 0,
    name: "Extra"
};

const AddMealForm = (props) => {
    const {
        onSubmit,
        onCancel,
        mealTypesOptions,
    } = props;

    const [error, setError] = useState(null);

    const handleSubmit = useCallback(async (values, actions) => {
        setError(null);
        try {
            await onSubmit(values);
        } catch (e) {
            setError(e.message);
        } finally {
            actions.setSubmitting(false);
        }
    }, []);

    return (
        <Formik initialValues={initialValues} onSubmit={handleSubmit}>
            {
                ({
                     values,
                     errors,
                     touched,
                     handleChange,
                     handleBlur,
                     handleSubmit,
                     isSubmitting
                }) => (
                    <Form onSubmit={handleSubmit}>
                        {/*<FormGroup>*/}
                        {/*    <FormLabel required>*/}
                        {/*        Select meal type*/}
                        {/*    </FormLabel>*/}
                        {/*    <select*/}
                        {/*        name={'type'}*/}
                        {/*        value={values.type}*/}
                        {/*        onChange={handleChange}*/}
                        {/*        onBlur={handleBlur}*/}
                        {/*    >*/}
                        {/*        {*/}
                        {/*            mealTypesOptions.map(item => (*/}
                        {/*                <option value={item.value}>{item.label}</option>*/}
                        {/*            ))*/}
                        {/*        }*/}
                        {/*    </select>*/}
                        {/*</FormGroup>*/}
                        <p>
                            This will add an extra meal to this meal plan, and will update the ingredients in the existing meals.
                        </p>
                        {error && (
                            <Alert type="danger" multiline>{error}</Alert>
                        )}
                        <FormActions>
                            <Button disabled={isSubmitting} type="button" onClick={onCancel}>
                                Cancel
                            </Button>
                            <Button disabled={isSubmitting} modifier={'primary'} type={'submit'}>
                                {isSubmitting ? 'Adding...' : 'Add'}
                            </Button>
                        </FormActions>
                    </Form>
                )
            }
        </Formik>
    );
};

export default memo(AddMealForm);