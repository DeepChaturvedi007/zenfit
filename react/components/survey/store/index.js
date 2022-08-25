import { applyMiddleware, createStore, combineReducers } from 'redux';
import { createLogger } from 'redux-logger'
import thunkMiddleware from 'redux-thunk';

import survey from './survey/reducer';
import config from './config/reducer';

export default (initialState = {}) => {
  const rootReducer = combineReducers({
    survey,
    config
  });
  const middlewares = [
    thunkMiddleware
  ];

  // DEBUG ONLY!!!
  // middlewares.push(createLogger());

  return createStore(rootReducer, initialState, applyMiddleware(...middlewares));
}
