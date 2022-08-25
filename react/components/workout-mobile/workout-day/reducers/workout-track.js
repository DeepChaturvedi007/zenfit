/* jshint esversion: 6 */
import {
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

const INITIAL_STATE = {
    history: [],
    selectedDate: null,
    isShowTrackWeight: false,
    isShowHistory: false,
    isOpenDatePicker: false,
    isUpdatingWeight: false
};

export default function (state = INITIAL_STATE, {type, payload}) {

    switch (type) {
        case ADD_NEW_WEIGHT:
            const newHistory = [...state.history];
            newHistory.push(payload.newItem);

            return {
                ...state,
                history: newHistory.sort((a, b) => a.date > b.date ? -1 : a.date < b.date ? 1 : a.id - b.id)
            };

        case ADD_TODAY_WEIGHT:
            const history = [...state.history];
            history.push(payload.newItem);

            return {
                ...state,
                history: history.sort((a, b) => a.date > b.date ? -1 : a.date < b.date ? 1 : a.id - b.id),
                selectedDate: payload.todayDate,
                isShowTrackWeight: true
            };

        case UPDATE_HISTORY:
            return {...state, history: [...payload.history]};

        case UPDATE_HISTORY_ON_DATE_CHANGE:
            return {
                ...state,
                history: [...payload.history],
                selectedDate: payload.selectedDate,
                isOpenDatePicker: false
            };

        case SHOW_TRACK_WEIGHT:
            return {...state, selectedDate: payload.date, isShowTrackWeight: true, isShowHistory: false};

        case SHOW_WEIGHT_HISTORY:
            return {...state, selectedDate: null, isShowTrackWeight: false, isShowHistory: true};

        case HIDE_TRACK_WEIGHT:
            return {...state, selectedDate: null, isShowTrackWeight: false};

        case HIDE_WEIGHT_HISTORY:
            return {...state, selectedDate: null, isShowHistory: false};

        case TOGGLE_DATE_PICKER:
            return {...state, isOpenDatePicker: !state.isOpenDatePicker};

        case SET_UPDATING_WEIGHT:
            return {...state, isUpdatingWeight: true};

        case DISABLE_UPDATING_WEIGHT:
            return {...state, isUpdatingWeight: false};

        default:
            return state;
    }
}
