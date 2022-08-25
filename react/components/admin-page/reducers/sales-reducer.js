import {CHANGE_PERIOD_FETCH, SALES_STATS_FETCH} from "../store";


export const INITIAL_STATE = {
    period:"Mtd",
    startDate:moment().subtract(1,"month").startOf("month").add(20,"days"),
    endDate:moment(),
    salesStats:{}
}


export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SALES_STATS_FETCH.REQUEST: {
            return{
                ...state,
                loading: true,
                ...payload
            }
        }
        case SALES_STATS_FETCH.SUCCESS: {
            return{
                ...state,
                ...payload
            }
        }

        case CHANGE_PERIOD_FETCH.SUCCESS:{
            return {
                ...state,
                period: payload.payloaded
            }
        }
        default:
            return state;
    }
}
