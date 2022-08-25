import {
  success,
  error,
  SAVE_DATA,
  SET_VALUE,
  FLUSH_MEAL,
  FLUSH_WORKOUT,
  SET_FIELD_ERROR,
  UNSET_FIELD_ERROR
} from './types';
import { saveData } from '../../api/survey';

class ValidationError extends Error {
  constructor(message, data) {
    super(message);
    this.data = data;
  }
}

export const setValue = (key, value) => {
  return {
    type: SET_VALUE,
    payload: {
      key,
      value
    }
  }
};

export const unsetFieldError = (field) => {
  return {
    type: UNSET_FIELD_ERROR,
    payload: {
      field
    }
  }
};

export const setFieldError = (field, message) => {
  return {
    type: SET_FIELD_ERROR,
    payload: {
      field,
      message
    }
  }
};

export const flushMealData = () => ({type: FLUSH_MEAL});

export const flushWorkoutData = () => ({type: FLUSH_WORKOUT});

const validate = (data) => {
  const errors = [];
  if(!data.startWeight) {
    errors.push({
      field: 'startWeight',
      type: 'required',
    });
  }
  if(!data.goalWeight) {
    errors.push({
      field: 'goalWeight',
      type: 'required',
    });
  }
  if(!data.height) {
    errors.push({
      field: 'height',
      type: 'required',
    });
  }
  if(!data.measuringSystem) {
    errors.push({
      field: 'measuringSystem',
      type: 'required',
    });
  }
  if(!data.age) {
    errors.push({
      field: 'age',
      type: 'required',
    });
  }
  if(!data.name) {
    errors.push({
      field: 'name',
      type: 'required',
    });
  }
  if(!data.email) {
    errors.push({
      field: 'email',
      type: 'required',
    });
  }
  if(!data.gender) {
    errors.push({
      field: 'gender',
      type: 'required',
    });
  }
  if (errors.length) {
    throw new ValidationError('errors.invalid', errors);
  }
  return true;
};

const transformToRequestData = (data) => {
  const {
    bundle = undefined,
    name = '',
    age,
    email = '',
    phone,
    gender,
    primaryGoal,
    goalType,
    startWeight,
    goalWeight,
    height,
    measuringSystem,
    activity,
    dietStyle,
    budget,
    cookingTime,
    foodPreferences,
    numberOfMeals,
    excludeIngredients = [],
    workoutsPerWeek,
    experience,
    place,
    exercisePreferences,
    goalParts = [],
    injuries,
    other,
    photo
  } = data;

  const FRONT = 1;
  const SIDE = 2;
  const REAR = 3;

  const excludeIngredientsString = excludeIngredients
    .map(({name}) => (name || '').trim())
    .filter(value => !!value)
    .join(', ')
    .trim();

  const bodyPartsString = goalParts
    .map(({name}) => (name || '').trim())
    .filter(value => !!value)
    .join(', ')
    .trim();

  const injuriesString = injuries
    .map(({name}) => (name || '').trim())
    .filter(value => !!value)
    .join(', ')
    .trim();
  // Concat dietStyle, budget and Cooking time
  const dietStyleArr = [];
  if(!!dietStyle) {
    dietStyleArr.push(dietStyle.trim());
  }
  if(!!budget) {
    dietStyleArr.push(`Budget: ${budget}`.trim())
  }
  if(!!cookingTime) {
    dietStyleArr.push(`Cooking time: ${cookingTime}`.trim())
  }

  const exercisePreferenceArr = [];
  if (!!exercisePreferences) {
    exercisePreferenceArr.push(exercisePreferences.trim());
  }
  if(!!bodyPartsString) {
    exercisePreferenceArr.push(`Extra focus on: ${bodyPartsString}`.trim());
  }

  return {
    bundle,
    name: `${name}`.trim() || undefined,
    age: Number(age) || undefined,
    email: email ? email.trim() : undefined,
    phone: phone ? phone.trim() : undefined,
    gender: Number(gender) || undefined,
    primaryGoal: (primaryGoal || {}).value || undefined,
    goalType: Number(goalType) || undefined,
    startWeight: Number(startWeight) || undefined,
    goalWeight: Number(goalWeight) || undefined,
    height: Number(height) || undefined,
    activityLevel: (activity || {}).value || undefined,
    dietStyle: dietStyleArr.join(', ') || undefined,
    foodPreferences: foodPreferences || [],
    numberOfMeals: Number(numberOfMeals) || undefined,
    excludeIngredients: excludeIngredientsString || undefined,
    workoutsPerWeek: Number(workoutsPerWeek) || undefined,
    experience: experience || undefined,
    workoutLocation: place || undefined,
    exercisePreferences: exercisePreferenceArr.join(', ') || undefined,
    injuries: injuriesString || undefined,
    other: other || undefined,
    measuringSystem: Number(measuringSystem) || 1,
    [FRONT]: (photo || {}).front || undefined,
    [REAR]: (photo || {}).back || undefined,
    [SIDE]: (photo || {}).side || undefined,
    // permanent definition
    locale: window.locale || 'en',
    answeredQuestionnaire: 1
  };
};

export const submitData = (data) => async (dispatch, getState) => {
  //add bundle as quickfix
  let bundleId = getState().config.bundle;
  data.bundle = bundleId;
  //end quickfix

  const { config: { hash }} = getState();
  dispatch({type: SAVE_DATA});
  try {
    validate(data);
  } catch (e) {
    const err = {
      message: e.message,
      errors: e.data
    };
    dispatch({
      type: error(SAVE_DATA),
      payload: { error: err }
    });
    return { error: true, message: e.message };
  }
  const requestData = transformToRequestData(data);
  return saveData(requestData, hash)
    .then(data => {
      dispatch({
        type: success(SAVE_DATA),
      });
      return data;
    })
    .catch(err => {

      let message = '';
      if(err.response) {
        if(err.response.status === 422) {
          message = err.response.data.message
        } else {
          message = err.response.statusText
        }
      } else {
        message = err.message();
      }

      dispatch({
        type: error(SAVE_DATA),
        payload: {
          error: message
        }
      });
      return { error: true, message };
    })
};
