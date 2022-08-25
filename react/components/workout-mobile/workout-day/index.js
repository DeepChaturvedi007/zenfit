/*jshint esversion: 6 */
import './index.scss';

import React from 'react';
import {Provider} from 'react-redux';
import {createStore, applyMiddleware} from 'redux';
import ReactDOM from 'react-dom';
import thunk from 'redux-thunk';
import reducers from './reducers';
import WorkoutsContainer from './components/workouts-container';

const WListElement = document.getElementById('workout-day-container');
const store = createStore(
    reducers, {}, applyMiddleware(thunk)
);

ReactDOM.render(
    <Provider store={store}>
        <WorkoutsContainer/>
    </Provider>,
    WListElement
);
