/* jshint esversion: 6 */
import _ from 'lodash';
import 'whatwg-fetch';
import {
    DROPSET_TEXT,
    CREATE_WORKOUTS,
    FETCH_WORKOUTS_DAY,
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
import {
    GET_WORKOUT_PLAN_DAY,
    SAVE_WORKOUT_DAY_PLAN,
    CREATE_WORKOUT_PLAN,
    APPLY_WORKOUT_TEMPLATE_TO_CLIENTS
} from '../../../../api/workout-api';

const workoutStatsDefaultValues = {
    sets: 3,
    reps: 12,
    rest: 60,
    startWeight: '',
    tempo: '',
    rm: ''
};
const debouncedUpdateDayData = _.debounce(updateDayData, 1000);
const debouncedUpdateDayExercises = _.debounce(updateDayExercises, 1000);

//Actions
export function fetchDay() {
    return (dispatch, getState) => {
        const {dayId} = getState().global;
        dispatch({type: FETCH_WORKOUTS_DAY.REQUEST});
        const url = GET_WORKOUT_PLAN_DAY(dayId);
        return fetch(url, {
            credentials: 'include'
        }).then(response => response.json()).then(response => {
            dispatch({
                type: FETCH_WORKOUTS_DAY.SUCCESS,
                payload: {
                    workouts: response.workouts,
                    comment: response.comment
                }
            });
        });
    };
}

export function addSortableWorkouts(oldIndex, newIndex, fromParentId, toParentId) {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts, isStatDisabled} = getState().workoutDay;
        const workouts = [...oldWorkouts];
        let movedWorkout;
        if (fromParentId) {
            workouts.forEach(workout => {
                if (workout.id === fromParentId) {
                    if (oldIndex && oldIndex === workout.supers.length - 1) {
                        workout.supers[oldIndex - 1].rest = workoutStatsDefaultValues['rest'];
                    }
                    movedWorkout = workout.supers.splice(oldIndex, 1)[0];
                    movedWorkout.rest = (!movedWorkout.rest || movedWorkout.rest === '0')
                        ? workoutStatsDefaultValues['rest']
                        : movedWorkout.rest;
                }
            });
        } else {
            movedWorkout = workouts.splice(oldIndex, 1)[0];
        }
        if (toParentId) {
            workouts.forEach(workout => {
                if (workout.id === toParentId) {
                    let rest = 0;
                    if (newIndex === workout.supers.length) {
                        rest = (!movedWorkout.rest || movedWorkout.rest === '0')
                            ? workoutStatsDefaultValues['rest']
                            : movedWorkout.rest;
                        workout.supers[newIndex - 1].rest = 0;
                    }
                    workout.supers.splice(newIndex, 0, {...movedWorkout, rest: rest, sets: workout.sets, supers: null});
                    workout.supers.forEach((supWorkout, i) => {supWorkout.order = i+1});
                }
            });
        } else {
            workouts.splice(newIndex, 0, {...movedWorkout, supers: movedWorkout.supers || []});
            workouts.forEach((workout, i) => {workout.order = i+1});
        }
        dispatch({type: UPDATE_DAY_WORKOUTS, payload: {workouts, selectedWorkout: {}, isStatDisabled}});
        updateDayExercises(dispatch, dayId, workouts, isTemplate);
    };
}

