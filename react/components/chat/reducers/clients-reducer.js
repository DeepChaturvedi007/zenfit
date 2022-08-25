import get from 'lodash/get';
import {CLIENTS_FETCH} from "../constants";

const INITIAL_STATE = {
    clients: [],
    isLoading: false,
    query: '',
    tags: [],
};

export default function (state = INITIAL_STATE, {type, payload}) {
    const query = get(payload, 'q', state.query);
    const tags = get(payload, 'tags', state.tags);

    switch (type) {
        case CLIENTS_FETCH.REQUEST:
            return {
                ...state,
                isLoading: true,
                query,
                tags,
            };
        case CLIENTS_FETCH.SUCCESS:
            return {
                ...state,
                clients: [...payload.clients],
                isLoading: false,
                query,
                tags,
            };
        default:
            return state;
    }
}
