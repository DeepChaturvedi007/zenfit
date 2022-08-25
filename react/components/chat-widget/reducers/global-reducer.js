import {INIT_CHAT_WIDGET} from "../constants";

export const INITIAL_STATE = {
    initChatWidget: false,
    userId: null,
    clientId: null,
    clientName: null,
    clientPhoto: null,
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case INIT_CHAT_WIDGET:
            return {...state, ...payload, initChatWidget: true};

        default:
            return state;
    }
}
