import {
    request,
    success,
    error,
    FETCH,
    FETCH_EXERCISES,
    FETCH_EXERCISE,
    SET_MFP_INTEGRATION_INFO,
    GET_WORKOUT
} from './types'
import { SET_CLIENT_ID } from '../macros/types'

const INITIAL_STATE = {
    clientId: null,
    combined: {},
    mfpLink: false,
    error: null,
    loading: false,
    exerciseLoading: false,
    exercisesLoading: false,
    client: null,
    exercise: [],
    exercises: [],
    currentPlan: null,
    workouts: [],
    workoutsLoading: false
}

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SET_CLIENT_ID: {
            return {...state, clientId: payload.id || [] };
        }
        case SET_MFP_INTEGRATION_INFO: {
            return {...state, mfpLink: payload.link || false};
        }
        case FETCH: {
            return {...state, loading: true, error: null};
        }

        case FETCH_EXERCISE: {
            return {...state, exerciseLoading: true, error: null}
        }

        case FETCH_EXERCISES: {
            return {...state, exercisesLoading: true, error: null}
        }
        case request(GET_WORKOUT): {
            return {...state, workoutsLoading: true}
        }
        case success(FETCH): {
            return {
                ...state,
                loading: false,
                combined: payload.stats.combined,
                currentPlan: payload.stats.currentPlan,
                workouts: payload.stats.savedWorkouts
            }
        }
        case success(FETCH_EXERCISES): {
            return {
                ...state,
                exercisesLoading: false,
                exercises: payload.stats.exercises
            }
        }
        case success(FETCH_EXERCISE): {
            return {
                ...state,
                exerciseLoading: false,
                exercise: payload.stats.exercise
            }
        }
        case success(GET_WORKOUT): {
            return {
                ...state,
                workouts: payload.savedWorkouts,
                workoutsLoading: false
            }
        }
        case error(FETCH):
        case error(FETCH_EXERCISE):
        case error(FETCH_EXERCISES): {
            return {
                ...state,
                error: payload.error,
                loading: false,
                exerciseLoading: false,
                exercisesLoading: false,
                workoutsLoading: false,
                combined: {},
                exercise: [],
                exercises: [],
                currentPlan: null,
                workouts: [],
            }
        }
        default:
            return state;
    }
}
