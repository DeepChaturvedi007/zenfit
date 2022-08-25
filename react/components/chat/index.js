import './styles.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware} from 'redux';
import thunk from 'redux-thunk';

import reducers from './reducers';
import ChatContainer from './containers/chat';

const ChatElement = document.getElementById('chat-container');
const userId = ChatElement.getAttribute('data-user-id');
const assistant = ChatElement.getAttribute('data-assistant');

const store = createStore(reducers, {global: {userId, assistant}}, applyMiddleware(thunk));

const App = () => (
	<Provider store={store}>
    <ChatContainer/>
  </Provider>
);

ReactDOM.render(<App/>, ChatElement);