export function createWorkouts(exercises) {
    return (dispatch, getState) => {
        const {dayId} = getState().global;
        const {workouts, selectedSuperSetId, isStatDisabled} = getState().workoutDay;
        let startOrderBy = workouts.length;
        let restDefault = workoutStatsDefaultValues['rest'];
        if (selectedSuperSetId) {
            let filteredWorkout = workouts.filter(workout => workout.id === selectedSuperSetId)[0];
            startOrderBy = filteredWorkout.supers.length;
            if(startOrderBy) {
                restDefault = filteredWorkout.supers[startOrderBy - 1].rest;
            }
        }
        const newWorkouts = exercises.map(exercise => {
            let rest = (exercise != exercises[exercises.length - 1]) && selectedSuperSetId
                ? 0
                : restDefault;
            return createWorkout(exercise, startOrderBy++, selectedSuperSetId, rest);
        });
        const urlParentId = selectedSuperSetId ? `/${selectedSuperSetId}` : '';
        const url = CREATE_WORKOUT_PLAN(dayId, urlParentId);
        fetch(url, {
            method: 'post',
            credentials: 'include',
            body: JSON.stringify(newWorkouts)
        }).then(response => response.json()).then(response => {
            dispatch({
                type: UPDATE_DAY_WORKOUTS,
                payload: {workouts: response.oldWorkouts, selectedWorkout: {}, isStatDisabled}
            });
            dispatch({
                type: CREATE_WORKOUTS,
                payload: {newWorkouts: response.newWorkouts, parentId: selectedSuperSetId}
            });
        });
    };
}

export function updateSortableWorkouts(oldIndex, newIndex, parentId) {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts, isStatDisabled} = getState().workoutDay;
        const workouts = [...oldWorkouts];
        if (parentId) {
            workouts.forEach(workout => {
                if (workout.id === parentId) {
                    if (newIndex === workout.supers.length - 1) {
                        workout.supers[oldIndex].rest = workout.supers[newIndex].rest || workoutStatsDefaultValues['rest'];
                        workout.supers[newIndex].rest = 0;
                    } else if (oldIndex === workout.supers.length - 1) {
                        workout.supers[oldIndex - 1].rest = workout.supers[oldIndex].rest || workoutStatsDefaultValues['rest'];
                        workout.supers[oldIndex].rest = 0;
                    }
                    workout.supers.splice(newIndex, 0, workout.supers.splice(oldIndex, 1)[0]);
                    workout.supers.forEach((supWorkout, i) => {supWorkout.order = i+1});
                }
            });
        } else {
            workouts.splice(newIndex, 0, workouts.splice(oldIndex, 1)[0]);
            workouts.forEach((workout, i) => {workout.order = i+1});
        }
        dispatch({type: UPDATE_DAY_WORKOUTS, payload: {workouts, selectedWorkout: {}, isStatDisabled}});
        updateDayExercises(dispatch, dayId, workouts, isTemplate);
    }
}

export function updateDayComment(comment) {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        dispatch({type: UPDATE_DAY_COMMENT, payload: {comment}});
        debouncedUpdateDayData(dispatch, dayId, {comment}, isTemplate);
    };
}

export function updateWorkoutStat(statKey, statValue, isDisabled = null) {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts = [], selectedWorkout: oldSelectedWorkout, isStatDisabled} = getState().workoutDay;
        const workouts = updateWorkoutValue(oldWorkouts, oldSelectedWorkout.id, statKey, statValue);
        const selectedWorkout = {...oldSelectedWorkout, [statKey]: statValue};
        const newWorkouts = updateWorkoutSuperSetSetsStat(workouts, selectedWorkout);
        dispatch({
            type: UPDATE_DAY_WORKOUTS,
            payload: {
                workouts: newWorkouts,
                selectedWorkout,
                isStatDisabled: isDisabled !== null ? isDisabled : isStatDisabled
            }
        });
        debouncedUpdateDayExercises(dispatch, dayId, newWorkouts, isTemplate);
    };
}

export function updateWorkoutComment(comment) {
    return updateWorkoutStat('comment', comment);
}

export function updateWorkoutDropSet() {
    return (dispatch, getState) => {
        const {selectedWorkout} = getState().workoutDay;
        const comment = selectedWorkout.comment && selectedWorkout.comment.indexOf(DROPSET_TEXT) !== -1
            ? selectedWorkout.comment.replace(new RegExp(DROPSET_TEXT, 'g'), '').trim()
            : `${DROPSET_TEXT}\n${selectedWorkout.comment || ''}`;

        return updateWorkoutStat('comment', comment)(dispatch, getState);
    };
}

export function updateWorkoutStatToDefault(statKey) {
    return updateWorkoutStat(statKey, workoutStatsDefaultValues[statKey]);
}

