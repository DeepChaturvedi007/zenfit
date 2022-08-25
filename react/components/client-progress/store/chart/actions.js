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
    const { chart: { data }} = getState();
    const { macros: { clientId }} = getState();
    const {
      period: { from, to },
      limit,
      offset
    } = params;

    const query = {from, to, limit, offset};
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
    dispatch({type: FETCH_DATA });
    try {
      const data = await fetch(getMacrosEndpoint({client: clientId, ...query}), { credentials: 'include' })
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