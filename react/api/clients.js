export const GET_CLIENT_FILTER_COUNTS = () => {
    return '/api/clients/count';
};
export const FETCH_CLIENTS = () => {
    return '/api/clients';
};
export const FETCH_CLIENTS_DETAIL = (id) => {
    return `/api/clients/${id}`
}
export const FETCH_TAGS_LIST = () => {
    return `/api/trainer/get-tags-by-user`
}
export const ACTIVATE_CLIENT = (id) => {
    return `/api/client/activateClient/${id}`;
};
export const IGNORE_STATUS = () => {
    return `/api/client-status/ignore`;
}
export const DELETE_SELECTED_CLIENTS = () => {
    return `/api/clients/delete`;
}
export const DEACTIVATE_SELECTED_CLIENTS = () => {
    return `/api/clients/deactivate`;
};
export const ADD_NEW_TASK = () => {
    return `/api/client-reminder`;
}
export const RESOLVE_REMINDER = () => {
    return `/api/client-reminder/resolve`;
}
export const CLIENT_STATUS_UPDATE = () => {
    return `/api/client/status/update`;
}
export const CLIENT_DURATION_UPDATE = (client) => {
    return `/api/client/updateClientInfo/${client}`;
}
export const ADD_NEW_CLIENT = () => {
    return `/api/client/add`;
}
export const PAUSE_SUBSCRIPTION = () => {
    return `/client/connect/pause-subscription`;
}
export const UNSUBSCRIBE_CLIENT = () => {
    return `/client/connect/unsubscribe`;
}
export const REFUND_CLIENT = () => {
    return `/client/connect/refund-client`;
}
export const FETCH_VIDEOS = () => {
    return `/video/api`
}
export const FETCH_DOCUMENTS = () => {
    return `/docs/api`
}
export const FETCH_CLIENT_VIDEOS = (clientId) => {
    return `/video/api/${clientId}`
}
export const ADD_CLIENT_VIDEO = (clientId, videoId) => {
    return `/video/api/${clientId}/${videoId}`
}
export const REMOVE_CLIENT_VIDEO = (clientId, videoId) => {
    return `/video/api/${clientId}/${videoId}`
}
export const REMOVE_CLIENT_DOC = (clientId, videoId) => {
    return `/docs/api/${clientId}/${videoId}`
}
export const POST_CLIENT_DOC_LIBRARY = () => {
    return `/docs/api/upload`
}
export const FETCH_CLIENT_DOCS = (clientId) =>{
    return `/docs/api/${clientId}`
}
export const ADD_CLIENT_DOC = (clientId, docId) => {
    return `/docs/api/${clientId}/${docId}`
}
export const UPDATE_CLIENT_INFO = (clientId) => {
    return `/api/client/submitClientInfo/${clientId}`;
}
export const SUBMIT_CLIENT_INFO_REACT_API = () => {
    return `/react-api/v3/activation/submit`;
}
export const FETCH_CLIENT_PAYMENTS_LOG = () => {
    return `/api/stripe/payments-log`;
}
export const FETCH_CLIENT_IMAGES = (clientId) => {
    return `/progress/api/images/${clientId}`;
}
export const FETCH_CLIENT_PROGRESS = (clientId) => {
    return `/progress/api/client/${clientId}`;
}
export const GENERATE_MEAL_PLAN = (clientId) => {
    return `/api/v3/meal/plans/generate/${clientId}`;
}
export const GET_RECIPE_INGREDIENTS = () => {
    return `/api/recipe/get-ingredients`;
}
export const GET_CLIENT_KCALS_URL = (clientId) => {
    return `/api/meal/client/kcals/${clientId}`;
}
export const UPLOAD_CLIENT_IMAGES = (clientId) => {
    return `/api/client/uploadImg/${clientId}`
}