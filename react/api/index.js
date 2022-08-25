export const GET_CLIENTS = (q = '', tags = '') => {
  return `/api/trainer/clients?q=${q}&tags=${tags}`;
};

export const GET_SIGNED_UPLOAD_URL = (extension, contentType) => {
  return `/chat/api/get-presigned-request?extension=${extension}&contentType=${contentType}`;
};

export const TOGGLE_PLAN_STATUS = () => {
  return `/plan/togglePlanStatus`;
};

export const MESSAGES_RECEIVE = clientId => {
  return `/chat/api/fetch-messages/${clientId}`;
};

export const MESSAGE_SEND = () => {
  return `/chat/api/send`;
};

export const CLIENT_STATUS_ACTION = () => {
  return `/api/client-status/take-action`;
}

export const MULTIPLE_MESSAGE_SEND = (userId, q='') => {
  return `/chat/api/multiple-send${userId}?q=${q}`;
};

export const MARK_MESSAGES_READ = (userId) => {
  return `/chat/api/mark-messages-read/${userId}`
};

export const CONVERSATIONS_RECEIVE = (userId, q, tags) => {
  return `/chat/api/fetch-conversations/${userId}?q=${q}&tags=${tags}`;
};

export const CONVERSATIONS_GET = (id, q) => {
  return `/chat/api/get-conversation/${id}?q=${q}`;
};

export const CONVERSATIONS_MARK_UNREAD = (id) => {
  return `/chat/api/mark-unread-conversation/${id}`;
};

export const CONVERSATIONS_MARK_DONE = (id) => {
  return `/chat/api/mark-done-conversation/${id}`;
};

export const CONVERSATION_DELETE = () => {
  return `/chat/api/delete-conversation`;
};

export const MESSAGE_DELETE = () => {
  return `/chat/api/delete-message`;
};

export const ADD_NEW_CLIENT = () => {
  return `/api/client/add`;
};

export const SET_CLIENT_SETTINGS = () => {
  return `/api/client/settings/set-client-settings`;
};

export const DELETE_CLIENT = clientId => {
  return `/api/client/deleteClient/${clientId}`;
};

export const SEND_EMAIL = () => {
  return `/api/trainer/send-email-to-client`;
}

export const SAVE_TEMPLATE = () => {
  return `/api/trainer/set-default-message`;
}
