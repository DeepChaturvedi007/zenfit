import React, {useCallback, useRef, useState} from 'react';
import {Formik, Field, ErrorMessage as BaseErrorMessage} from "formik";
import * as Yup from "yup";
import swal from "sweetalert";
import _ from 'lodash';

const initialValues =  {
  name_en: '',
  name_da_DK: '',
  name_sv_SE: '',
  name_nb_NO: '',
  name_nl_NL: '',
  name_fi_FI: '',
  name_de_DE: '',
  carbohydrate: '',
  protein: '',
  fat: '',
  kcals: '',
  saturatedFat: '',
  monoFat: '',
  sugars: '',
  fiber: '',
  amount_name_en: '',
  amount_value_en: '',
  amount_name_da_DK: '',
  amount_value_da_DK: '',
  amount_name_sv_SE: '',
  amount_value_sv_SE: '',
  amount_name_nb_NO: '',
  amount_value_nb_NO: '',
  amount_name_nl_NL: '',
  amount_value_nl_NL: '',
  amount_name_fi_FI: '',
  amount_value_fi_FI: '',
  amount_name_de_DE: '',
  amount_value_de_DE: '',
};

const IngredientSchema = Yup.object().shape({
  name_en: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_da_DK: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_sv_SE: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_nb_NO: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_nl_NL: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_fi_FI: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  name_de_DE: Yup
  .string()
  .test(
    'oneOfRequired',
    'At least one of the titles must be entered',
    function() {
      return (this.parent.name_en || this.parent.name_da_DK || this.parent.name_sv_SE || this.parent.name_nb_NO || this.parent.name_nl_NL || this.parent.name_fi_FI || this.parent.name_de_DE)
    }
  ),
  carbohydrate: Yup
    .number()
    .min(0)
    .required('Field is required'),
  protein: Yup
    .number()
    .min(0)
    .required('Field is required'),
  fat: Yup
    .number()
    .min(0)
    .required('Field is required'),
  saturatedFat: Yup
    .number()
    .min(0),
  monoFat: Yup
    .number()
    .min(0),
  sugars: Yup
    .number()
    .min(0),
  fiber: Yup
    .number()
    .min(0),
  kcals: Yup
    .number()
    .min(0)
    .required('Field is required'),
  amount_name_en: Yup.string(),
  amount_value_en: Yup.number().positive(),
  amount_name_da_DK: Yup.string(),
  amount_value_da_DK: Yup.number().positive(),
  amount_name_sv_SE: Yup.string(),
  amount_value_sv_SE: Yup.number().positive(),
  amount_name_nb_NO: Yup.string(),
  amount_value_nb_NO: Yup.number().positive(),
  amount_name_nl_NL: Yup.string(),
  amount_value_nl_NL: Yup.number().positive(),
  amount_name_de_DE: Yup.string(),
  amount_value_de_DE: Yup.number().positive(),
});

