import './styles.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware, compose} from 'redux';
import thunk from 'redux-thunk';
import { INITIAL_STATE as CHAT_INITIAL_STATE } from './reducers/chat-reducer';

import reducers from './reducers';
import ChatWidgetContainer from './containers/chat';
import {INIT_CHAT_WIDGET, TOGGLE_CHAT_WIDGET_OPEN} from "./constants";

const ChatWidgetElement = document.getElementById('chat-widget-container');
const userId = ChatWidgetElement.getAttribute('data-user-id');
const clientId = ChatWidgetElement.getAttribute('data-client-id');
const clientName = ChatWidgetElement.getAttribute('data-client-name');
const clientPhoto = ChatWidgetElement.getAttribute('data-client-photo');
const unreadMessagesCount = parseInt(ChatWidgetElement.getAttribute('data-unread-messages-count')) || 0;

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
  applyMiddleware(thunk)
);

const chatState = {...CHAT_INITIAL_STATE, unreadMessagesCount};

const initChatWidget = Boolean(clientId);
let globalInitProps = {};
if (initChatWidget) {
    globalInitProps = {initChatWidget, userId, clientId, clientName, clientPhoto};
}

const store = createStore(
  reducers,
  {global: globalInitProps, chat: chatState},
  enhancer
);

const App = () => (
  <Provider store={store}>
    <ChatWidgetContainer />
  </Provider>
);

window.openChatWidget = (userId, clientId, clientName, clientPhoto, locale, messageType, conversationId) => {
    store.dispatch({type: INIT_CHAT_WIDGET, payload: {
        userId,
        clientId,
        clientName,
        clientPhoto,
        locale,
        messageType,
        conversationId
    }});
    store.dispatch({type: TOGGLE_CHAT_WIDGET_OPEN, payload: false});
    store.dispatch({type: TOGGLE_CHAT_WIDGET_OPEN, payload: true});
};

ReactDOM.render(<App/>, ChatWidgetElement);
