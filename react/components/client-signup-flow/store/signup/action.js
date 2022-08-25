import {
    CHANGE_LANGUAGE,
    CHANGE_STEP,
    MAP_CLIENT_FIELDS,
    POST_CLIENT,
    SAVE_STEP
} from './types';
import {SUBMIT_CLIENT_INFO_REACT_API, UPLOAD_CLIENT_IMAGES} from "../../../../api/clients";
import http from "../../../../shared/helper/http";
import {DIET_PREFERENCES_ARR, SIGNUP_FLOW_FIELDS} from "../../const";

export const saveFieldsAction = (obj, step, stepName) => {
    return dispatch => {
        dispatch({type: CHANGE_STEP, payload: step});
        dispatch({type: SAVE_STEP, payload: {stepVal: obj, stepName: stepName}})
    };
}

export const submitClientAction = (obj, stepName) => {
    return async (dispatch, getState) => {
        dispatch({type: SAVE_STEP, payload: {stepVal: obj, stepName: stepName}})
        dispatch({type: POST_CLIENT.REQUEST})

        let fields = {};
        const fieldsPosted = ['account', 'general', 'workout', 'goal', 'diet', 'other'];

        const shouldPostImg = (
            typeof (getState().signup.photos.front) !== "string" && getState().signup.photos.front !== undefined ||
            typeof (getState().signup.photos.side) !== 'string' && getState().signup.photos.side !== undefined ||
            typeof (getState().signup.photos.back) !== 'string' && getState().signup.photos.back !== undefined ||
            typeof (getState().signup.general.photo) !== 'string' && getState().signup.general.photo !== undefined
        )

        Object.entries(getState().signup).map(field =>
            fieldsPosted.includes(field[0].toString()) && Object.assign(fields, field[1])
        )

        fields['datakey'] = getState().signup.datakey;
        fields['client'] = getState().signup.client.id;
        fields['isQuestionnaire'] = stepName !== 'account' && (getState().signup.config === 'survey' || getState().signup.config === 'full')

        /*Remove photo from request */
        delete fields['photo']

        !fields['startWeight'] && (fields['startWeight'] = '')
        !fields['clientFoodPreferences'] && (fields['clientFoodPreferences'] = '')

        /*Cleanup array for splited options*/
        fields['clientFoodPreferences'] = fields['clientFoodPreferences'].filter(item => !DIET_PREFERENCES_ARR.includes(item))
        getState().signup.diet.dietPreference && fields['clientFoodPreferences'].push(getState().signup.diet.dietPreference.toString())
        try {
            await http.post(SUBMIT_CLIENT_INFO_REACT_API(), fields);

            if (shouldPostImg) {

                let imgs = new FormData();
                getState().signup.photos.front && imgs.append('front-img', getState().signup.photos.front)
                getState().signup.photos.side && imgs.append('side-img', getState().signup.photos.side)
                getState().signup.photos.back && imgs.append('back-img', getState().signup.photos.back)
                getState().signup.general.photo && imgs.append('profile-img', getState().signup.general.photo)

                await http.post(UPLOAD_CLIENT_IMAGES(getState().signup.client.id), imgs)
            }

            dispatch({type: POST_CLIENT.SUCCESS, payload: stepName})


        } catch ({response}) {
            dispatch({type: POST_CLIENT.FAILURE, payload: response.data})
        }
    }
}

export const ChangeLangAction = (newLang) => {
    return (dispatch, getState) => {
        dispatch({type: CHANGE_LANGUAGE, payload: newLang});
    };
}

export const ChangeStepAction = (newStep) => {
    return (dispatch, getState) => {
        dispatch({type: CHANGE_STEP, payload: newStep});
    };
}

export const mapClientToFieldsAction = () => {
    /*IS LINKED TO SIGNUP_FLOW FIELDS, which maps which fields are needed*/
    return (dispatch, getState) => {
        let mappedFields = {};
        const currentClient = getState().signup.client;
        Object.entries(SIGNUP_FLOW_FIELDS).map(field => {
            const nested = Object.keys(field[1])
                .filter(key => currentClient[key])
                .map(key => {
                    return {[key]: currentClient[key]}
                })
            nested.length > 0 && Object.assign(mappedFields, {
                [field[0]]: nested
            })
        })

        dispatch({type: MAP_CLIENT_FIELDS, payload: mappedFields})

    }
}
