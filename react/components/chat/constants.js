const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

// Clients
export const CLIENTS_FETCH = createRequestTypes('CLIENTS_FETCH');

// Chat
export const MESSAGES_FETCH = createRequestTypes('MESSAGES_FETCH');
export const MESSAGES_SHOW = 'MESSAGES_SHOW';
export const MESSAGES_UPDATE = 'MESSAGES_UPDATE';
export const CONVERSATIONS_FETCH = createRequestTypes('CONVERSATIONS_FETCH');
export const CONVERSATIONS_SHOW_MODAL = 'CONVERSATIONS_SHOW_MODAL';
export const CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS = 'CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS';
export const CONVERSATIONS_ORDER = 'CONVERSATIONS_ORDER';
export const CONVERSATIONS_MARK_AS_UNREAD = 'CONVERSATIONS_MARK_AS_UNREAD';
export const CONVERSATIONS_SET_EMPTY_SELECTED = 'CONVERSATIONS_SET_EMPTY_SELECTED';
export const CONVERSATION_SELECTED_UPDATE = 'CONVERSATION_SELECTED_UPDATE';
export const MOBILE_VIEW = 'MOBILE_VIEW';
export const SET_SCROLLED = 'SET_SCROLLED';
export const S3_BEFORE_AFTER_IMAGES = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/client/photo/';
export const DEFAULT_IMAGE_URL = '/bundles/app/1456081788_user-01.png';
export const PLAY_BUTTON_URL = '/bundles/app/play.png';
export const PAUSE_BUTTON_URL = '/bundles/app/pause.png';
export const WAVE_URL = '/bundles/app/wave.png';
export const MESSAGE_PENDING = 'pending';
export const MESSAGE_DELIVERED = 'delivered';
export const MESSAGE_FAILED = 'failed';
export const MESSAGE_READ = 'read';
