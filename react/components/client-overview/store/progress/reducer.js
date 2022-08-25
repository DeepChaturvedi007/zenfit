import {
  GET_CLIENT_PROGRESS, 
  UPDATE_CHECKIN_INFO
} from './types';

import produce from 'immer';

export const INITIAL_STATE = {
  clientProgress: [],
  checkInInfo: {},
  progressLoading: false
};

export default function (state = INITIAL_STATE, { type, payload }) {
    switch (type) {
        case GET_CLIENT_PROGRESS.REQUEST: {
            return { ...state, progressLoading: true };
        }
        case GET_CLIENT_PROGRESS.SUCCESS: {
            return { ...state, clientProgress: payload.progress, progressLoading: false };
        }
        case UPDATE_CHECKIN_INFO: {
            return { ...state, checkInInfo: payload };
        }
        default: {
            return state;
        }
    }
};
