import {applyMiddleware, createStore, combineReducers, compose} from 'redux';
import {merge} from 'lodash';

import clients from './clients/reducer';
import workouts from './workouts/reducer';
import progress from './progress/reducer';
import thunk from "redux-thunk";
import {INITIAL_STATE as CLIENTS_INITIAL_STATE} from "./clients/reducer";
import {INITIAL_STATE as WORKOUTS_INITIAL_STATE} from "./workouts/reducer";
import {INITIAL_STATE as PROGRESS_INITIAL_STATE} from "./progress/reducer";

const rootReducer = combineReducers({
    clients,
    workouts,
    progress
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const clientState = {clients: CLIENTS_INITIAL_STATE};
const workoutState = {workouts: WORKOUTS_INITIAL_STATE};
const progressState = {progress: PROGRESS_INITIAL_STATE};

export default (initialState = {}) => {
    const preloadedState = merge(clientState, workoutState, progressState, initialState);
    return createStore(rootReducer, preloadedState, enhancer);
}
