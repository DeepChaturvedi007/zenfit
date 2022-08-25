const SUCCESS = 'SUCCESS';
const ERROR = 'ERROR';

export const success = (type) => `${type}_${SUCCESS}`;
export const error = (type) => `${type}_${ERROR}`;

export const SAVE_DATA            = 'SURVEY_SAVE_DATA';
export const SET_VALUE            = 'SURVEY_SET_VALUE';
export const FLUSH_MEAL           = 'SURVEY_FLUSH_MEAL';
export const FLUSH_WORKOUT        = 'SURVEY_FLUSH_WORKOUT';
export const SET_FIELD_ERROR        = 'SURVEY_SET_FIELD_ERROR';
export const UNSET_FIELD_ERROR        = 'SURVEY_UNSET_FIELD_ERROR';