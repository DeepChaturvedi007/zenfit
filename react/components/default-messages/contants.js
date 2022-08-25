const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const MESSAGES_CREATE = createRequestTypes('MESSAGES_CREATE');
export const MESSAGES_UPDATE = createRequestTypes('MESSAGES_UPDATE');
export const MESSAGES_DELETE = createRequestTypes('MESSAGES_DELETE');
export const POPULATE_FORM = 'POPULATE_FORM';
export const TOGGLE_MODAL_FORM = 'TOGGLE_MODAL_FORM';
export const ADD_FORM_ERROR = 'ADD_FORM_ERROR';
