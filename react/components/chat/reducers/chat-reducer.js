import {
  MESSAGES_FETCH,
  MESSAGES_SHOW,
  MESSAGES_UPDATE,
  CONVERSATIONS_FETCH,
  CONVERSATIONS_SHOW_MODAL,
  CONVERSATIONS_ORDER,
  CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS,
  CONVERSATION_SELECTED_UPDATE,
  CONVERSATIONS_MARK_AS_SEEN,
  MOBILE_VIEW,
  SET_SCROLLED
} from '../constants';

const INITIAL_STATE = {
  conversations: [],
  selectedConversation: null,
  isShowNewMessageConversation: false,
  isShowSendMessageToAllClients: false,
  messages: [],
  startFrom: 0,
  hasMore: false,
  isShowMessages: false,
  selectedClient: null,
  isLoading: true,
  isMessageLoading: true,
  scrolled: false
};

export default function (state = INITIAL_STATE, {type, payload}) {
  switch (type) {
    case MESSAGES_FETCH.REQUEST:
      return {...state, isMessageLoading: true, messages: [], scrolled: false, selectedConversation: null};
    case MESSAGES_FETCH.SUCCESS:
      return {
        ...state,
        messages: [...payload.messages],
        hasMore: payload.hasMore,
        selectedConversation: payload.selectedConversation,
        selectedClient: payload.selectedClient,
        isMessageLoading: false
      };
    case MESSAGES_UPDATE:
      return {
        ...state,
        hasMore: payload.hasMore,
        messages: [...payload.messages]
      };
    case MESSAGES_SHOW:
      return {
        ...state,
        isShowMessages: payload.isShowMessages
      };
    case CONVERSATIONS_FETCH.REQUEST:
      return {...state, isLoading: true, messages: []};
    case CONVERSATIONS_FETCH.SUCCESS:
      return {
        ...state,
        conversations: [...payload.conversations],
        selectedConversation: payload.selectedConversation,
        selectedClient: payload.selectedClient,
        isLoading: false
      };
    case CONVERSATIONS_SHOW_MODAL:
      return {
        ...state,
        isShowNewMessageConversation: payload.isShowNewMessageConversation
      };
    case CONVERSATIONS_SEND_MESSAGE_TO_ALL_CLIENTS:
      return {
        ...state,
        isShowSendMessageToAllClients: payload.isShowSendMessageToAllClients
      };
    case CONVERSATIONS_ORDER:
      return {
        ...state,
        conversations: [...payload.conversations]
      };
    case CONVERSATION_SELECTED_UPDATE:
      return {
        ...state,
        selectedConversation: payload.selectedConversation
      };

    case CONVERSATIONS_MARK_AS_SEEN:
      const nextState = {
        ...state,
        conversations: state.conversations.map(conversation => {
          if (conversation.id === payload.id) {
            conversation.isNew = false;
          }
          return conversation;
        }),
      };

      if (payload.message) {
        nextState.messages = state.messages.map(message => {
          if (message.id === payload.message.id) {
            return {
              ...message,
              ...payload.message,
            };
          }
          return message;
        })
      }

      return nextState;
    case SET_SCROLLED:
      return {
        ...state,
        scrolled: payload.scrolled
      };
    case MOBILE_VIEW:
      return {
        ...state,
        isMobile: payload.isMobile,
        isShowMessages: payload.isShowMessages
      };
    default:
      return state;
  }
}
