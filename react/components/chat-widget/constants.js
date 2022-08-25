const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

// Chat
export const MESSAGES_FETCH = createRequestTypes('MESSAGES_FETCH');
export const MESSAGES_SHOW = 'MESSAGES_SHOW';
export const MESSAGES_UPDATE = 'MESSAGES_UPDATE';
export const MESSAGES_READ = 'MESSAGES_READ';
export const MOBILE_VIEW = 'MOBILE_VIEW';
export const SET_SCROLLED = 'SET_SCROLLED';
export const S3_BEFORE_AFTER_IMAGES = 'https://zenfit-images.s3.eu-central-1.amazonaws.com/before-after-images/client/photo/';
export const DEFAULT_IMAGE_URL = '/bundles/app/1456081788_user-01.png';
export const PLAY_BUTTON_URL = '/bundles/app/play.png';
export const WAVE_URL = '/bundles/app/wave.png';
export const PAUSE_BUTTON_URL = '/bundles/app/pause.png';
export const MESSAGE_PENDING = 'pending';
export const MESSAGE_FAILED = 'failed';
export const MESSAGE_READ = 'read';

export const INIT_CHAT_WIDGET = 'INIT_CHAT_WIDGET';
export const TOGGLE_CHAT_WIDGET_OPEN = 'TOGGLE_CHAT_WIDGET_OPEN';
export const DEFAULT_MESSAGE_TYPES = {
  9: {
    'title': 'Delete client?',
    'subtitle': 'You\'re saying goodbye to this client, would you like to delete them?',
  },
  13: {
    'title': 'Ready to activate client?',
    'subtitle': 'This will move the client to Active Clients and allow them to watch their plans in the app.'
  }
};
