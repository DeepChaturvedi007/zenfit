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
} from "./types";

import {LOAD_MORE_LIMIT} from '../../const'
import produce from 'immer';

export const INITIAL_STATE = {
    pageLoading: true,
    listUpdate: false,
    getList: false,
    allLeads: [],
    noAnswerLeads: [],
    newLeads: [],
    inDialogLeads: [],
    wonLeads: [],
    lostLeads: [],
    paymentWaitingLeads: [],
    searchQuery: '',
    activeFilter: 'all',
    searchTag: '',
    offset: 30,
    limit: 30,
    loadMoreLoading: false,
    submitLoading: false,
    leadTags: [],
    count: {
        all: 0,
        new: 0,
        noAnswer: 0,
        inDialog: 0,
        won: 0,
        lost: 0,
        paymentWaiting: 0
    }
};

export default function (state = INITIAL_STATE, {type, payload}) {
    switch (type) {
        case LEADS_FETCH.SUCCESS: {
            return { ...state, pageLoading: false, ...payload }
        }
        case LEADS_FETCH_COUNT.SUCCESS: {
            return { ...state, ...payload }
        }
        case LEAD_TAGS_FETCH.SUCCESS: {
            return { ...state, leadTags: payload }
        }
        case LEADS_MORE_FETCH.REQUEST:
        case LOAD_MORE:
        {
            return { ...state, loadMoreLoading: true }
        }
        case LEADS_MORE_FETCH.SUCCESS: {
            return { ...state, loadMoreLoading: false, ...payload }
        }
        case LEAD_UPDATE.REQUEST:
        {
            return { ...state, submitLoading: true }
        }
        case NEW_READ_CREATE.SUCCESS:
        case LEAD_UPDATE.SUCCESS:
        case LEAD_UPDATE.FAILURE:
        case LEAD_DELETE.SUCCESS: {
            return { ...state, listUpdate: payload, submitLoading: false }
        }
        case SEARCH_QUERY: {
            return { ...state, searchQuery: payload }
        }
        case SEARCH_BY_TAG: {
            return { ...state, searchTag: payload }
        }
        case CHANGE_FILTER: {
            return { ...state, activeFilter: payload }
        }
        default:
            return state;
    }
}
