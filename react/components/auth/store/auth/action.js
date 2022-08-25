import http from '../../../../shared/helper/http';
import {setToken, setRefreshToken} from '../../../../shared/helper/jwt';
import {
    CHANGE_MESSAGE,
    CHANGE_ERROR,
    SET_VIEW, CREATE_USER
} from './types';

import {
    LOGIN,
    FORGOT_PASSWORD,
    SAVE_PASSWORD,
    SIGNUP
} from '../../../../api/auth';
import {POST_USER} from "../../../client-signup-flow/store/signup/types";

export const login = (data) => {
    const rememberMe = data.rememberMe;
    return (dispatch) => {
        let value = {username: data.email, password: data.password};

        http.post(LOGIN(), value)
            .then(({data}) => {
                dispatch({type: CHANGE_ERROR, payload: ""});
                setToken(data.token);
                setRefreshToken(data.refresh_token, rememberMe);
                window.location.replace("/dashboard");
            })
            .catch(err => {
                dispatch({type: CHANGE_ERROR, payload: err.response.data.message})
            });
    };
};

export const forgotPasswordClick = (data) => {
    let value = {email: data.resetEmail}
    return (dispatch) => {
        http.post(FORGOT_PASSWORD(), value)
            .then(({data}) => {
                dispatch({type: CHANGE_ERROR, payload: ""})
                dispatch({type: CHANGE_MESSAGE, payload: data.message})
            })
            .catch(err => {
                dispatch({type: CHANGE_MESSAGE, payload: ""})
                dispatch({type: CHANGE_ERROR, payload: err.response.data.message})
            });
    };
};

export const savePasswordClick = (data) => {
    let value = {password1: data.newPassword, password2: data.repeatPassword, datakey: data.datakey}
    return (dispatch) => {
        http.post(SAVE_PASSWORD(), value)
            .then(({data}) => {
                dispatch({type: CHANGE_ERROR, payload: ""})
                dispatch({type: CHANGE_MESSAGE, payload: data.message})
                setTimeout(function () {
                    window.location.replace("/login");
                }, 800);
            })
            .catch(err => {
                dispatch({type: CHANGE_MESSAGE, payload: ""})
                dispatch({type: CHANGE_ERROR, payload: err.response.data.message})
            });
    };
};

export const changeView = (value) => {
    return (dispatch, getState) => {
        dispatch({type: SET_VIEW, payload: value})
        dispatch({type: CHANGE_ERROR, payload: ""})
        dispatch({type: CHANGE_MESSAGE, payload: ""})
    };
};

export const createUserAction = (data) => {
    return async (dispatch) => {
        dispatch({type: CREATE_USER.REQUEST})
        try {
            const response = await http.post(SIGNUP(), data)
            dispatch({type: CREATE_USER.SUCCESS, payload: response.data})
        } catch (e) {
            dispatch({type: CREATE_USER.FAILURE, payload: e.response.data})

        }
    };
}
