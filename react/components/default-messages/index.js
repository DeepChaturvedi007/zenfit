import './styles.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware, compose} from 'redux';
import thunk from 'redux-thunk';

import reducers from './reducers';
import DefaultMessages from "./DefaultMessages";

const RootElement = document.getElementById('default-messages-container');
const propsStr = RootElement.getAttribute('data-props');

let initialProps = {};
try {
    initialProps = JSON.parse(propsStr) || {};
} catch (e) {
    console.debug('Props is empty')
}

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const store = createStore(reducers, initialProps, enhancer);

const App = () => (
    <Provider store={store}>
        <DefaultMessages />
    </Provider>
);

ReactDOM.render(<App/>, RootElement);
