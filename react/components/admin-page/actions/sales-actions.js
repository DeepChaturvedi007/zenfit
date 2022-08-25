import {SALES_STATS_FETCH} from "../store";
import client from "../api/base";


export const fetchSalesAction = (start,end) =>{
    return async (dispatch,getState) =>{
        dispatch({type:SALES_STATS_FETCH.REQUEST});

        const momentStart = moment(start).format("YYYY-MM-DD")
        const momentEnd = moment(end).format("YYYY-MM-DD")

        try {
            const val = {
                start:momentStart !== "Invalid date" ? momentStart : null,
                end:momentEnd !== "Invalid date" ? momentEnd : null
            }
            const responed =  await client.get("/admin/gecko",{params:val});
            const payload ={
                startDate:start,
                endDate:end,
                salesStats:responed.data ? responed.data : getState().salesStats
            }
            dispatch({type:SALES_STATS_FETCH.SUCCESS,payload:payload})

        }catch (e){
            console.log(e)
        }


    }
}


export const changePeriodAction=(val) =>{
    return(dispatch,getState)=>{
        const payloaded ={
            period:val
        }
        dispatch({type:CHANGE_PERIOD_FETCH.SUCCESS,payload:payloaded})
    }
}
