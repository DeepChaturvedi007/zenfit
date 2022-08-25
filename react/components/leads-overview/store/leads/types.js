const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const LEADS_FETCH = createRequestTypes('LEADS_FETCH');
export const LEADS_FETCH_COUNT = createRequestTypes('LEADS_FETCH_COUNT');
export const LEAD_TAGS_FETCH = createRequestTypes('LEAD_TAGS_FETCH');
export const LEADS_MORE_FETCH = createRequestTypes('LEADS_MORE_FETCH');
export const NEW_READ_CREATE = createRequestTypes('NEW_READ_CREATE');
export const LEAD_UPDATE = createRequestTypes('LEAD_UPDATE');
export const LEAD_DELETE = createRequestTypes('LEAD_DELETE');
export const LOAD_MORE = 'LOAD_MORE';
export const SEARCH_QUERY = 'SEARCH_QUERY';
export const SEARCH_BY_TAG = 'SEARCH_BY_TAG';
export const CHANGE_FILTER = 'CHANGE_FILTER';
