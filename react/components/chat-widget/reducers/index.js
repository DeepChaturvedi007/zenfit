import { combineReducers } from 'redux';
import chat from './chat-reducer';
import global from './global-reducer';

export default combineReducers({
  chat: chat,
  global: global,
});
