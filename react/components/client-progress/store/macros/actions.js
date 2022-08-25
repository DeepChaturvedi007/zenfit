import {
  error,
  success,
  SET_CLIENT_ID,
  FETCH_DATA
} from './types';
import queryString from "query-string";
import moment from 'moment';

const getMacrosEndpoint = (obj) => {
  const query = queryString.stringify(obj);
  return `/react-api/v2/macros?${query}`
};

export const fetchData = (params) => async (dispatch, getState) => {
    const { macros: { clientId, data }} = getState();
    const {
      from,
      to
    } = params;

    const fromInstance = moment(from);
    const toInstance = moment(to);
    const pattern = {};
    while (fromInstance.unix() <= toInstance.unix()) {
      pattern[fromInstance.format('YYYY-MM-DD')] = {
        date: fromInstance.format('YYYY-MM-DD'),
        carbs: undefined,
        fat: undefined,
        id: undefined,
        kcal: undefined,
        protein: undefined
      };
      fromInstance.add(1, 'day');
    }

    // Check if the data are currently loaded
    let shouldLoad = false;
    Object.keys(pattern)
      .forEach(day => {
        shouldLoad = !Object.keys(data).includes(day) || shouldLoad;
      })

    if(!shouldLoad) return;

    dispatch({type: FETCH_DATA });
    const q = {
      client: clientId,
      limit: Object.keys(pattern).length,
      offset: 0,
      ...params,
    };
    try {
      const data = await fetch(getMacrosEndpoint(q), { credentials: 'include' })
        .then(response => response.json())
        .then(result => {
          return result.macros;
        });
      if(!Array.isArray(data)) {
        throw new Error('The data should be an instance of array')
      }

      const populated = {};
      data.forEach(item => {
        populated[item.date] = item
      })
      dispatch({ type: success(FETCH_DATA), payload: { data: { ...pattern, ...populated } } });
      return data;
    } catch (e) {
      dispatch({ type: error(FETCH_DATA), payload: { error: e.message } });
      return null;
    }
};

export const setClientId = (id) => ({
  type: SET_CLIENT_ID,
  payload: { id }
});