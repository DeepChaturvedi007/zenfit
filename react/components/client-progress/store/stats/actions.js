import {
  SET_MFP_INTEGRATION_INFO,
  request,
  success,
  error,
  FETCH,
  FETCH_EXERCISE,
  FETCH_EXERCISES,
  GET_WORKOUT
} from './types';
import queryString from 'query-string'
import { SET_CLIENT_ID } from '../macros/types';
import axios from 'axios';

const getWorkoutEndpoint = (obj) => {
  const query = queryString.stringify(obj);
  return `/react-api/v2/workout/stats?${query}`
};

const getMacrosEndpoint = clientId => `/react-api/v2/macros?client=${clientId}`;
const getMealsEndpoint = clientId => `/react-api/v2/meals?client=${clientId}`;

export const getStats = (clientId, from, to) => async (dispatch) => {
    dispatch({type: FETCH});
    try {
      const data = await Promise.all([
        fetch(getWorkoutEndpoint({client: clientId, from, to}), { credentials: 'include' }),
        fetch(getMacrosEndpoint(clientId), { credentials: 'include' }),
        fetch(getMealsEndpoint(clientId), { credentials: 'include' }),
      ])
        .then(responses => Promise.all(responses.map(response => response.json())))
        .then(([workouts, macros, meals]) => {
          return ({...workouts, ...macros, meals})
        });
      dispatch({ type: success(FETCH), payload: { stats: data } });
      return data;
    } catch (e) {
      dispatch({ type: error(FETCH) });
      return null;
    }
};

export const getExercisesStats = ({ ...params }) => async (dispatch) => {
  const { clientId, filters: { limit, offset }, type } = params;
  dispatch({ type: FETCH_EXERCISES })
  const q = {
    client: clientId,
    limit,
    offset,
    type
  }

  try {
    const data = await fetch(getWorkoutEndpoint(q), { credentials: 'include' })
      .then(response => response.json())
      .then(result => {
        return result;
      });
    dispatch({ type: success(FETCH_EXERCISES), payload: { stats: data } })
    return data
  }
  catch (e) {
    dispatch({ type: error(FETCH_EXERCISES), payload: { error: e.message } });
    return null
  }
}

export const getExerciseStats = ({ ...params }) => async (dispatch) => {
  const { clientId, exerciseId, type } = params;
  dispatch({ type: FETCH_EXERCISE })
  const q = {
    client: clientId,
    exerciseId,
    type
  }

  try {
    const data = await fetch(getWorkoutEndpoint(q), { credentials: 'include' })
    .then(response => response.json())
    .then(result => {
      return result;
    });
    dispatch({ type: success(FETCH_EXERCISE), payload: { stats: data } })
    return data
  }
  catch (e) {
    dispatch({ type: error(FETCH_EXERCISE), payload: { error: e.message } });
    return null
  }
}

export const setMfpIntegrationInfo = (link) => async (dispatch) => {
  dispatch({type: SET_MFP_INTEGRATION_INFO, payload: { link }});
};

export const setClientId = (id) => ({
  type: SET_CLIENT_ID,

  payload: { id }
});

export const getAllExercise = () => {
  return axios.get('/react-api/v2/workout/stats').then(res => {
    return res.data;
  })
  .catch((err) => {
    console.log(err);
    return err
  })
}

export const getWorkoutWeek = (from, to, clientId, exerciseId) => (dispatch) => {
  dispatch({
    type: request(GET_WORKOUT)
  })
  const query = {client: clientId, exerciseId, from, to}
  axios.get(getWorkoutEndpoint(query)).then(res => {
    dispatch({
      type: success(GET_WORKOUT),
      payload: res.data
    })
  })
  .catch((err) => {
    console.log(err);
    return err
  })
}
