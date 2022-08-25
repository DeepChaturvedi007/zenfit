import './styles.scss';

import React from 'react';
import createStore from './store';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import Main from './Main';

const Wrapper = document.getElementById('client-overview');
const propsStr = Wrapper.getAttribute('data-props');

let initialProps = {};
try {
    initialProps = JSON.parse(propsStr) || {};
    initialProps.settings = JSON.parse(initialProps.settings) || {};
    initialProps.clientsCount.active = parseInt(initialProps.clientsCount.active)
    initialProps.clientsCount.inactive = parseInt(initialProps.clientsCount.inactive)
    initialProps.tagsList = JSON.parse(initialProps.tagsList) || {};
    localStorage.setItem('token', initialProps.token);
} catch (e) {
    console.debug('Props is empty')
}

const store = createStore({clients: initialProps});

ReactDOM.render(
    <Provider store={store}>
        <Main />
    </Provider>,
    Wrapper
);