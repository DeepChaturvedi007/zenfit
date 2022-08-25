import {ADMIN_FETCH_CLIENTS} from "../store";


export const INITIAL_STATE = {
    loading:false,
    clients:{}

}

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case ADMIN_FETCH_CLIENTS.REQUEST: {
            return{
                ...state,
                ...payload
            }
        }
        case ADMIN_FETCH_CLIENTS.SUCCESS: {
            return{
                ...state,
                ...payload
            }
        }
        default:
            return state;
    }
}
