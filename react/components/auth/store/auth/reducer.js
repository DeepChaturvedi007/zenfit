import {
    CHANGE_MESSAGE,
    CHANGE_ERROR,
    SET_VIEW, CREATE_USER
} from "./types";
import produce from "immer";

export const INITIAL_STATE = {
    error: "",
    message: "",
    userCreated: false,
    userSubmitting: false,
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case CHANGE_MESSAGE:
            return {...state, message: payload};
        case CHANGE_ERROR:
            return {...state, error: payload};
        case SET_VIEW:
            return {...state, view: payload};

        case CREATE_USER.REQUEST: {
            return produce(state, draftState => {
                draftState.userSubmitting = true;
                draftState.error = "";
            })
        }
        case CREATE_USER.SUCCESS: {
            return produce(state, draftState => {
                draftState.userSubmitting = false;
                draftState.error = "";
                draftState.userCreated = true;
            })
        }
        case CREATE_USER.FAILURE: {
            return produce(state, draftState => {
                draftState.userSubmitting = false;
                draftState.error = payload;
            })
        }

        default:
            return state;
    }
}
