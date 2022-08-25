import axios from 'axios';
import _ from 'lodash';
import { rescue } from '../utils';
import {
    MESSAGES_FETCH,
    MESSAGES_SHOW,
    MESSAGES_UPDATE,
    CONVERSATIONS_FETCH,
    CONVERSATIONS_SHOW_MODAL,
    CONVERSATIONS_ORDER,
    CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS,
    CONVERSATION_SELECTED_UPDATE,
    CONVERSATIONS_MARK_AS_UNREAD,
    MOBILE_VIEW,
    SET_SCROLLED
} from '../constants';
import {
    MESSAGES_RECEIVE,
    CONVERSATIONS_RECEIVE,
    CONVERSATIONS_GET,
    CONVERSATION_DELETE,
    CONVERSATIONS_MARK_UNREAD,
    CONVERSATIONS_MARK_DONE,
    MESSAGE_DELETE
} from "../../../api";

const debouncedFetchConversations = _.debounce(fetchConversations, 700);

export const fetchMessages = (conversationId, isViewed = false) => {
    return (dispatch, getState) => {
        getMessages(dispatch, getState, conversationId, isViewed);
    }
};

export const searchConversations = (q = '', tags= '', isShowMessages = false, isViewed = false) => {
    return (dispatch, getState) => {
        const userId = getState().global.userId;
        return debouncedFetchConversations(dispatch, getState, userId, q, tags, isShowMessages, isViewed);    };
};

export const tagFilterConversation = (q = '', tags= '', isShowMessages = false, isViewed = false) => {
    return (dispatch, getState) => {
        const userId = getState().global.userId;
        return debouncedFetchConversations(dispatch, getState, userId, q, tags, isShowMessages, isViewed);
    };
}

export const deleteConversation = () => {
    return (dispatch, getState) => {
        const selectedConversation = getState().chat.selectedConversation.id;
        const obj = {id: selectedConversation};
        return axios.post(CONVERSATION_DELETE(), obj).then(res => {
            const userId = getState().global.userId;
            debouncedFetchConversations(dispatch, getState, userId, '', false, false);
        });
    };
};

export const deleteMessage = id => {
    return (dispatch, getState) => {
        const obj = {id: id};
        return axios.post(MESSAGE_DELETE(), obj).then(res => {
            const userId = getState().global.userId;
            debouncedFetchConversations(dispatch, getState, userId, '', false, false);
        });
    };
};


export const showNewMessageConversation = status => {
    return {type: CONVERSATIONS_SHOW_MODAL, payload: {isShowNewMessageConversation: status}};
};

export const showSendMessageToAllClientsConversation = status => {
    return {type: CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS, payload: {isShowSendMessageToAllClients: status}};
};

export const getConversation = (id, q = '', isShowMessages = true, isViewed = true) => {
    return (dispatch, getState) => {
        return axios.get(CONVERSATIONS_GET(id, q)).then(res => {
            dispatch({type: CONVERSATIONS_SHOW_MODAL, payload: {isShowNewMessageConversation: false}});
            const { conversations, selectedConversation } = res.data;
            const selectedClientId = _.get(selectedConversation, 'clientId');

            const selectedClient = selectedClientId
              ? getState().clients.clients.filter(client => client.id === selectedClientId).shift()
              : null;

            dispatch({
                type: CONVERSATIONS_FETCH.SUCCESS,
                payload: {
                    conversations: conversations,
                    selectedClient: selectedClient,
                    selectedConversation: selectedConversation
                }
            });
            return selectedConversation;
        }).then(conversation => {
            updConversation(dispatch, getState, conversation, isShowMessages, isViewed);
        });
    };
};

export const showMessages = status => {
    return {type: MESSAGES_SHOW, payload: {isShowMessages: status}};
};

export const setScrolled = status => {
    return {type: SET_SCROLLED, payload: {scrolled: status}};
};

export const isMobileView = status => {
    return {type: MOBILE_VIEW, payload: {isMobile: status, isShowMessages: !status}};
};

