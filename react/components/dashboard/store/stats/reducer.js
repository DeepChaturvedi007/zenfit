import _ from 'lodash';
import {
    success,
    error,
    SETUP,
    GET_AFFILIATE_LINK
} from './types';

const INITIAL_STATE = {
    metrics: {
        leads: {},
        clients: {},
        conversion: {},
        successRate: {},
    },
    payments: {
        revenue: {
            chart: {},
            metrics: {},
            currency: 'usd',
            goal: {},
            total: {}
        },
        total: {},
        connected: false,
        stripeConnectUrl: '',
    },
    referral: {
      link: null,
      earnings: null
    }
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case SETUP: {
            return {...state, ...payload.data}
        }
        case GET_AFFILIATE_LINK: {
            let data = _.cloneDeep(state);
            data.referral = null;
            return {...data, error: null};
        }
        case success(GET_AFFILIATE_LINK): {
            let data = _.cloneDeep(state);
            data.referral = payload.referral;
            return {...data};
        }
        case error(GET_AFFILIATE_LINK): {
            return {...state, error: payload.error}
        }
        default:
            return state;
    }
}