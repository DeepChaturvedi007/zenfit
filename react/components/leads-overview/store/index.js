import {applyMiddleware, createStore, combineReducers, compose} from 'redux';
import {merge} from 'lodash';

import leads from './leads/reducer';
import thunk from "redux-thunk";
import {INITIAL_STATE as LEADS_INITIAL_STATE} from "./leads/reducer";

const rootReducer = combineReducers({
    leads,
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const leadState = {leads: LEADS_INITIAL_STATE};

export default (initialState = {}) => {
    const preloadedState = merge(leadState, initialState);

    return createStore(rootReducer, preloadedState, enhancer);
}
