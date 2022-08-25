import {ADMIN_FETCH_CLIENTS} from "../store";
import client from "../api/base";

export const searchClients = (val) =>{
    return async (dispatch,getState) =>{
        dispatch({type:ADMIN_FETCH_CLIENTS.REQUEST});

        const response = await client.get('/admin/api/clients?q='+val)

        const payload ={
            clients:response.data
        }

        dispatch({type:ADMIN_FETCH_CLIENTS.SUCCESS,payload:payload})
    }
}
