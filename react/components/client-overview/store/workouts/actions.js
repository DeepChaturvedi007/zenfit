import axios from 'axios';
import http from '../http';

import {
  WORKOUT_TEMPLATE_MODAL_OPEN,
  GET_CLIENT_WORKOUTS,
  APPLY_WORKOUT_TEMPLATE,
  CHANGE_WORKOUT,
  DELETE_WORKOUT_PLAN,
} from './types';

import {
  APPLY_WORKOUT_TEMPLATE_TO_CLIENTS,
  UPDATE_WORKOUT as UPDATE_WORKOUT_URL,
  GET_WORKOUT_PLANS,
  DELETE_WORKOUT_PLAN as DELETE_WORKOUT_PLAN_URL,
} from '../../../../api/workout-api';
import {CLIENT_WORKOUT_ADD_COUNT, CLIENT_WORKOUT_SUBTRACT_COUNT} from "../clients/types";

export const handleWorkoutTemplateModal = (open, id) => {
    return (dispatch, getState) => {
        dispatch({ type: WORKOUT_TEMPLATE_MODAL_OPEN, payload: { open: open, id: id } });
    }
}

export const addWorkoutTemplate = (id, clients) => {
    return (dispatch, getState) => {
        const bodyData = {
            clientsIds: clients
        }
        return axios.post(APPLY_WORKOUT_TEMPLATE_TO_CLIENTS(id), bodyData).then(({ data }) => {
            dispatch({ type: APPLY_WORKOUT_TEMPLATE.SUCCESS, payload: { clientsIds: clients, plans: data } });
            dispatch({ type: CLIENT_WORKOUT_ADD_COUNT, payload: clients[0]});
            return data;
        })
    }
}

export const updateWorkoutPlanAction = (param, val, id) => {
    return (dispatch, getState) => {
        let requestBody = new FormData();
        requestBody.append(param, val);
        return http.post(UPDATE_WORKOUT_URL(id), requestBody).then(({ data }) => {
            dispatch({ type: CHANGE_WORKOUT.SUCCESS, payload: { plan: data } });
        })
    }
}

export const getWorkoutPlans = id => {
    return (dispatch, getState) => {
        dispatch({ type: GET_CLIENT_WORKOUTS.REQUEST });
        return axios.get(GET_WORKOUT_PLANS(id)).then(({ data }) => {
            dispatch({ type: GET_CLIENT_WORKOUTS.SUCCESS, payload: { plans: data } });
        })
    }
}

export const deleteWorkoutPlanAction = (planId,clientId) => {
    return (dispatch, getState) => {
        dispatch({ type: DELETE_WORKOUT_PLAN.REQUEST });
        return axios.delete(DELETE_WORKOUT_PLAN_URL(planId)).then(({data}) => {
            dispatch({ type: DELETE_WORKOUT_PLAN.SUCCESS, payload: { id: data.id } });
            dispatch({ type: CLIENT_WORKOUT_SUBTRACT_COUNT, payload: clientId });
        })
    }
}
