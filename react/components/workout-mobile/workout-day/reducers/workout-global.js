/*jshint esversion: 6 */
import {SET_TOTAL_ACTIVE_CLIENTS} from '../constants';

const WListElement = document.getElementById('workout-day-container');

const INITIAL_STATE = {
    dayId: WListElement.getAttribute('data-day-id'),
    dayName: WListElement.getAttribute('data-workout-plan-name'),
    workoutPlanName: WListElement.getAttribute('data-day-name'),
    clientId: WListElement.getAttribute('data-client-id'),
    isTemplate: !WListElement.getAttribute('data-client-id'),
    workoutId: WListElement.getAttribute('data-workout-id'),
    url: WListElement.getAttribute('data-url'),
    muscles: JSON.parse(WListElement.getAttribute('data-muscles')),
    equipments: JSON.parse(WListElement.getAttribute('data-equipments')),
    btnClass: WListElement.getAttribute('data-btn-class'),
    totalActiveClients: WListElement.getAttribute('data-total-active-clients')
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {

        case SET_TOTAL_ACTIVE_CLIENTS:
            return { ...state, totalActiveClients: 1};

        default:
            return state;
    }
}
