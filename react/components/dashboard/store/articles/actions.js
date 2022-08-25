import {
  success,
  error,
  FETCH,
} from './types';

import { fetchData } from '../../api/articles';

export const fetchArticles = () => async (dispatch, getState) => {
  const { articles: { items }} = getState();
  dispatch({type: FETCH});
  return fetchData({offset: items.length + 5})
    .then(items => {
      dispatch({
        type: success(FETCH),
        payload: { items }
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