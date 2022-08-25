/*jshint esversion: 6 */
import { combineReducers } from 'redux';
import global from './reducers/workout-global';
import workoutDay from './reducers/workout-day';
import workoutTrack from './reducers/workout-track';

export default combineReducers({
    workoutDay,
    workoutTrack,
    global
});