const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

/*REST*/
export const POST_CLIENT = createRequestTypes('POST_CLIENT')

/*Function*/
export const CHANGE_LANGUAGE = 'CHANGE_LANGUAGE';
export const CHANGE_STEP = 'CHANGE_STEP';
export const SAVE_STEP = 'SAVE_STEP'
export const MAP_CLIENT_FIELDS = 'MAP_CLIENT_FIELDS'

