import { applyMiddleware, createStore, combineReducers, compose } from 'redux';
import { merge } from 'lodash';
import signup from './signup/reducer';
import thunk from "redux-thunk";
import { INITIAL_STATE as CLIENT_SIGNUP_FLOW_INITIAL_STATE } from "./signup/reducer";

const rootReducer = combineReducers({
    signup,
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const componentState = { signup: CLIENT_SIGNUP_FLOW_INITIAL_STATE };

export default (initialState = {}) => {
    const preloadedState = merge(componentState, initialState);
    return createStore(rootReducer, preloadedState, enhancer);
}
