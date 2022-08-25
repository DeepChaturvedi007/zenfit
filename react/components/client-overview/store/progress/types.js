const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const GET_CLIENT_PROGRESS = createRequestTypes('GET_CLIENT_PROGRESS');
export const UPDATE_CHECKIN_INFO = 'UPDATE_CHECKIN_INFO';
