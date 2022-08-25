import axios from 'axios';
import {
    MESSAGES_FETCH,
    MESSAGES_SHOW,
    MESSAGES_UPDATE,
    MOBILE_VIEW,
    SET_SCROLLED,
    MESSAGES_READ, TOGGLE_CHAT_WIDGET_OPEN
} from '../constants';
import {
    MARK_MESSAGES_READ,
    MESSAGES_RECEIVE,
    CONVERSATIONS_MARK_UNREAD,
    CONVERSATIONS_MARK_DONE
} from "../../../api";

export const fetchMessages = (clientId, isViewed = false) => {
    return (dispatch, getState) => {
        dispatch({type: MESSAGES_FETCH.REQUEST});

        const state = getState();
        const obj = {
            startFrom: state.chat.startFrom,
            isViewed: isViewed,
        };
        return axios.post(MESSAGES_RECEIVE(clientId), obj).then(({data}) => {
            dispatch({
                type: MESSAGES_FETCH.SUCCESS,
                payload: {
                    messages: data.messages,
                    hasMore: data.hasMore,
                },
            });
        });
    }
};

export const clearMessages = () => {
    return {
        type: MESSAGES_FETCH.SUCCESS,
        payload: {
            messages: [],
            hasMore: false,
            startFrom: 0,
        }
    };
};

export const updateMessages = (messages, hasMore, startFrom, unreadMessagesCount = 0) => {
    return {type: MESSAGES_UPDATE, payload: {messages, hasMore, startFrom, unreadMessagesCount}};
};

export const markMessagesAsRead = (clientId) => {
    return (dispatch, getState) => {
        dispatch({type: MESSAGES_READ, payload: {unreadMessagesCount: 0}});
        return axios.post(MARK_MESSAGES_READ(clientId), {});
    }
};

export const toggleChatWidgetOpen = (isOpen = null) => {
    return {type: TOGGLE_CHAT_WIDGET_OPEN, payload: isOpen};
};

export const markUnreadConversation = id => {
    return (dispatch, getState) => {
        return axios.post(CONVERSATIONS_MARK_UNREAD(id), {}).then(res => {
            toastr.success('Success');
        });
    };
};

export const markConversationDone = id => {
    return (dispatch, getState) => {
        return axios.post(CONVERSATIONS_MARK_DONE(id), {}).then(res => {
            toastr.success('Success');
        });
    };
};
