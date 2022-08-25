import axios from 'axios';
import _ from 'lodash';
import {
    LEADS_FETCH,
    LEADS_FETCH_COUNT,
    LEADS_MORE_FETCH,
    LOAD_MORE,
    NEW_READ_CREATE,
    LEAD_UPDATE,
    LEAD_DELETE,
    LEAD_TAGS_FETCH,
    SEARCH_QUERY,
    SEARCH_BY_TAG,
    CHANGE_FILTER
} from './types';

import {
    GET_LEADS,
    DELETE_LEAD,
    ADD_UPDATE_LEAD,
    GET_LEAD_TAGS
} from '../../../../api/leads';

import {
    LOAD_MORE_LIMIT,
    FILTER_LIST
} from '../../const'

import {
  SEND_EMAIL as SEND_EMAIL_URL,
  SET_CLIENT_SETTINGS
} from '../../../../api';

export const fetchLeadTags = () => {
    return (dispatch, getState) => {
        axios.get(GET_LEAD_TAGS()).then(({data}) => {
            const transformedTags = data.tags.map(tag => {
                return {label: tag.title, value: tag.title};
            });

            dispatch({type: LEAD_TAGS_FETCH.SUCCESS, payload: transformedTags});
        })
    }
}

const debouncedFetchLeads = _.debounce(getLeads, 500);

export const fetchLeads = () => {
    return (dispatch, getState) => {
        return debouncedFetchLeads(dispatch, getState);
    }
}

function getLeads(dispatch, getState) {
    dispatch({type: LEADS_FETCH.REQUEST});
    let key = Object.keys(FILTER_LIST).find(k => FILTER_LIST[k].key === getState().leads.activeFilter);
    let status = FILTER_LIST[key].id;

    const requestData = {
        offset: 0,
        limit: getState().leads.limit,
        q: getState().leads.searchQuery,
        tag: getState().leads.searchTag,
        status: status
    }
    axios.get(GET_LEADS(), { params: requestData }).then(({data}) => {
        const payloadData = {
            allLeads: data.all,
            newLeads: data.new,
            noAnswerLeads: data.noAnswer,
            inDialogLeads: data.inDialog,
            wonLeads: data.won,
            lostLeads: data.lost,
            paymentWaitingLeads: data.paymentWaiting,
            getList: !getState().leads.getList
        }
        dispatch({type: LEADS_FETCH.SUCCESS, payload: payloadData});
    })
}


const debouncedFetchLeadsCount = _.debounce(getLeadsCount, 500);

export const fetchLeadsCount = () => {
    return (dispatch, getState) => {
        return debouncedFetchLeadsCount(dispatch, getState);
    }
}

function getLeadsCount(dispatch, getState) {
    dispatch({type: LEADS_FETCH.REQUEST});
    const requestData = {
        offset: 0,
        limit: getState().leads.limit,
        q: getState().leads.searchQuery,
        tag: getState().leads.searchTag,
        count: 1
    }
    axios.get(GET_LEADS(), { params: requestData }).then(({data}) => {
        const payloadData = {
            all: data.all,
            won: data.won,
            noAnswer: data.noAnswer,
            inDialog: data.inDialog,
            lost: data.lost,
            new: data.new,
            paymentWaiting: data.paymentWaiting
        };
        const count = {
          count: payloadData
        }
        dispatch({type: LEADS_FETCH_COUNT.SUCCESS, payload: count});
    })
}

export const loadMore = () => {
    return (dispatch, getState) => {
        dispatch({type: LOAD_MORE});
    }
}
export const fetchLoadMoreLeads = () => {
    return (dispatch, getState) => {
        dispatch({type: LEADS_MORE_FETCH.REQUEST});
        let key = Object.keys(FILTER_LIST).find(k => FILTER_LIST[k].key === getState().leads.activeFilter);
        let status = FILTER_LIST[key].id;
        const requestData = {
            offset: getState().leads.offset,
            limit: LOAD_MORE_LIMIT,
            q: getState().leads.searchQuery,
            tag: getState().leads.searchTag,
            status: status
        }
        axios.get(GET_LEADS(), { params: requestData }).then(({data}) => {
            const payloadData = {
                allLeads: [...getState().leads.allLeads, ...data.all],
                newLeads: [...getState().leads.newLeads, ...data.new],
                inDialogLeads: [...getState().leads.inDialogLeads, ...data.inDialog],
                noAnswerLeads: [...getState().leads.noAnswerLeads, ...data.noAnswer],
                wonLeads: [...getState().leads.wonLeads, ...data.won],
                lostLeads: [...getState().leads.lostLeads, ...data.lost],
                paymentWaitingLeads: [...getState().leads.paymentWaitingLeads, ...data.paymentWaiting],
                getList: !getState().leads.getList,
                offset: getState().leads.offset+LOAD_MORE_LIMIT,
                limit: getState().leads.limit+LOAD_MORE_LIMIT,
            }
            dispatch({type: LEADS_MORE_FETCH.SUCCESS, payload: payloadData});
        })
    }
}

export const createUpdateLead = (data) => {
    return (dispatch, getState) => {
        dispatch({type: LEAD_UPDATE.REQUEST})
        return axios.post(ADD_UPDATE_LEAD(), {}, {params: data}).then(res => {
            dispatch({type: LEAD_UPDATE.SUCCESS, payload: !getState().leads.listUpdate})
            return {
                status: 'success',
                resData: res.data
            }
        })
        .catch(err => {
            dispatch({type: LEAD_UPDATE.FAILURE});
            return {
                status: 'error',
                resData: err.response.data.error
            }
        })
    }
}

export const clientUpdate = (bodyData) => {
    return (dispatch, getState) => {
        var form_data = new FormData();
        for ( var key in bodyData ) {
            form_data.append(key, bodyData[key]);
        }
        return axios.post(SET_CLIENT_SETTINGS(), form_data).then(({data}) => {
            return {
                status: 'success',
                resData: data
            }
        })
        .catch(err => {
            return {
                status: 'error',
                resData: err.response.data.error
            }
        });
    }
}

export const leadDelete = (id) => {
    return (dispatch, getState) => {
        const data = {
            lead: id
        }
        return axios.delete(DELETE_LEAD(), {params: data}).then(res => {
            dispatch({type: LEAD_DELETE.SUCCESS, payload: !getState().leads.listUpdate})
            return {
                status: 'success',
                resData: res.data
            }
        })
        .catch(err => {
            return {
                status: 'error',
                resData: err.response.data.error
            }
        })
    }
}

export const sendEmail = (bodyData) => {
    return (dispatch, getState) => {
        var form_data = new FormData();
        for ( var key in bodyData ) {
            form_data.append(key, bodyData[key]);
        }
        return axios.post(SEND_EMAIL_URL(), form_data).then(({data}) => {
            dispatch({type: LEAD_DELETE.SUCCESS, payload: !getState().leads.listUpdate})
            return data;
        })
    }
}

export const handleUpdateFilter = filter => {
    return (dispatch, getState) => {
        return dispatch({type: CHANGE_FILTER, payload: filter})
    }
};

export const searchLeads = (searchString) => {
    return (dispatch, getState) => {
        return dispatch({type: SEARCH_QUERY, payload: searchString})
    }
}

export const searchByTag = (searchTag) => {
    return (dispatch, getState) => {
        return dispatch({type: SEARCH_BY_TAG, payload: searchTag})
    }
}
