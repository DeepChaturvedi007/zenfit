const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const SALES_STATS_FETCH = createRequestTypes('SALES_STATS_FETCH');
export const CHANGE_PERIOD_FETCH = createRequestTypes('CHANGE_PERIOD_FETCH');

/*Client administration*/
export const ADMIN_FETCH_CLIENTS = createRequestTypes('ADMIN_FETCH_CLIENTS');
