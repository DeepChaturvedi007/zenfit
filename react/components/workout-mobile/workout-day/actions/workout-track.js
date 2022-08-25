/* jshint esversion: 6 */
import _ from 'lodash';
import 'whatwg-fetch';
import dateService from '../../services/date';
import {
    WEIGHT_DATE_FORMAT,

    ADD_NEW_WEIGHT,
    ADD_TODAY_WEIGHT,
    UPDATE_HISTORY,
    UPDATE_HISTORY_ON_DATE_CHANGE,
    SHOW_TRACK_WEIGHT,
    SHOW_WEIGHT_HISTORY,
    HIDE_TRACK_WEIGHT,
    HIDE_WEIGHT_HISTORY,
    TOGGLE_DATE_PICKER,
    SET_UPDATING_WEIGHT,
    DISABLE_UPDATING_WEIGHT
} from '../constants';
import {
  GET_TRACK_WEIGHT_HISTORY,
  GET_NEW_WEIGHT_LIFTED,
  POST_WEIGHT_LIFTED
} from '../../../../api/workout-api';

const debouncedFetchHistoryData = _.debounce(fetchHistoryData, 1000);

export function addNewTrackWeight(workoutId, date) {
    return dispatch => {
        fetchNewWeight(dispatch, workoutId, date).then(response => {
            dispatch({type: ADD_NEW_WEIGHT, payload: {newItem: response.newEntity}});
        });
    };
}

export function fetchHistory(workoutId) {
    return dispatch => {
        fetch(GET_TRACK_WEIGHT_HISTORY(workoutId), {
            credentials: 'include'
        }).then(response => response.json()).then((history) => {
            dispatch({type: UPDATE_HISTORY, payload: {history}});
        });
    };
}

export function updateWeightStat(id, statKey, statValue) {
    return (dispatch, getState) => {
        const oldHistory = getState().workoutTrack.history;
        const history = oldHistory.map(item => item.id === id ? {...item, [statKey]: statValue} : {...item});
        dispatch({type: UPDATE_HISTORY, payload: {history}});
    };
}

export function updateWeightDate(dateObj) {
    return (dispatch, getState) => {
        const {history: oldHistory, selectedDate: oldSelectedDate} = getState().workoutTrack;
        const selectedDate = dateService.formatDate(dateObj, WEIGHT_DATE_FORMAT);
        const history = oldHistory
            .map(item => ({...item, date: item.date === oldSelectedDate ? selectedDate : item.date}))
            .sort((a, b) => a.date > b.date ? -1 : a.date < b.date ? 1 : a.id - b.id);
        dispatch({type: UPDATE_HISTORY_ON_DATE_CHANGE, payload: {history, selectedDate}});
    };
}

export function deleteWeight(id) {
    return (dispatch, getState) => {
        const oldHistory = getState().workoutTrack.history;
        const history = oldHistory.filter(item => item.id !== id);
        dispatch({type: UPDATE_HISTORY, payload: {history}});
    };
}

export function showTodayTrackWeight() {
    return (dispatch, getState) => {
        const history = getState().workoutTrack.history;
        const workoutId = getState().workoutDay.selectedWorkout.id;
        const todayDate = dateService.formatDate(new Date(), WEIGHT_DATE_FORMAT);

        if (history.length && history[0].date === todayDate) {
            dispatch({type: SHOW_TRACK_WEIGHT, payload: {date: todayDate}});
        } else {
            fetchNewWeight(() => {}, workoutId, todayDate).then(response => {
                dispatch({type: ADD_TODAY_WEIGHT, payload: {newItem: response.newEntity, todayDate}});
            });
        }
    }
}

export function saveWeights(items) {
    return (dispatch, getState) => {
        const history = getState().workoutTrack.history;
        debouncedFetchHistoryData(dispatch, history);
    };
}

export function showTrackWeight(date) {
    return {type: SHOW_TRACK_WEIGHT, payload: {date}};
}

export function showWeightHistory() {
    return {type: SHOW_WEIGHT_HISTORY};
}

export function hideTrackWeight() {
    return {type: HIDE_TRACK_WEIGHT};
}

export function hideWeightHistory() {
    return {type: HIDE_WEIGHT_HISTORY};
}

export function toggleDatePicker() {
    return {type: TOGGLE_DATE_PICKER};
}

//Shared
function fetchNewWeight(dispatch, workoutId, date) {
    return fetch(GET_NEW_WEIGHT_LIFTED(workoutId), {
        method: 'post',
        credentials: 'include',
        body: JSON.stringify({date})
    }).then(response => {
        dispatch({type: DISABLE_UPDATING_WEIGHT});
        return response.json();
    });
}

function fetchHistoryData(dispatch, history) {
    return fetch(POST_WEIGHT_LIFTED(), {
        method: 'post',
        credentials: 'include',
        body: JSON.stringify(history)
    }).then(response => {
        dispatch({type: HIDE_TRACK_WEIGHT});
        return response.json();
    });
}
