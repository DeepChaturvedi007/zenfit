import { applyMiddleware, createStore, combineReducers, compose } from 'redux';
import { merge } from 'lodash';
import auth from './auth/reducer';
import thunk from "redux-thunk";
import { INITIAL_STATE as AUTH_INITIAL_STATE } from "./auth/reducer";

const rootReducer = combineReducers({
    auth,
});

const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
const enhancer = composeEnhancers(
    applyMiddleware(thunk)
);

const authState = { auth: AUTH_INITIAL_STATE };

export default (initialState = {}) => {
    const preloadedState = merge(authState, initialState);

    return createStore(rootReducer, preloadedState, enhancer);
}