const serializeValues = (values) => {
  const {
    id,
    name_en,
    name_da_DK,
    name_sv_SE,
    name_nb_NO,
    name_nl_NL,
    name_fi_FI,
    name_de_DE,
    carbohydrate,
    protein,
    fat,
    kcals,
    saturatedFat,
    monoFat,
    sugars,
    fiber,
    amount_name_en,
    amount_value_en,
    amount_name_da_DK,
    amount_value_da_DK,
    amount_name_sv_SE,
    amount_value_sv_SE,
    amount_name_nb_NO,
    amount_value_nb_NO,
    amount_name_nl_NL,
    amount_value_nl_NL,
    amount_name_fi_FI,
    amount_value_fi_FI,
    amount_name_de_DE,
    amount_value_de_DE
  } = values;

  const names = [];
  names.push({locale: 'en', name: name_en});
  names.push({locale: 'da_DK', name: name_da_DK});
  names.push({locale: 'sv_SE', name: name_sv_SE});
  names.push({locale: 'nb_NO', name: name_nb_NO});
  names.push({locale: 'nl_NL', name: name_nl_NL});
  names.push({locale: 'fi_FI', name: name_fi_FI});
  names.push({locale: 'de_DE', name: name_de_DE});

  const name = name_en ? name_en:
    (name_da_DK ? name_da_DK :
      (name_sv_SE ? name_sv_SE : (name_nb_NO ? name_nb_NO : (name_nl_NL ? name_nl_NL : (name_fi_FI ? name_fi_FI : name_de_DE)) ))
    );

  const amounts = [];
  if(amount_name_en && amount_value_en) {
    amounts.push({locale: 'en', name: amount_name_en, value: amount_value_en,});
  }
  if(amount_name_da_DK && amount_value_da_DK) {
    amounts.push({locale: 'da_DK', name: amount_name_da_DK, value: amount_value_da_DK,});
  }
  if(amount_name_sv_SE && amount_value_sv_SE) {
    amounts.push({locale: 'sv_SE', name: amount_name_sv_SE, value: amount_value_sv_SE,});
  }
  if(amount_name_nb_NO && amount_value_nb_NO) {
    amounts.push({locale: 'nb_NO', name: amount_name_nb_NO, value: amount_value_nb_NO,});
  }
  if(amount_name_nl_NL && amount_value_nl_NL) {
    amounts.push({locale: 'nl_NL', name: amount_name_nl_NL, value: amount_value_nl_NL,});
  }
  if(amount_name_fi_FI && amount_value_fi_FI) {
    amounts.push({locale: 'fi_FI', name: amount_name_fi_FI, value: amount_value_fi_FI,});
  }
  if(amount_name_de_DE && amount_value_de_DE) {
    amounts.push({locale: 'de_DE', name: amount_name_de_DE, value: amount_value_de_DE,});
  }

  return {
    id,
    name,
    carbohydrate,
    protein,
    fat,
    kcals,
    saturatedFat,
    monoFat,
    sugars,
    fiber,
    names,
    amounts
  };
}

const normalize = (item) => {
  const {
    amounts,
    addedSugars,
    alcohol,
    allowSplit,
    brand,
    carbohydrates,
    cholesterol,
    deleted,
    excelId,
    fat,
    fiber,
    id,
    kcal,
    kj,
    label,
    monoUnsaturatedFat,
    name,
    names,
    nameDanish,
    polyUnsaturatedFat,
    protein,
    saturatedFat
  } = item;

  const name_en = names.en ? names.en.name: '';
  const name_nb_NO = names.nb_NO ? names.nb_NO.name: '';
  const name_sv_SE = names.sv_SE ? names.sv_SE.name: '';
  const name_da_DK = names.da_DK ? names.da_DK.name: '';
  const name_nl_NL = names.nl_NL ? names.nl_NL.name: '';
  const name_fi_FI = names.fi_FI ? names.fi_FI.name: '';
  const name_de_DE = names.de_DE ? names.de_DE.name: '';

  const amount_name_en = amounts.en ? amounts.en.name: '';
  const amount_value_en = amounts.en ? amounts.en.weight: '';
  const amount_name_da_DK = amounts.da_DK ? amounts.da_DK.name: '';
  const amount_value_da_DK = amounts.da_DK ? amounts.da_DK.weight: '';
  const amount_name_sv_SE = amounts.sv_SE ? amounts.sv_SE.name: '';
  const amount_value_sv_SE = amounts.sv_SE ? amounts.sv_SE.weight: '';
  const amount_name_nb_NO = amounts.nb_NO ? amounts.nb_NO.name: '';
  const amount_value_nb_NO = amounts.nb_NO ? amounts.nb_NO.weight: '';
  const amount_name_nl_NL = amounts.nl_NL ? amounts.nl_NL.name: '';
  const amount_value_nl_NL = amounts.nl_NL ? amounts.nl_NL.weight: '';
  const amount_name_fi_FI = amounts.fi_FI ? amounts.fi_FI.name: '';
  const amount_value_fi_FI = amounts.fi_FI ? amounts.fi_FI.weight: '';
  const amount_name_de_DE = amounts.de_DE ? amounts.de_DE.name: '';
  const amount_value_de_DE = amounts.de_DE ? amounts.de_DE.weight: '';

  return {
    id,
    name_en,
    carbohydrate: carbohydrates,
    protein,
    fat,
    kcals: kcal,
    saturatedFat,
    monoFat: monoUnsaturatedFat,
    sugars: addedSugars,
    fiber,
    name_da_DK,
    name_sv_SE,
    name_nb_NO,
    name_nl_NL,
    name_fi_FI,
    name_de_DE,
    amount_name_en,
    amount_value_en,
    amount_name_da_DK,
    amount_value_da_DK,
    amount_name_sv_SE,
    amount_value_sv_SE,
    amount_name_nb_NO,
    amount_value_nb_NO,
    amount_name_nl_NL,
    amount_value_nl_NL,
    amount_name_fi_FI,
    amount_value_fi_FI,
    amount_name_de_DE,
    amount_value_de_DE
  }
};

