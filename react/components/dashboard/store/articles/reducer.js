import {
    success,
    error,
    FETCH,
} from './types';

const INITIAL_STATE = {
    items: [],
    error: null,
    loading: false
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case FETCH: {
            return  {...state, error: null, loading: true};
        }
        case success(FETCH): {
            return  {
                ...state,
                items: [
                  ...state.items,
                  ...payload.items
                ],
                error: null,
                loading: false};
        }
        case error(FETCH): {
            return  {...state, error: payload.error, loading: false};
        }
        default:
            return state;
    }
}