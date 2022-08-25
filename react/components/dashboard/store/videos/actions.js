import {
  success,
  error,
  FETCH,
} from './types';

import { fetchData } from '../../api/videos';

export const fetchVideos = () => async (dispatch, getState) => {
  const { videos: { items }} = getState();
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
