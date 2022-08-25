const SUCCESS = 'SUCCESS';
const ERROR = 'ERROR';

export const success = (type) => `${type}_${SUCCESS}`;
export const error = (type) => `${type}_${ERROR}`;

export const SET_CLIENT_ID = 'PROGRESS_SET_CLIENT_ID';
export const FETCH_DATA = 'PROGRESS_FETCH_DATA';