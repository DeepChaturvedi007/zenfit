/*jshint esversion: 6 */
import React from 'react';
import reducers from './reducers';
import ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import Main from './Main';
import {createStore,applyMiddleware} from "redux";
import thunk from "redux-thunk";
import './main.css'

let initialProps = {};
const Wrapper = document.getElementById('admin-screen-controller');
const screen = Wrapper.getAttribute('data-screen');
const propsStr = Wrapper.getAttribute('data-props');
try {
  initialProps = JSON.parse(propsStr);
} catch (e) {
  console.debug('Props is empty')
}

const store = createStore(reducers, {}, applyMiddleware(thunk));

ReactDOM.render(
    <Provider store={store}>
      <Main initial={initialProps} screen={screen} />
    </Provider>,
    Wrapper
);