export const updateConversation = (conversation, isShowMessages = true, isViewed = true) => {
    return (dispatch, getState) => {
        updConversation(dispatch, getState, conversation, isShowMessages, isViewed);
    };
};

export const markUnreadConversation = id => {
    return (dispatch) => {
        return axios.post(CONVERSATIONS_MARK_UNREAD(id)).then(res => {
            toastr.success('Success');
        });
    };
};

export const markConversationDone = id => {
    return (dispatch) => {
        return axios.post(CONVERSATIONS_MARK_DONE(id)).then(res => {
            toastr.success('Success');
        });
    };
};

export const reorderConversations = conversations => {
    return {type: CONVERSATIONS_ORDER, payload: {conversations: conversations}};
};

export const updateMessages = (messages, hasMore) => {
    return {type: MESSAGES_UPDATE, payload: {messages: messages, hasMore}};
};

export const updateSelectedConversation = conversation => {
    return {type: CONVERSATION_SELECTED_UPDATE, payload: {selectedConversation: conversation}};
};

function getMessages(dispatch, getState, conversationId, isViewed) {
    dispatch({type: MESSAGES_FETCH.REQUEST});

    const state = getState();
    const selectedConversation = state.chat.conversations.filter(conversation => conversation.id === conversationId).shift();

    if (selectedConversation) {
        const clients = state.clients.clients;
        const clientId = selectedConversation.clientId;

        const selectedClient = {
          id: clientId,
          name: selectedConversation.client
        };

        const obj = {
            startFrom: state.chat.startFrom,
            isViewed: isViewed,
        };

        return axios.post(MESSAGES_RECEIVE(clientId), obj).then(({ data }) => {
            dispatch({
                type: MESSAGES_FETCH.SUCCESS,
                payload: {
                    messages: data.messages,
                    hasMore: data.hasMore,
                    selectedConversation,
                    selectedClient,
                },
            });
        });
    } else {
        dispatch(dispatchEmptyMessages());
    }
}

function fetchConversations(dispatch, getState, userId, q, tags, isShowMessages, isViewed) {
    dispatch({type: CONVERSATIONS_FETCH.REQUEST});

    return axios.get(CONVERSATIONS_RECEIVE(userId, q, tags)).then(({ data }) => {
        const searchParams = new URLSearchParams(window.location.search.slice(1));
        let readConversations = [];

        if (searchParams.has('client')) {
            const clientId = parseInt(searchParams.get('client'), 10);
            readConversations = data.conversations.filter(conversation => conversation.clientId === clientId);
        }

        if (!readConversations.length) {
            readConversations = data.conversations;

            if (readConversations.length > 1) {
                readConversations =  data.conversations.filter(conversation => !conversation.isNew);
            }
        }

        const selectedConversation = readConversations[0] || null;
        const selectedClient = selectedConversation
            ? getState().clients.clients.filter(client => client.id === selectedConversation.clientId).shift()
            : null;

        dispatch({
            type: CONVERSATIONS_FETCH.SUCCESS,
            payload: {
                conversations: data.conversations,
                selectedClient: selectedClient,
                selectedConversation: selectedConversation,
            },
        });

        if (selectedConversation) {
            updConversation(dispatch, getState, selectedConversation, isShowMessages, isViewed);
        } else {
            dispatch(dispatchEmptyMessages());
        }
    });
}

function dispatchEmptyMessages() {
    return {
        type: MESSAGES_FETCH.SUCCESS,
        payload: {
            messages: [],
            hasMore: false,
            selectedClient: null
        }
    };
}

function updConversation(dispatch, getState, conversation, isShowMessages, isViewed) {
    const {conversations: oldConversations, isMobile} = getState().chat;
    if (+conversation.isNew && (!isMobile || (isShowMessages && isMobile))) {
        const conversations = [...oldConversations];
        const index = _.findIndex(conversations, conversation);
        conversations[index].isNew = 0;
        dispatch(reorderConversations(conversations));
    }
    getMessages(dispatch, getState, conversation.id, isViewed);
    if (isShowMessages && isMobile) dispatch(showMessages(true));
}
