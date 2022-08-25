import {
  success,
  error,
  SETUP,
  GET_AFFILIATE_LINK
} from './types';
import { generateAffiliateLink } from "../../api/user";

export const setup = (data) => dispatch => {
  return dispatch({type: SETUP, payload: { data } })
};

export const getAffiliateLink = () => async (dispatch, getState) => {
  const {
    stats: {
      userId
    }
  } = getState();
  dispatch({type: GET_AFFILIATE_LINK});
  return generateAffiliateLink(userId)
    .then(referral => {
      dispatch({
        type: success(GET_AFFILIATE_LINK),
        payload: {
          referral
        }
      });
      return referral;
    })
    .catch(err => {
      dispatch({
        type: error(GET_AFFILIATE_LINK),
        payload: {
          error: err.message
        }
      });
      return [];
    })
};