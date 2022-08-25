const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const SET_SELECTED_CLIENT = 'SET_SELECTED_CLIENT';
export const CLIENTS_FETCH = createRequestTypes('CLIENTS_FETCH');
export const FILTER_COUNT_FETCH = createRequestTypes('FILTER_COUNT_FETCH');
export const CHANGE_FILTER = 'CHANGE_FILTER';
export const CHANGE_SEARCH_QUERY = 'CHANGE_SEARCH_QUERY';
export const CHANGE_ACTIVE_FILTER = 'CHANGE_ACTIVE_FILTER';
export const DELETE_CLIENT = createRequestTypes('DELETE_CLIENT');
export const ADD_NEW_CLIENT = createRequestTypes('ADD_NEW_CLIENT');
export const DEACTIVATE_CLIENT = createRequestTypes('DEACTIVATE_CLIENT');
export const ACTIVATE_CLIENT = createRequestTypes('ACTIVATE_CLIENT');
export const CHANGE_SORT = 'CHANGE_SORT';
export const IGNORE_CLIENT_STATUS = 'IGNORE_CLIENT_STATUS';
export const SELECTED_CLIENT_COUNT = 'SELECTED_CLIENT_COUNT';
export const SELECTED_ALL_CLIENTS = 'SELECTED_ALL_CLIENTS';
export const CLIENT_STATUS_UPDATE = 'CLIENT_STATUS_UPDATE';
export const DELETE_SELECTED_CLIENTS = createRequestTypes('DELETE_SELECTED_CLIENTS');
export const UPDATE_UNREAD_MESSAGE = 'UPDATE_UNREAD_MESSAGE';
export const CLIENT_TAG_FILTER = 'CLIENT_TAG_FILTER';
export const TOOLTIP_HANDLE = 'TOOLTIP_HANDLE';
export const TASK_ADD_MODAL_OPEN = 'TASK_ADD_MODAL_OPEN'
export const MEDIA_TEMPLATE_MODAL_OPEN = createRequestTypes('MEDIA_TEMPLATE_MODAL_OPEN');
export const MEAL_PLAN_MODAL_OPEN = 'MEAL_PLAN_MODAL_OPEN';
export const ADD_NEW_TASK = createRequestTypes('ADD_NEW_TASK');
export const RESOLVE_REMINDER = createRequestTypes('RESOLVE_REMINDER');
export const SUBSCRIPTION_MODAL_OPEN = 'SUBSCRIPTION_MODAL_OPEN';
export const UPDATE_CLIENT_PAYMENT = createRequestTypes('UPDATE_CLIENT_PAYMENT');
export const DEACTIVATE_SELECTED_CLIENT = createRequestTypes('DEACTIVATE_SELECTED_CLIENT');
export const ACTIVATE_SELECTED_CLIENT = createRequestTypes('ACTIVATE_SELECTED_CLIENT');
export const EXTEND_CLIENT_MODAL_OPEN = 'EXTEND_CLIENT_MODAL_OPEN';
export const EXTEND_CLIENT_DURATION = 'EXTEND_CLIENT_DURATION';
export const CLIENT_ACTIVE_STATUS_UPDATE = createRequestTypes('CLIENT_ACTIVE_STATUS_UPDATE');
export const CLIENT_UPDATE_DURATION = 'CLIENT_UPDATE_DURATION';
export const OPEN_SIDE_CONTENT = 'OPEN_SIDE_CONTENT';
export const SEND_EMAIL = createRequestTypes('SEND_EMAIL');
export const MULTI_SEND_MESSAGE = createRequestTypes('MULTI_SEND_MESSAGE');
export const SUBSCRIPTION_UPDATE = createRequestTypes('SUBSCRIPTION_UPDATE');
export const MESSAGE_COUNT_UPDATE = 'MESSAGE_COUNT_UPDATE';
export const UPDATE_CLIENT_INFO = 'UPDATE_CLIENT_INFO';
export const GET_CLIENT_DOCS = createRequestTypes('GET_CLIENT_DOCS');
export const GET_CLIENT_VIDEOS = createRequestTypes('GET_CLIENT_VIDEOS');
export const UNLOAD_MEAL_PLAN = 'UNLOAD_MEAL_PLAN';
export const CLIENT_WORKOUT_ADD_COUNT = 'CLIENT_WORKOUT_ADD_COUNT';
export const CLIENT_WORKOUT_SUBTRACT_COUNT = 'CLIENT_WORKOUT_SUBTRACT_COUNT';
export const GENERATE_MEAL_PLAN = createRequestTypes('GENERATE_MEAL_PLAN');
export const GET_CLIENT_PAYMENTS_LOG = createRequestTypes('GET_CLIENT_PAYMENTS_LOG');
export const UPDATE_CLIENT_PAYMENT_LOG = createRequestTypes('UPDATE_CLIENT_PAYMENT_LOG');
export const GET_CLIENT_IMAGES = createRequestTypes('GET_CLIENT_IMAGES');
export const ADD_CLIENT_VIDEO = createRequestTypes('GET_CLIENT_VIDEO');
export const DELETE_CLIENT_VIDEO = createRequestTypes('DELETE_CLIENT_VIDEO');
export const ADD_CLIENT_DOC = createRequestTypes('GET_CLIENT_DOC');
export const DELETE_CLIENT_DOC = createRequestTypes('DELETE_CLIENT_DOC');
export const ADD_CLIENT_DOC_LIBRARY = createRequestTypes('ADD_CLIENT_DOC_LIBRARY');
export const FETCH_MORE_CLIENT_IMAGES = createRequestTypes('FETCH_MORE_CLIENT_IMAGES');
export const UPDATE_CLIENT_DATA = 'UPDATE_CLIENT_DATA';
export const GET_CLIENT_KCALS = createRequestTypes('GET_CLIENT_KCALS');
