import { applyMiddleware, createStore, combineReducers } from 'redux';
import { createLogger } from 'redux-logger'
import thunkMiddleware from 'redux-thunk';

import stats from './stats/reducer';
import articles from './articles/reducer';
import news from './news/reducer';
import videos from './videos/reducer';

export default (initialState = {}) => {
  const rootReducer = combineReducers({
    stats,
    articles,
    news,
    videos
  });
  const middlewares = [
    thunkMiddleware
  ];

  middlewares.push(createLogger());

  return createStore(rootReducer, initialState, applyMiddleware(...middlewares));
}
