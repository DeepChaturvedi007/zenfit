import {
  SETUP
} from './types';

export const setup = (payload) => {
  return {
    type: SETUP,
    payload: {
      plans: payload.plans || [],
      language: payload.language || 'en',
      hash: payload.hash || '',
      background: payload.background || undefined,
      bundle: payload.bundle || undefined
    }
  }
};