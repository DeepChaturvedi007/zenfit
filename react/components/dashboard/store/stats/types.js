const SUCCESS = 'SUCCESS';
const ERROR = 'ERROR';

export const success = (type) => `${type}_${SUCCESS}`;
export const error = (type) => `${type}_${ERROR}`;

export const SETUP = 'STATS_SETUP';
export const GET_AFFILIATE_LINK = 'STATS_GET_AFFILIATE_LINK';