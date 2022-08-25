import {
    WORKOUT_TEMPLATE_MODAL_OPEN,
    APPLY_WORKOUT_TEMPLATE,
    CHANGE_WORKOUT,
    GET_CLIENT_WORKOUTS,
    DELETE_WORKOUT_PLAN
} from './types';

import produce from 'immer';

export const INITIAL_STATE = {
    isWorkoutTemplateModalOpen: false,
    plans: [],
    plansLoading: false
};

export default function (state = INITIAL_STATE, { type, payload }) {
    switch (type) {
        case WORKOUT_TEMPLATE_MODAL_OPEN: {
            return { ...state, isWorkoutTemplateModalOpen: payload.open };
        }
        case APPLY_WORKOUT_TEMPLATE.SUCCESS: {
            const { clientsIds, plans } = payload;
            return produce(state, draftState => {
                Object.values(plans).map(plan => draftState.plans.unshift(plan))
                draftState.isWorkoutTemplateModalOpen = false;
            });
        }
        case CHANGE_WORKOUT.SUCCESS: {
            const { plan } = payload;
            return produce(state, draftState => {
                const obj = draftState.plans.find(foundPlan => foundPlan.id === plan.id);
                obj.status = plan.status;
                obj.name = plan.name;
            });
        }
        case GET_CLIENT_WORKOUTS.REQUEST: {
            return { ...state, plansLoading: true };
        }
        case GET_CLIENT_WORKOUTS.SUCCESS: {
            return { ...state, plans: payload.plans, plansLoading: false };
        }
        case DELETE_WORKOUT_PLAN.REQUEST: {
            return { ...state, plansLoading: true };
        }
        case DELETE_WORKOUT_PLAN.SUCCESS: {
            return produce(state, draftState => {
                draftState.plans = draftState.plans.filter(plan => plan.id !== payload.id);
                draftState.plansLoading = false;
            });
        }
        default: {
            return state;
        }
    }
};
