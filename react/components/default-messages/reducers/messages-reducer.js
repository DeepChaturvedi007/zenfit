import {
    ADD_FORM_ERROR,
    MESSAGES_CREATE,
    MESSAGES_DELETE,
    MESSAGES_UPDATE,
    POPULATE_FORM,
    TOGGLE_MODAL_FORM
} from '../contants';

import produce from 'immer';

export const INITIAL_STATE = {
    messages: {},
    userId: null,
    isFormModalOpen: false,
    typeTitles: {},
    placeholderLabels: {},
    formValues: {},
    formError: null,
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case MESSAGES_CREATE.SUCCESS:
            return produce(state, draftState => {
                draftState.messages[payload.type][payload.id] = payload;
            });

        case MESSAGES_UPDATE.SUCCESS:
            return produce(state, draftState => {
                draftState.messages[payload.type][payload.id] = payload;
            });

        case MESSAGES_DELETE.SUCCESS:
            return produce(state, draftState => {
                delete draftState.messages[payload.type][payload.id];
            });

        case POPULATE_FORM:
            return {
                ...state,
                formValues: payload,
                formError: null,
            };

        case TOGGLE_MODAL_FORM:
            const isOpen = (payload !== null) ? payload : !state.isFormModalOpen;
            return {
                ...state,
                isFormModalOpen: isOpen,
                formError: null,
            };

        case ADD_FORM_ERROR:
            return {
                ...state,
                formError: payload,
            };

        default:
            return state;
    }
}
