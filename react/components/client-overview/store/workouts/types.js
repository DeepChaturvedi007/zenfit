const REQUEST = 'REQUEST';
const SUCCESS = 'SUCCESS';
const FAILURE = 'FAILURE';

function createRequestTypes(base) {
    return [REQUEST, SUCCESS, FAILURE].reduce((requestType, type) => {
        requestType[type] = `${base}_${type}`;
        return requestType;
    }, {});
}

export const APPLY_WORKOUT_TEMPLATE = createRequestTypes('APPLY_WORKOUT_TEMPLATE');
export const CHANGE_WORKOUT = createRequestTypes('CHANGE_WORKOUT');
export const GET_CLIENT_WORKOUTS = createRequestTypes('GET_CLIENT_WORKOUTS');
export const WORKOUT_TEMPLATE_MODAL_OPEN = 'WORKOUT_TEMPLATE_MODAL_OPEN';
export const DELETE_WORKOUT_PLAN = createRequestTypes('DELETE_WORKOUT_PLAN');