export function showAddExercises(superSetId = null) {
    return {type: SHOW_ADD_EXERCISES, payload: {superSetId}};
}

export function showWorkoutDetails(workoutId = null, parentId, isSuperSetWorkout) {
    return dispatch => {
        dispatch({type: HIDE_ASSIGN_PLAN_BAR});
        dispatch({type: SHOW_WORKOUT_DETAILS, payload: {workoutId, parentId, isSuperSetWorkout}});
    };
}

export function showSuperSetOptionsList(superSetId) {
    return {type: SHOW_SUPERSET_OPTIONS_LIST, payload: {superSetId}};
}

export function hideAddExercises() {
    return {type: HIDE_ADD_EXERCISES};
}

export function hideWorkoutDetails() {
    return {type: HIDE_WORKOUT_DETAILS};
}

export function hideSuperSetOptionsList() {
    return {type: HIDE_SUPERSET_OPTIONS_LIST};
}

export function toggleTimer() {
    return {type: TOGGLE_TIMER};
}

export function toggleWorkoutDelete() {
    return {type: TOGGLE_WORKOUT_REMOVE};
}

export function removeWorkoutSuperSet() {
    return updateWorkoutStat('supers', [], false);
}

export function removeWorkoutFromSuperSet() {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts, selectedWorkout: oldSelectedWorkout} = getState().workoutDay;
        let restValue = oldSelectedWorkout.rest ? oldSelectedWorkout.rest : workoutStatsDefaultValues['rest'];
        const newOldWorkouts = updateWorkoutValue(oldWorkouts, oldSelectedWorkout.id, 'rest', restValue);
        let count = 0;
        const workouts = newOldWorkouts.reduce((workouts, workout) => {
            let supCount = 0;
            const newWorkout = {...workout, order: ++count};
            workouts.push(newWorkout);
            newWorkout.supers = newWorkout.supers.reduce((supWorkouts, supWorkout) => {
                if (oldSelectedWorkout.id !== supWorkout.id) {
                    supWorkouts.push({...supWorkout, order: ++supCount});
                } else {
                    workouts.push({...supWorkout, order: ++count, supers: []});
                }
                return supWorkouts;
            }, []);
            return workouts
        }, []);
        const selectedWorkout = {...oldSelectedWorkout, rest: restValue, supers: []};
        dispatch({type: UPDATE_DAY_WORKOUTS, payload: {workouts, selectedWorkout, isStatDisabled: false}});
        updateDayExercises(dispatch, dayId, workouts, isTemplate);
    }
}

export function removeWorkout() {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts, selectedWorkout} = getState().workoutDay;
        let count = 0;
        const workouts = oldWorkouts.reduce((newWorkouts, workout) => {
            let supCount = 0;
            if (workout.id !== selectedWorkout.id) {
                newWorkouts.push({
                    ...workout,
                    order: ++count,
                    supers: workout.supers.reduce((newSupers, supWorkout, i, arr) => {
                        if (supWorkout.id !== selectedWorkout.id) {
                            newSupers.push({...supWorkout, order: ++supCount});
                        } else if (i && arr.length - 1 === i) {
                            newSupers[i - 1].rest = workoutStatsDefaultValues['rest'];
                        }
                        return newSupers;
                    }, [])
                });
            }
            return newWorkouts;
        }, []);
        dispatch({type: REMOVE_WORKOUT, payload: {workouts}});
        updateDayExercises(() => {}, dayId, workouts, isTemplate);
    };
}

export function removeSuperSetWorkoutId(workoutId) {
    return (dispatch, getState) => {
        const {dayId, isTemplate} = getState().global;
        const {workouts: oldWorkouts, isStatDisabled} = getState().workoutDay;
        const workouts = updateWorkoutValue(oldWorkouts, workoutId, 'supers', []);
        dispatch({type: UPDATE_DAY_WORKOUTS, payload: {workouts, selectedWorkout: {}, isStatDisabled}});
        debouncedUpdateDayExercises(dispatch, dayId, workouts, isTemplate);
    };
}

