export const GET_WORKOUT_TEMPLATES = () => {
    return `/api/workout/template/plans`;
};

export const GET_WORKOUT_PLANS = clientId => {
    return `/api/workout/client/plans/${clientId}`;
};

export const WORKOUT_DAYS_OVERVIEW = plan => {
    return `/workout/client/workout/${plan}`;
};

export const GET_WORKOUT_PLAN_DAYS = plan => {
    return `/api/workout/client/${plan}/days`;
};

export const GET_WORKOUT_PLAN_DAY = (dayId) => {
    return `/api/workout/client/plan/day/${dayId}`;
};

export const GET_WORKOUT_PLAN_DAY_URL = plan => {
    return `/dashboard/client/workout/${plan}`;
};

export const GET_WORKOUT_TEMPLATE_DAY = dayId => {
    return `/api/workout/templates/day/${dayId}`;
};

export const DELETE_PLAN = plan => {
    return `/workout/deletePlan/${plan}`;
};

export const APPLY_WORKOUT_TEMPLATES_TO_CLIENT = clientId => {
    return `/api/workout/templates/assign-templates/${clientId}`;
};

export const APPLY_WORKOUT_TEMPLATE_TO_CLIENTS = template => {
    return `/api/workout/client/assign-plan/${template}`;
};

export const SAVE_WORKOUT_PLAN = plan => {
    return `/api/workout/client/save-workout/${plan}`;
};

export const SAVE_WORKOUT_DAY_PLAN = plan => {
    return `/api/workout/client/save-workout-day/${plan}`;
};

export const GET_EXERCISES = (q, page, muscleId, equipmentId) => {
    return `/api/exercises?q=${q}&page=${page}&equipmentId=${equipmentId}&muscleId=${muscleId}`;
};

export const GET_TRACK_WEIGHT_HISTORY = workoutId => {
    return `/api/workout/mobile/getWeightLiftedHistory?workout=${workoutId}`;
};

export const GET_NEW_WEIGHT_LIFTED = workoutId => {
    return `/api/workout/mobile/createWeightLifted/${workoutId}`;
};

export const POST_WEIGHT_LIFTED = () => {
    return `/api/workout/mobile/insertWeightLifted`;
};

export const CREATE_WORKOUT_PLAN = (dayId, urlParentId) => {
    return `/api/workout/mobile/createWorkouts/${dayId}${urlParentId}`;
};

export const DELETE_WORKOUT_PLAN = (workoutPlanId) => {
    return `/workout/${workoutPlanId}/delete`;
}

export const CREATE_WORKOUT_TEMPLATE = (dayId, urlParentId) => {
    return `/react-api/createWorkoutTemplates/${dayId}${urlParentId}`;
};

export const DELETE_WORKOUT_TEMPLATE = plan => {
    return `/workout/deleteTemplate/${plan}`;
};

export const CREATE_PLAN = () => {
    return `/workout`;
};

export const EDIT_WORKOUT_DAY = plan => {
    return `/workout/${plan}/update`;
};

export const UPDATE_WORKOUT = plan => {
    return `/workout/${plan}/update`;
};

export const DUPLICTE_WORKOUT_DAY_TEMPLATE = plan => {
    return `/dashboard/cloneTemplatePlan/${plan}`;
};
