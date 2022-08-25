import {
    POPULATE_FORM,
    MESSAGES_CREATE,
    MESSAGES_UPDATE,
    MESSAGES_DELETE,
    TOGGLE_MODAL_FORM,
    ADD_FORM_ERROR,
} from '../contants';
import {
    CREATE_DEFAULT_MESSAGE,
    UPDATE_DEFAULT_MESSAGE,
    DELETE_DEFAULT_MESSAGE,
} from '../../../api/default-messages';

import axios from 'axios';

export const populateForm = (id = '', type = '', body = '', title = '', subject = '') => {
    return {type: POPULATE_FORM, payload: {id: id || '', type: type || '', body: body || '', title: title || '', subject: subject || ''}};
};

export const toggleFormModal = (isOpen = null) => {
    return {type: TOGGLE_MODAL_FORM, payload: isOpen};
};

export const createMessage = (type, title, body, subject) => {
    const formData = {
        type: type,
        title: title,
        textarea: body,
        subject: subject,
    };
    return (dispatch, getState) => {
        axios.post(CREATE_DEFAULT_MESSAGE(), formData)
            .then(res => {
                dispatch({type: MESSAGES_CREATE.SUCCESS, payload: res.data.data});
                dispatch(toggleFormModal(false));
            })
            .catch(res => {
                dispatch({type: ADD_FORM_ERROR, payload: res.data.reason || 'Something went wrong'});
            })
    };
};

export const updateMessage = (id, type, title, body, subject) => {
    const formData = {
        type: type,
        title: title,
        textarea: body,
        subject: subject,
    };
    return (dispatch, getState) => {
        axios.post(UPDATE_DEFAULT_MESSAGE(id), formData)
            .then(res => {
                dispatch({type: MESSAGES_UPDATE.SUCCESS, payload: res.data.data});
                dispatch(toggleFormModal(false));
            })
            .catch(res => {
                dispatch({type: ADD_FORM_ERROR, payload: res.data.reason || 'Something went wrong'});
            })
    };
};

export const deleteMessage = (id) => {
    return (dispatch, getState) => {
        axios.get(DELETE_DEFAULT_MESSAGE(id))
            .then(res => {
                dispatch({type: MESSAGES_DELETE.SUCCESS, payload: res.data.data});
            })
    };
};