export function hideAssignPlanBar() {
    return {type: HIDE_ASSIGN_PLAN_BAR};
}

export function showAssignClientsList(status) {
    return {type: HIDE_ASSIGN_CLIENTS_LIST, payload: {status}};
}

export function showAddNewClient(status) {
    return {type: HIDE_ADD_CLIENT_MODAL, payload: {status}};
}

export function showAssignTemplate(status) {
    return {type: HIDE_ASSIGN_TEMPLATE, payload: {status}};
}

export function setAssignClientsData(clientsData) {
    return {type: SET_ASSIGN_CLIENT_DATA, payload: {clientsData}};
}

export function handleAssignClients(clients) {
    return (dispatch, getState) => {
        const {workoutId} = getState().global;
        fetch(APPLY_WORKOUT_TEMPLATE_TO_CLIENTS(workoutId), {
            method: 'post',
            credentials: 'include',
            body: JSON.stringify({clientsIds: clients.map(client => client.id)})
        }).then(response => response.json()).then(response => {
            mixpanel.track("Assigned workout template to client");
            dispatch({type: SET_ASSIGN_CLIENT_DATA, payload: {clientsData: response}});
            dispatch({type: HIDE_ASSIGN_TEMPLATE, payload: {status: false}});
            dispatch({type: HIDE_ASSIGN_TEMPLATE, payload: {status: true}});
        });
    }
}

//Shared
function createWorkout(exercise, order, isSup, rest = workoutStatsDefaultValues['rest']) {
    return {
        order,
        comment: null,
        info: null,
        time: null,
        reps: 12,
        sets: 3,
        rest: rest,
        rm: null,
        tempo: null,
        startWeight: null,
        supers: isSup ? null : [],
        type: exercise.workoutType,
        exercise: {
            equipment: exercise.equipment,
            id: exercise.id,
            muscle: exercise.muscleGroup,
            name: exercise.name,
            picture: exercise.picture_url,
            type: exercise.exerciseType,
            video: exercise.video_url
        }
    }
}

function updateDayData(dispatch, dayId, data, isTemplate) {
    const url = SAVE_WORKOUT_DAY_PLAN(dayId);
    return fetch(url, {
        method: 'post',
        credentials: 'include',
        body: JSON.stringify(data)
    }).then(() => {
        dispatch({type: DISABLE_DAY_DATA_UPDATING})
    });
}

function updateDayExercises(dispatch, dayId, workouts, isTemplate) {
    updateDayData(dispatch, dayId, {exercises: workouts.map(serializeWorkout)}, isTemplate);
}

function updateWorkoutValue(workouts, workoutId, key, value) {
    return workouts.map(workout => ({
        ...workout,
        supers: key !== 'supers' ? workout.supers.map(sWorkout => ({
            ...sWorkout,
            [key]: sWorkout.id === workoutId ? value : sWorkout[key]
        })) : workout.supers,
        [key]: workout.id === workoutId ? value : workout[key]
    }));
}

function updateWorkoutSuperSetSetsStat(workouts, selectedWorkout){
    return workouts.map(workout => ({
        ...workout,
        supers: workout.supers.length && selectedWorkout.id === workout.supers[workout.supers.length - 1].id ?
            workout.supers.map(sWorkout => {
                if(sWorkout.id !== selectedWorkout.id) {
                    return {
                        ...sWorkout,
                        sets: selectedWorkout.sets
                    }
                }
                return sWorkout;
            }) : workout.supers,
        sets: workout.supers.length && selectedWorkout.id === workout.supers[workout.supers.length - 1].id ?
            selectedWorkout.sets : workout.sets
    }));
}

function serializeWorkout(workout) {
    return {
        comment: workout.comment,
        id: workout.exercise.id,
        order: workout.order,
        reps: workout.reps,
        rest: workout.rest || 0,
        sets: workout.sets,
        tempo: workout.tempo,
        start_weight: workout.startWeight,
        rm: workout.rm,
        superset: workout.supers && workout.supers.map(supWorkout => serializeWorkout(supWorkout)),
        workout_id: workout.id
    };
}
