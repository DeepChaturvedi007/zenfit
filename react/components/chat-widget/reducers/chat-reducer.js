import {
  MESSAGES_FETCH,
  MESSAGES_UPDATE,
  MESSAGES_READ, TOGGLE_CHAT_WIDGET_OPEN
} from '../constants';

export const INITIAL_STATE = {
  messages: [],
  hasMore: false,
  isLoading: true,
  startFrom: 0,
  isMessageLoading: true,
  scrolled: false,
  unreadMessagesCount: 0,
  isChatWidgetOpen: false,
};

export default function (state = INITIAL_STATE, {type, payload}) {
  switch (type) {
    case MESSAGES_FETCH.REQUEST:
      return {...state, isMessageLoading: true, scrolled: false};
    case MESSAGES_FETCH.SUCCESS:
      return {
        ...state,
        messages: [...payload.messages],
        startFrom: ((payload.startFrom || payload.startFrom === 0)
          ? payload.startFrom
          : state.startFrom + payload.messages.length),
        hasMore: payload.hasMore,
        isMessageLoading: false,
      };
    case MESSAGES_UPDATE:
      return {
        ...state,
        hasMore: payload.hasMore,
        messages: [...payload.messages],
        startFrom: payload.startFrom,
        unreadMessagesCount: payload.unreadMessagesCount,
      };
    case MESSAGES_READ:
      return {
        ...state,
        unreadMessagesCount: payload.unreadMessagesCount
      };

    case TOGGLE_CHAT_WIDGET_OPEN:
      return {
        ...state,
        isChatWidgetOpen: ((payload === null) ? !state.isChatWidgetOpen : payload),
      };
    default:
      return state;
  }
}
