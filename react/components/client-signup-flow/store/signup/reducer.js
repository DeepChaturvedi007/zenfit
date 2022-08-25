import {CHANGE_LANGUAGE, CHANGE_STEP, MAP_CLIENT_FIELDS, POST_CLIENT, POST_ON_LEAVE_CLIENT, SAVE_STEP} from "./types";
import produce from "immer";
import i18n from "i18next";
import {S3_BEFORE_AFTER_IMAGES, S3_CLIENT_PHOTO} from "../../../../shared/helper/const";
import {DIET_PREFERENCES_ARR} from "../../const";

export const INITIAL_STATE = {
    step: 1,
    clientSubmitting: false,
    clientSubmitError: '',
    account: {},
    general: {},
    photos: {},
    workout: {},
    goal: {},
    diet: {},
    other: {}
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {

        case MAP_CLIENT_FIELDS: {
            return produce(state, draftState => {
                draftState['locale'] = state.locale || 'en'
                i18n.changeLanguage(state.locale || 'en')

                /*MAP all the states from client from backend to frontend structure*/
                Object.entries(payload).map(fieldGroup => {
                    Object.entries(fieldGroup[1]).map( field => Object.assign(draftState[fieldGroup[0]], field[1]))
                })

                /*Custom mapping */
                const questions = {}
                draftState['answers'].map(question => Object.assign(questions, {[(question.questionId).toString()]:question.answer}))
                draftState['other'].questions = questions

                /*Map dietPreference*/
                draftState['diet'].dietPreference = draftState['diet'].clientFoodPreferences.filter(item => DIET_PREFERENCES_ARR.includes(item))
                draftState['diet'].dietPreference.length === 0 && (draftState['diet'].dietPreference = ['none'])

                draftState['diet'].clientFoodPreferences = draftState['diet'].clientFoodPreferences.filter(item => !DIET_PREFERENCES_ARR.includes(item))

                /*Map PHotos to match amazon */
                draftState['general'].photo = draftState['general'].photo && S3_CLIENT_PHOTO+draftState['general'].photo;
                draftState['photos'].front = draftState['clientImages'][0] && S3_BEFORE_AFTER_IMAGES+draftState['clientImages'][0].name;
                draftState['photos'].side = draftState['clientImages'][2] && S3_BEFORE_AFTER_IMAGES+draftState['clientImages'][2].name;
                draftState['photos'].back = draftState['clientImages'][1] && S3_BEFORE_AFTER_IMAGES+draftState['clientImages'][1].name;
            })
        }

        case CHANGE_LANGUAGE: {
            return produce(state, draftState => {
                try {
                    i18n.changeLanguage(payload)
                    draftState.locale = payload
                }catch (e){
                    console.log("lang issue",e)
                }
            })
        }
        case CHANGE_STEP: {
            return produce(state, draftState => {
                draftState.step = payload
            })
        }
        case SAVE_STEP: {
            return produce(state, draftState => {
                draftState[payload.stepName] = payload.stepVal
            })
        }

        case POST_CLIENT.REQUEST: {
            return produce(state, draftState => {
                draftState.clientSubmitting = true
            })
        }

        case POST_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.clientSubmitting = false;
                payload !== 'on_leave_save' && (draftState.step = parseInt(draftState.step) + 1)
            })
        }
        case POST_CLIENT.FAILURE: {
            return produce(state, draftState => {
                draftState.clientSubmitting = false;
                draftState.clientSubmitError = payload;
            })
        }

        default:
            return state;
    }
}
