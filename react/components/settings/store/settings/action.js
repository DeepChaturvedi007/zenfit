import axios from 'axios';
import http from '../http'
import _ from 'lodash';
import {
    SAVE_SETTING,
    CHANGE_SAVE_STATUS
} from './types';

import {
    SAVE_GENERAL_SETTINGS,
    EDIT_STRIPE_SETTINGS,
    UPLOAD_PHOTO,
    UPLOAD_LOGO,
    CHANGE_PASSWORD
} from '../../../../api/settings';

export const saveGeneralSetting = (data) => {
    return (dispatch, getState) => {
        dispatch({ type: CHANGE_SAVE_STATUS, payload: "Saving" })
        http.post(SAVE_GENERAL_SETTINGS(), data)
            .then(({ data }) => {
                 dispatch({ type: SAVE_SETTING, payload: data })
                 dispatch({ type: CHANGE_SAVE_STATUS, payload: "Saved" })                 
            })
            .catch(err => {
                dispatch({ type: CHANGE_SAVE_STATUS, payload: "Error" })                 
                toastr.error(err.response.data.reason);
            });
    };
};

export const uploadPhoto = (file) => {
    return (dispatch, getState) => {
        if (file != "" && file != null) {
            var formData = new FormData();
            formData.append("files", file);
            axios.post(UPLOAD_PHOTO(), formData)
                .then((res) => {
                    console.log(res);
                })
                .catch(err => {
                    toastr.error(err.response.data.message);
                });
        }

    };
};

export const uploadLogo = (file) => {
    return (dispatch, getState) => {
        if (file != "" && file != null) {
            var formData = new FormData();
            formData.append("files", file);
            axios.post(UPLOAD_LOGO(), formData)
                .then((res) => {
                    console.log(res);
                })
                .catch(err => {
                    toastr.error(err.response.data.message);
                });
        }
    };
};

export const editStripeSetting = () => {
    return (dispatch, getState) => {
        http.post(EDIT_STRIPE_SETTINGS(), {})
            .then(({ data }) => {
                window.open(data.url, '_blank');
            })
            .catch(function (exception) {
                if (!axios.isCancel(exception)) {
                    throw exception;
                }
            });
    };
};

export const changeSaveStatus = (data) => {
    return (dispatch, getState) => {
        dispatch({ type: CHANGE_SAVE_STATUS, payload: data })
    };
}

export const changePassword = (data) => {
    return (dispatch, getState) => {
        http.post(CHANGE_PASSWORD(), data)
            .then(({ data }) => {
                toastr.success('Password saved');
            })
            .catch(function (exception) {
                toastr.error(exception.response.data.message);
            });
    };
};