const ErrorMessage = (props) =>
  <BaseErrorMessage
    render={msg => <span className={'text-danger'}>{msg}</span>}
    {...props}
  />

const AddMealForm = (props) => {
  const {onSubmit, ingredient} = props;
  const formValues = _.isEmpty(ingredient) ? initialValues : normalize(ingredient);
  const [error, setError] = useState(null);
  const formRef = useRef();

  const handleSubmit = useCallback(async (values, actions) => {
    setError(null);
    try {
      const data = serializeValues(values);
      const {
        id,
        name
      } = await onSubmit(data);

      const text = data.id ? `The ingredient (${name}) has been successfully updated.` : `New ingredient (${name}) has been successfully created.`;
      swal({
        title: 'Success!',
        text,
        buttons: {
          confirm: {
            text: 'Ok'
          }
        }
      })
        .then(() => {
          formRef.current.reset();
        })
    } catch (e) {
      setError(e.message);
    } finally {
      actions.setSubmitting(false);
    }
  }, []);

  return (
    <Formik
      initialValues={formValues}
      onSubmit={handleSubmit}
      validationSchema={IngredientSchema}
    >
      {
        ({
           values,
           errors,
           touched,
           handleChange,
           handleBlur,
           handleSubmit,
           isSubmitting,
           resetForm
         }) => (
          <form ref={formRef} onSubmit={handleSubmit} onReset={resetForm}>
            <div className="row">
              <div className="col-xs-12">
                <hr/>
                <h3>Required fields:</h3>
              </div>
            </div>
            <Field name={'id'} type="hidden"></Field>
            <Field name={'name_da_DK'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (da_DK) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_sv_SE'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (sv_SE) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_nb_NO'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (nb_NO) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_nl_NL'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (nl_NL) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_fi_FI'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (fi_FI) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_de_DE'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (de_DE) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <Field name={'name_en'}>
              {
                ({ field, meta }) => (
                  <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                    <label className={'control-label'}>Title (en) *</label>
                    <input className={'form-control'} {...field} />
                    {meta.touched && <ErrorMessage name={field.name} />}
                  </div>
                )
              }
            </Field>
            <div className="row">
              <div className="col-sm-3">
                <Field name={'carbohydrate'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Carbs *</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'protein'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Protein *</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'fat'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Fat *</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'kcals'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Kcals *</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <hr/>
                <h3>Optional fields:</h3>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-3">
                <Field name={'sugars'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Sugars</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'saturatedFat'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Saturated Fat</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'monoFat'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Mono fat</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-3">
                <Field name={'fiber'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <label className={'control-label'}>Fibers</label>
                        <input type="number" step="any" className={'form-control'} {...field} />
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (da_DK)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_da_DK'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_da_DK'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (sv_SE)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_sv_SE'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_sv_SE'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (nb_NO)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_nb_NO'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_nb_NO'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (nl_NL)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_nl_NL'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_nl_NL'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (fi_FI)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_fi_FI'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_fi_FI'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (de_DE)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_de_DE'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_de_DE'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            <div className="row">
              <div className="col-xs-12">
                <label className={'control-label'}>Amount (en)</label>
              </div>
            </div>
            <div className="row">
              <div className="col-sm-6">
                <Field name={'amount_name_en'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="text" className={'form-control'} {...field} placeholder={'Name'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
              <div className="col-sm-6">
                <Field name={'amount_value_en'}>
                  {
                    ({ field, meta }) => (
                      <div className={`form-group ${meta.touched && meta.error ? 'has-error' : ''}`}>
                        <input type="number" step="any" className={'form-control'} {...field} placeholder={'Value'}/>
                        {meta.touched && <ErrorMessage name={field.name} />}
                      </div>
                    )
                  }
                </Field>
              </div>
            </div>
            {error && <p className={'text-danger'}>{error}</p>}
            <button className={'btn btn-success'} type="submit">Submit</button>
          </form>
        )
      }
    </Formik>
  );
};

export default AddMealForm;
