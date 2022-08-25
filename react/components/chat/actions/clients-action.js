import {GET_CLIENTS} from '../../../api';
import {CLIENTS_FETCH} from '../constants';

export const searchClients = (params = {}) => {
  return dispatch => fetchClients(dispatch, params);
};

function fetchClients(dispatch, params = {}) {
  dispatch({
    type: CLIENTS_FETCH.REQUEST,
    payload: {...params},
  });

  const tags = Array.isArray(params.tags) ? params.tags : '';

  return fetch(GET_CLIENTS(params.q, tags), {
    credentials: 'include'
  }).then(res => res.json()).then(res => {
    const clients = Array.isArray(res.clients) ? res.clients : [];

    dispatch({
      type: CLIENTS_FETCH.SUCCESS,
      payload: {...params, clients},
    });
  });
}
