const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

/*Rest*/
export const CREATE_USER = createRequestTypes('CREATE_USER');

/*Function*/
export const CHANGE_MESSAGE = "CHANGE_MESSAGE"
export const CHANGE_ERROR = "CHANGE_ERROR"
export const SET_VIEW = "SET_VI"
