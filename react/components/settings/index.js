import './styles.scss';

import React from 'react';
import createStore from './store';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import Main from './Main';

const Wrapper = document.getElementById('settings');
const propsStr = Wrapper.getAttribute('data-props');

let initialProps = {};
try {
    initialProps = JSON.parse(propsStr) || {};
    initialProps.settings = initialProps.settings ? JSON.parse(initialProps.settings) : {};
    localStorage.setItem('token', initialProps.token);
} catch (e) {
    console.log(e)
}

const store = createStore({ settings: initialProps });
ReactDOM.render(
    <Provider store={store}>
        <Main />
    </Provider>,
    Wrapper
);