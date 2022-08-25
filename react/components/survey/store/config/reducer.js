import {
    SETUP
} from './types';

const INITIAL_STATE = {
    ready: false
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SETUP: {
            return {
                ...state,
                ...payload,
                ready: true
            }
        }
        default:
            return state;
    }
}