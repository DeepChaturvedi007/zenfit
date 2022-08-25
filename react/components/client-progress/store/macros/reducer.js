import {
    success,
    error,
    SET_CLIENT_ID,
    FETCH_DATA,
} from './types';

const INITIAL_STATE = {
    data: {},
    mapping: {},
    clientId: null,
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SET_CLIENT_ID: {
            return {...state, clientId: payload.id || [] };
        }
        case FETCH_DATA: {
            return {...state, error: null };
        }
        case success(FETCH_DATA): {
            return {...state, data: { ...state.data, ...payload.data }};
        }
        case error(FETCH_DATA): {
            return {...state, error: payload && payload.error};
        }
        default:
            return state;
    }
}