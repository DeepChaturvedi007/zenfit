import {
    SAVE_SETTING,
    CHANGE_SAVE_STATUS
} from "./types";

import produce from 'immer';

export const INITIAL_STATE = {
    settings: [],
    saveStatus: "save"
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SAVE_SETTING: 
            return {...state, settings: payload  };
        case CHANGE_SAVE_STATUS: 
            return { ...state, saveStatus: payload };
        default:
            return state;
    }
}
