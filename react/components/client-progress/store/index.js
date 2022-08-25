import thunkMiddleware from 'redux-thunk';
import { applyMiddleware, createStore, combineReducers } from 'redux';

import stats from './stats/reducer';
import macros from './macros/reducer';
import chart from './chart/reducer';
import progress from './progress/reducer';

const rootReducer = combineReducers({
  stats,
  macros,
  chart,
  progress
});

export default (initialState = {}) => {
  return createStore(rootReducer, initialState, applyMiddleware(thunkMiddleware));
}