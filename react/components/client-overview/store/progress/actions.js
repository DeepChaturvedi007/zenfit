import http from '../http';

import {
	GET_CLIENT_PROGRESS,
	UPDATE_CHECKIN_INFO
} from './types';

import {
	FETCH_CLIENT_PROGRESS as FETCH_CLIENT_PROGRESS_URL
} from '../../../../api/clients';

export const fetchClientProgress = id => {
	return (dispatch, getState) => {
		dispatch({ type: GET_CLIENT_PROGRESS.REQUEST });
		setTimeout(() => {
			return http.get(FETCH_CLIENT_PROGRESS_URL(id)).then(({ data }) => {
				const progressArray = data.checkIns && Object.values(data.checkIns).filter((item) => {
					return item[0].checkIns !== null
				})
				const progressData = progressArray && progressArray.length === 0 ? {} : progressArray[progressArray.length - 1][0].checkIns[progressArray[progressArray.length - 1][0].checkIns.length - 1]
				dispatch({ type: GET_CLIENT_PROGRESS.SUCCESS, payload: { progress: data } });
				dispatch({ type: UPDATE_CHECKIN_INFO, payload: progressData });
			})
		}, 500);
	}
}

export const updateCheckInInfo = (data) => {
	return (dispatch, getState) => {
		dispatch({ type: UPDATE_CHECKIN_INFO, payload: data })
	}
}
