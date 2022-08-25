/*jshint esversion: 6 */
import {
    FETCH_WORKOUTS_DAY,
    CREATE_WORKOUTS,
    UPDATE_DAY_COMMENT,
    UPDATE_DAY_WORKOUTS,
    SHOW_ADD_EXERCISES,
    SHOW_WORKOUT_DETAILS,
    SHOW_SUPERSET_OPTIONS_LIST,
    HIDE_ADD_EXERCISES,
    HIDE_WORKOUT_DETAILS,
    HIDE_SUPERSET_OPTIONS_LIST,
    TOGGLE_TIMER,
    TOGGLE_WORKOUT_REMOVE,
    REMOVE_WORKOUT,
    DISABLE_DAY_DATA_UPDATING,
    HIDE_ASSIGN_PLAN_BAR,
    HIDE_ASSIGN_CLIENTS_LIST,
    HIDE_ADD_CLIENT_MODAL,
    HIDE_ASSIGN_TEMPLATE,
    SET_ASSIGN_CLIENT_DATA
} from '../constants';

const INITIAL_STATE = {
    workouts: [],
    comment: '',
    selectedWorkout: {},
    selectedSuperSetId: null,
    isDayDataUpdating: false,
    isLoading: false,
    isShowAddExercises: false,
    isShowTimer: false,
    isShowWorkoutDetails: false,
    isShowWorkoutRemove: false,
    isShowOptionsList: false,
    isShowSuperSetOptionsList: false,
    isStatDisabled: false,
    isShowAssignPlanBar: true,
    isShowClients: false,
    isShowAddNewClient: false,
    isShowAssignTemplate: false,
    clientsData: []
};

export default function (state = INITIAL_STATE, {type, payload}) {

    switch (type) {
        case FETCH_WORKOUTS_DAY.REQUEST:
            return {...state, isLoading: true};

        case FETCH_WORKOUTS_DAY.SUCCESS:
            return {
                ...state,
                workouts: [...payload.workouts],
                comment: payload.comment,
                isShowAssignPlanBar: payload.workouts.length ? false : true,
                isLoading: false
            };

        case CREATE_WORKOUTS:
            const workouts = payload.parentId ? state.workouts.map(workout => {
                return {
                    ...workout,
                    supers: payload.parentId === workout.id
                        ? workout.supers.concat(payload.newWorkouts)
                        : workout.supers
                };
            }) : state.workouts.concat(payload.newWorkouts);

            return {
                ...state,
                workouts,
                isShowWorkoutDetails: false,
                isShowAddExercises: false,
                selectedSuperSetId: null,
            };

        case UPDATE_DAY_COMMENT:
            return {...state, comment: payload.comment};

        case UPDATE_DAY_WORKOUTS:
            return {
                ...state,
                isDayDataUpdating: true,
                workouts: [...payload.workouts],
                selectedWorkout: {...payload.selectedWorkout},
                isStatDisabled: payload.isStatDisabled,
                isShowAssignPlanBar: payload.workouts.length ? false : true
            };

        case SHOW_ADD_EXERCISES:
            return {...state, isShowAddExercises: true, selectedSuperSetId: payload.superSetId};

        case SHOW_WORKOUT_DETAILS:
            let isStatDisabled = payload.isSuperSetWorkout ? true : false;
            let filteredWorkout;
            if (payload.parentId) {
                let selectedSuperSet = state.workouts.filter(workout => workout.id === payload.parentId)[0].supers;
                filteredWorkout = selectedSuperSet.filter(workout => workout.id === payload.workoutId);
                isStatDisabled = !(filteredWorkout[0] === selectedSuperSet[selectedSuperSet.length - 1]);
            } else {
                filteredWorkout = state.workouts.filter(workout => workout.id === payload.workoutId);
            }

            return {
                ...state,
                isShowWorkoutDetails: true,
                selectedWorkout: {...filteredWorkout[0]},
                isStatDisabled: isStatDisabled
            };

        case SHOW_SUPERSET_OPTIONS_LIST:
            return {...state, isShowSuperSetOptionsList: true, selectedSuperSetId: payload.superSetId};

        case HIDE_ADD_EXERCISES:
            return {...state, isShowAddExercises: false, selectedSuperSetId: null};

        case HIDE_WORKOUT_DETAILS:
            return {...state, isShowWorkoutDetails: false, selectedWorkout: {}};

        case HIDE_SUPERSET_OPTIONS_LIST:
            return {...state, isShowSuperSetOptionsList: false, selectedSuperSetId: null};

        case TOGGLE_TIMER:
            return {...state, isShowTimer: !state.isShowTimer};

        case TOGGLE_WORKOUT_REMOVE:
            return {...state, isShowWorkoutRemove: !state.isShowWorkoutRemove};

        case REMOVE_WORKOUT:
            return {
                ...state,
                isShowWorkoutDetails: false,
                isShowWorkoutRemove: false,
                selectedWorkout: {},
                workouts: payload.workouts
            };

        case DISABLE_DAY_DATA_UPDATING:
            return {...state, isDayDataUpdating: false};

        case HIDE_ASSIGN_PLAN_BAR:
            return { ...state, isShowAssignPlanBar: false};

        case HIDE_ASSIGN_CLIENTS_LIST:
            return { ...state, isShowClients: payload.status};

        case HIDE_ADD_CLIENT_MODAL:
            return { ...state, isShowAddNewClient: payload.status};

        case HIDE_ASSIGN_TEMPLATE:
            return { ...state, isShowAssignTemplate: payload.status};

        case SET_ASSIGN_CLIENT_DATA:
            return { ...state, clientsData: [...payload.clientsData]};

        default:
            return state;
    }
}
