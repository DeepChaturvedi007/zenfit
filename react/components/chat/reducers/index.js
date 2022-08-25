import { combineReducers } from 'redux';
import chat from './chat-reducer';
import clients from './clients-reducer';

export default combineReducers({
  chat: chat,
  clients: clients,
  global: state => state || {}
});
