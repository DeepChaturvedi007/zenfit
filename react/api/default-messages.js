export const GET_DEFAULT_MESSAGES = (type, clientId) => {
    return `/api/trainer/get-default-messages/${type}/${clientId}`;
};

export const GET_DEFAULT_MESSAGES_LOCAL = (type, clientId, local) => {
    return `/api/trainer/get-default-messages/${type}/${clientId}/${local}`;
};

export const GET_DEFAULT_MESSAGES_LOCAL_QUEUE = (type, clientId, local, datakey) => {
    return `/api/trainer/get-default-messages/${type}/${clientId}/${local}/${datakey}`;
};

export const CREATE_DEFAULT_MESSAGE = () => {
    return '/api/trainer/set-default-message';
};

export const UPDATE_DEFAULT_MESSAGE = (id) => {
    return `/api/trainer/update-default-message/${id}`;
};

export const DELETE_DEFAULT_MESSAGE = (id) => {
    return `/api/trainer/delete-default-message/${id}`;
};
