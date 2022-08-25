/*jshint esversion: 6 */
import './styles.css';

import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import createStore from './store'

import Main from './Main';

const Wrapper = document.getElementById('dashboard-page-controller');
const propsStr = Wrapper.getAttribute('data-props');
let initialProps = {};
try {
  initialProps = JSON.parse(propsStr) || {};
} catch (e) {
  console.debug('Props is empty')
}
const store = createStore();
ReactDOM.render(
    <Provider store={store}>
        <Main initial={initialProps} />
    </Provider>,
    Wrapper
);