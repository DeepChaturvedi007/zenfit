import {
  success,
  error,
  FETCH,
} from './types';

import { fetchData } from '../../api/news';

export const fetchNews = () => async (dispatch, getState) => {
  const { news: { items }} = getState();
  const limit = 10;
  const offset = items.length;
  const order = 'date';
  const sort = 'DESC';
  dispatch({type: FETCH});
  return fetchData({limit, offset, order, sort})
    .then(items => {
      dispatch({
        type: success(FETCH),
        payload: {
          items,
          hasMore: items.length >= limit
        }
      });
      return items;
    })
    .catch(err => {
      dispatch({
        type: error(FETCH),
        payload: {
          error: err.message
        }
      });
      return [];
    })
};

export const addNews = (items) => dispatch => {
  dispatch({
    type: success(FETCH),
    payload: { items }
  });
};