const SUCCESS = 'SUCCESS';
const ERROR = 'ERROR';
const REQUEST = 'REQUEST';

export const request = (type) => `${type}_${REQUEST}`
export const success = (type) => `${type}_${SUCCESS}`;
export const error = (type) => `${type}_${ERROR}`;

export const FETCH = 'PROGRESS_STATS_FETCH';
export const FETCH_EXERCISE = 'PROGRESS_STATS_EXERCISE_FETCH';
export const FETCH_EXERCISES = 'PROGRESS_STATS_EXERCISES_FETCH';
export const SET_MFP_INTEGRATION_INFO = 'PROGRESS_SET_MFP_INTEGRATION_INFO';
export const GET_WORKOUT = 'GET_WORKOUT';