import { applyMiddleware, createStore, combineReducers, compose } from 'redux';
import { merge } from 'lodash';
import settings from './settings/reducer';
import thunk from "redux-thunk";
import { INITIAL_STATE as SETTINGS_INITIAL_STATE } from "./settings/reducer";

const rootReducer = combineReducers({
    settings,
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const settingsState = { settings: SETTINGS_INITIAL_STATE };

export default (initialState = {}) => {
    const preloadedState = merge(settingsState, initialState);

    return createStore(rootReducer, preloadedState, enhancer);
}
