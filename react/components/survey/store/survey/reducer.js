import {
    success,
    error,
    SAVE_DATA,
    SET_VALUE,
    FLUSH_MEAL,
    FLUSH_WORKOUT,
    SET_FIELD_ERROR,
    UNSET_FIELD_ERROR
} from './types';


const defaultData = {
    // general
    name: '',
    phone: '',
    email: '',
    age: undefined,
    gender: undefined,
    photo: {
        front: undefined,
        side: undefined,
        back: undefined,
    },
    activity: undefined,
    injuries: [],
    other: '',
    goalType: undefined,
    primaryGoal: undefined,
    goalParts: [],
    startWeight: undefined,
    goalWeight: undefined,
    height: undefined,
    fat: undefined,
    measuringSystem: undefined,
    // meal
    foodPreferences: [],
    dietStyle: '',
    budget: undefined,
    cookingTime: undefined,
    excludeIngredients: [],
    numberOfMeals: undefined,
    // workout
    workoutsPerWeek: undefined,
    experience: undefined,
    place: undefined,
    exercisePreferences: '',
};

const INITIAL_STATE = {
    data: {...defaultData},
    error: null,
    loading: false
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SET_VALUE: {
            const {key, value} = payload;
            return {
                ...state,
                data: {
                    ...state.data,
                    [key]: value
                }
            }
        }
        case UNSET_FIELD_ERROR: {
            const {field} = payload;
            let currentError = _.cloneDeep(state.error);
            if(!!currentError && !!currentError.errors) {
                currentError.errors = currentError.errors.filter(e => e.field !== field);
            }
            return {
                ...state,
                error: currentError
            }
        }
        case SET_FIELD_ERROR: {
            const {field, message} = payload;
            let currentError = _.cloneDeep(state.error) || {};
            if(!currentError.errors) {
                currentError.errors = []
            }
            currentError.errors.push({
                field, type: undefined, message
            });
            return {
                ...state,
                error: currentError
            }
        }
        case SAVE_DATA: {
            return  {...state, error: null, loading: true};
        }
        case FLUSH_MEAL: {
            return  {
                ...state,
                data: {
                    ...state.data,
                    foodPreferences: [],
                    dietStyle: '',
                    budget: undefined,
                    cookingTime: undefined,
                    excludeIngredients: [],
                }
            };
        }
        case FLUSH_WORKOUT: {
            return  {
                ...state,
                data: {
                    ...state.data,
                    workoutsPerWeek: undefined,
                    experience: undefined,
                    place: undefined,
                    exercisePreferences: '',
                }
            };
        }
        case success(SAVE_DATA): {
            return  {
                data: {...defaultData},
                error: null,
                loading: false
            };
        }
        case error(SAVE_DATA): {
            return  {...state, error: payload.error, loading: false};
        }
        default:
            return state;
    }
}