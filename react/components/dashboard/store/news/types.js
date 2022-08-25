const SUCCESS = 'SUCCESS';
const ERROR = 'ERROR';

export const success = (type) => `${type}_${SUCCESS}`;
export const error = (type) => `${type}_${ERROR}`;

export const FETCH              = 'NEWS_FETCH';