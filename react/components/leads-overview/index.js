
import './styles.scss';
import React from 'react';

import createStore from './store';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import Main from './Main';

const Wrapper = document.getElementById('leads-overview');
const propsStr = Wrapper.getAttribute('data-props');

let initialProps = {};
try {
    initialProps = JSON.parse(propsStr) || {};
    initialProps.settings = JSON.parse(initialProps.settings) || {};
} catch (e) {
    console.debug('Props is empty')
}

const store = createStore({ leads: initialProps });

ReactDOM.render(
    <Provider store={store}>
        <Main />
    </Provider>,
    Wrapper
);
