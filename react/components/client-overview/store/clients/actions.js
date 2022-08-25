import axios from 'axios';
import http from '../http';
import _ from 'lodash';
import {fetchClientProgress} from '../progress/actions';

import {
    FILTER_COUNT_FETCH,
    CLIENTS_FETCH,
    CHANGE_FILTER,
    CHANGE_SEARCH_QUERY,
    CHANGE_ACTIVE_FILTER,
    DELETE_CLIENT,
    DEACTIVATE_CLIENT,
    ACTIVATE_CLIENT,
    CHANGE_SORT,
    IGNORE_CLIENT_STATUS,
    SELECTED_CLIENT_COUNT,
    SELECTED_ALL_CLIENTS,
    CLIENT_STATUS_UPDATE,
    DELETE_SELECTED_CLIENTS,
    UPDATE_UNREAD_MESSAGE,
    CLIENT_TAG_FILTER,
    TOOLTIP_HANDLE,
    TASK_ADD_MODAL_OPEN,
    ADD_NEW_TASK,
    RESOLVE_REMINDER,
    SUBSCRIPTION_MODAL_OPEN,
    UPDATE_CLIENT_PAYMENT,
    DEACTIVATE_SELECTED_CLIENT,
    EXTEND_CLIENT_MODAL_OPEN,
    EXTEND_CLIENT_DURATION,
    CLIENT_ACTIVE_STATUS_UPDATE,
    CLIENT_UPDATE_DURATION,
    ADD_NEW_CLIENT,
    OPEN_SIDE_CONTENT,
    SEND_EMAIL,
    SUBSCRIPTION_UPDATE,
    MULTI_SEND_MESSAGE,
    MESSAGE_COUNT_UPDATE,
    MEDIA_TEMPLATE_MODAL_OPEN,
    MEAL_PLAN_MODAL_OPEN,
    GET_CLIENT_VIDEOS,
    GET_CLIENT_DOCS,
    UNLOAD_MEAL_PLAN,
    GENERATE_MEAL_PLAN,
    GET_CLIENT_PAYMENTS_LOG,
    UPDATE_CLIENT_PAYMENT_LOG,
    GET_CLIENT_IMAGES,
    SET_SELECTED_CLIENT,
    ADD_CLIENT_VIDEO,
    DELETE_CLIENT_VIDEO,
    ADD_CLIENT_DOC,
    DELETE_CLIENT_DOC,
    FETCH_MORE_CLIENT_IMAGES,
    UPDATE_CLIENT_DATA,
    ACTIVATE_SELECTED_CLIENT,
    ADD_CLIENT_DOC_LIBRARY,
    GET_CLIENT_KCALS
} from './types';
import {
    FETCH_CLIENTS,
    FETCH_CLIENTS_DETAIL,
    FETCH_TAGS_LIST,
    GET_CLIENT_FILTER_COUNTS,
    ADD_NEW_CLIENT as ADD_NEW_CLIENT_URL,
    DELETE_CLIENT as DELETE_CLIENT_URL,
    DEACTIVATE_CLIENT as DEACTIVATE_CLIENT_URL,
    ACTIVATE_CLIENT as ACTIVATE_CLIENT_URL,
    IGNORE_STATUS,
    DELETE_SELECTED_CLIENTS as DELETE_SELECTED_CLIENTS_URL,
    UNSUBSCRIBE_CLIENT as UNSUBSCRIBE_CLIENT_URL,
    ADD_NEW_TASK as ADD_NEW_TASK_URL,
    RESOLVE_REMINDER as RESOLVE_REMINDER_URL,
    DEACTIVATE_SELECTED_CLIENTS as DEACTIVATE_SELECTED_CLIENTS_URL,
    CLIENT_STATUS_UPDATE as CLIENT_STATUS_UPDATE_URL,
    CLIENT_DURATION_UPDATE,
    PAUSE_SUBSCRIPTION as PAUSE_SUBSCRIPTION_URL,
    REFUND_CLIENT as REFUND_CLIENT_URL,
    UPDATE_CLIENT_INFO as UPDATE_CLIENT_INFO_URL,
    FETCH_CLIENT_DOCS as FETCH_CLIENTS_DOCS_URL,
    FETCH_CLIENT_VIDEOS as FETCH_CLIENTS_VIDEOS_URL,
    FETCH_CLIENT_PAYMENTS_LOG as FETCH_CLIENT_PAYMENTS_LOG_URL,
    FETCH_CLIENT_IMAGES as FETCH_CLIENT_IMAGES_URL,
    GENERATE_MEAL_PLAN as GENERATE_MEAL_PLAN_URL,
    ADD_CLIENT_VIDEO as ADD_CLIENT_VIDEO_URL,
    GET_CLIENT_KCALS_URL,
    ADD_CLIENT_DOC as ADD_CLIENT_DOC_URL,
    REMOVE_CLIENT_VIDEO, REMOVE_CLIENT_DOC,
    FETCH_VIDEOS,
    FETCH_DOCUMENTS, POST_CLIENT_DOC_LIBRARY
} from "../../../../api/clients";

import {
    SEND_EMAIL as SEND_EMAIL_URL,
    MULTIPLE_MESSAGE_SEND,
    MARK_MESSAGES_READ,
    SET_CLIENT_SETTINGS
} from '../../../../api';

import {CLIENTS_LIST_FETCH_LIMIT, CLIENT_NOTES_FIELDS, CLIENT_INFO_FIELDS} from "../../const";
import {mergeValues} from "../../helpers";
import {GetCMFromFeetInches, GetFeetInchesFromCM} from "../../../../shared/helper/measurementHelper";

toastr.options = {
    'preventDuplicates': true,
    'preventOpenDuplicates': true
};

let filterCountRequestCancelToken = null;
export const fetchFilterCount = () => {
    return (dispatch, getState) => {
        dispatch({type: FILTER_COUNT_FETCH.REQUEST});

        if (filterCountRequestCancelToken !== null) {
            filterCountRequestCancelToken.cancel()
        }

        filterCountRequestCancelToken = axios.CancelToken.source()

        let tags, q = [];
        let params = new URLSearchParams(location.search);
        if (params.get('tag')) {
            tags = params.get('tag').split(',');
        }
        if (params.get('q')) {
            q = params.get('q');
        }

        const currentState = getState();
        const requestData = {
            status: (currentState.clients.isActiveFilter) ? 'active' : 'inactive',
            limit: CLIENTS_LIST_FETCH_LIMIT,
            filter: currentState.clients.filterProperty,
            q,
            tags
        };

        axios.get(GET_CLIENT_FILTER_COUNTS(), {params: requestData, cancelToken: filterCountRequestCancelToken.token})
            .then(({data}) => {
                dispatch({type: FILTER_COUNT_FETCH.SUCCESS, payload: data});
            })
            .catch(function (exception) {
                if (!axios.isCancel(exception)) {
                    throw exception;
                }
            });
    };
};

export const fetchClients = (startFrom = null) => {
    return (dispatch, getState) => {
        dispatch({type: CLIENTS_FETCH.REQUEST});

        let tags, q = [];
        let params = new URLSearchParams(location.search);
        if (params.get('tag')) {
            tags = params.get('tag').split(',');
        }
        if (params.get('q')) {
            q = params.get('q');
        }

        const currentState = getState();
        const offset = (startFrom !== null) ? startFrom : currentState.clients.listOffset;
        const requestData = {
            status: (currentState.clients.isActiveFilter) ? 'active' : 'inactive',
            offset,
            limit: CLIENTS_LIST_FETCH_LIMIT,
            filter: currentState.clients.filterProperty,
            q,
            tags,
            sortColumn: currentState.clients.sortColumn,
            sortOrder: currentState.clients.sortOrder,
        };
        return axios.get(FETCH_CLIENTS(), {params: requestData})
            .then(({data}) => {
                let currentClients = (offset > 0) ? currentState.clients.clients : [];
                dispatch({type: CLIENTS_FETCH.SUCCESS, payload: [...currentClients, ...data]});
            });
    };
};


export const handleSelectClient = (client) => {
    return (dispatch, getState) => {
        dispatch({type: SET_SELECTED_CLIENT, payload: client});
        dispatch(fetchClientPaymentsLog(client.id));
        dispatch(fetchClientImages(client.id));
        dispatch(fetchClientProgress(client.id));
    };
};

export const changeFilterProperty = (filterKey) => {
    return {type: CHANGE_FILTER, payload: filterKey};
};

export const changeSearchQuery = (query) => {
    return {type: CHANGE_SEARCH_QUERY, payload: query};
};

export const changeTagFilter = (query) => {
    return {type: CLIENT_TAG_FILTER, payload: query};
};

export const changeActiveFilter = (isActive) => {
    return {type: CHANGE_ACTIVE_FILTER, payload: isActive};
};

export const changeClientSort = (column, order) => {
    return {type: CHANGE_SORT, payload: {column, order}};
};

export const addClient = (client) => {
    return (dispatch, getState) => {
        return axios.post(ADD_NEW_CLIENT_URL(), client)
            .then(res => {
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
    };
}

export const clientUpdate = (bodyData) => {
    return (dispatch, getState) => {
        var form_data = new FormData();
        for (var key in bodyData) {
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

export const addClientModalClose = () => {
    return (dispatch, getState) => {
        dispatch(fetchFilterCount());
        dispatch(fetchClients());
    };
}

export const deleteClientAction = (clientId) => {
    return (dispatch, getState) => {
        dispatch({type: DELETE_CLIENT.REQUEST});
        const requestData = {
            "clients": [clientId]
        }
        axios.post(DELETE_SELECTED_CLIENTS_URL(), null, {params: requestData}).then(res => {
            dispatch({type: DELETE_SELECTED_CLIENTS.SUCCESS, payload: clients})
        })
    };
};

export const deactivateClientAction = (clientId, disableAccess) => {
    return (dispatch, getState) => {
        dispatch({type: DEACTIVATE_CLIENT.REQUEST});
        let requestBody = new FormData();
        requestBody.append('disableAccessApp', disableAccess);
        axios.post(DEACTIVATE_SELECTED_CLIENTS_URL(clientId), requestBody)
            .then(res => {
                dispatch({type: DEACTIVATE_CLIENT.SUCCESS, payload: clientId});
                dispatch(fetchFilterCount());
            })
    };
};

export const activateClientAction = (clientId) => {
    return (dispatch, getState) => {
        dispatch({type: ACTIVATE_CLIENT.REQUEST});

        axios.get(ACTIVATE_CLIENT_URL(clientId))
            .then(res => {
                dispatch({type: ACTIVATE_CLIENT.SUCCESS, payload: clientId});
                dispatch(fetchFilterCount());
            })
    };
};

export const ignoreStatus = (id, clientId, eventName) => {
    const data = new FormData();
    data.append('id', id)
    return (dispatch, getState) => {
        axios.post(IGNORE_STATUS(id), data).then(res => {
            const payloadData = {
                id: clientId,
                status: res.data.status,
                eventName: eventName,
                statusId: id
            }
            dispatch({type: IGNORE_CLIENT_STATUS, payload: payloadData});
        })
    }
}

export const selectClient = (client) => {
    return (dispatch, getState) => {
        dispatch({type: SELECTED_CLIENT_COUNT, payload: client});
    }
}

export const selectAllClients = () => {
    return (dispatch, getState) => {
        dispatch({type: SELECTED_ALL_CLIENTS});
    }
}

export const clientStatusUpdate = (responseData) => {
    return (dispatch, getState) => {
        dispatch({type: CLIENT_STATUS_UPDATE, payload: responseData});
    }
}

export const updateMessageCount = (responseData) => {
    return (dispatch, getState) => {
        const currectStateData = getState()
        dispatch({type: MESSAGE_COUNT_UPDATE, payload: responseData});
        if (currectStateData.clients.filterCounts.unanswered !== 0) {
            dispatch(fetchFilterCount());
        }
    }
}

export const unsubscribe = (client) => {
    return (dispatch, getState) => {
        let bodyData = new FormData();
        bodyData.append('client', client);
        http.post(UNSUBSCRIBE_CLIENT_URL(), bodyData).then(({data}) => {
            dispatch({type: SUBSCRIPTION_UPDATE.SUCCESS, payload: Object.assign(data, {type: 'unsubscribe'})})
        }).catch(err => {
            dispatch({type: SUBSCRIPTION_UPDATE.FAILURE});
        });
    }
}

export const pauseSubscription = (client, trialEnd, pause) => {
    return (dispatch, getState) => {
        let bodyData = new FormData();
        bodyData.append('client', client);
        bodyData.append('trialEnd', trialEnd);
        if (pause) {
            bodyData.append('pause', pause);
        }
        http.post(PAUSE_SUBSCRIPTION_URL(), bodyData).then(({data}) => {
            dispatch({
                type: SUBSCRIPTION_UPDATE.SUCCESS,
                payload: Object.assign(data, {type: pause ? 'pause' : 'adjust'})
            })
            toastr.success('Subscription was updated.', 'Success', {timeOut: 5000});
        }).catch(err => {
            dispatch({type: SUBSCRIPTION_UPDATE.FAILURE});
        });
    }
}

export const refundClient = (client, chargeId) => {
    return (dispatch, getState) => {
        const {clientPaymentsLog} = getState().clients;
        const currentPaymentLog = clientPaymentsLog.find(clientPaymentLog => clientPaymentLog.charge === chargeId);
        const newRefundPaymentLog = {
            id: currentPaymentLog.id + 50,
            type: "charge.refunded",
            customer: currentPaymentLog.customer,
            charge: currentPaymentLog.charge,
            amount: currentPaymentLog.amount,
            currency: currentPaymentLog.currency,
            createdAt: {
                date: moment(),
                timezone_type: currentPaymentLog.timezone_type,
                timezone: currentPaymentLog.timezone
            },
            arrivalDate: null
        }

        let bodyData = new FormData();
        bodyData.append('client', client);
        bodyData.append('chargeId', chargeId);
        http.post(REFUND_CLIENT_URL(), bodyData).then(({data}) => {
            toastr.success('Refund successful.', 'Success', {timeOut: 5000});
            dispatch({type: UPDATE_CLIENT_PAYMENT_LOG, payload: newRefundPaymentLog});
        }).catch(err => {
            toastr.error('An error occurred. Please try again later.', 'Error', {timeOut: 5000});
        });
    }
}

export const deleteSelectedClients = (clients) => {
    return (dispatch, getState) => {
        dispatch({type: DELETE_SELECTED_CLIENTS.REQUEST});
        const requestData = {
            "clients": clients
        }
        axios.post(DELETE_SELECTED_CLIENTS_URL(), null, {params: requestData}).then(res => {
            dispatch({type: DELETE_SELECTED_CLIENTS.SUCCESS, payload: clients})
        });
    }
}

export const deactivateSelectedClients = (clients) => {
    return (dispatch, getState) => {
        dispatch({type: DEACTIVATE_SELECTED_CLIENT.REQUEST})
        const requestData = {
            "clients": clients,
            "status": "deactivate"
        }
        axios.post(CLIENT_STATUS_UPDATE_URL(), null, {params: requestData}).then(res => {
            dispatch({type: DEACTIVATE_SELECTED_CLIENT.SUCCESS, payload: clients})
        })
    }
}
export const activateSelectedClients = (clients) => {
    return (dispatch, getState) => {
        dispatch({type: ACTIVATE_SELECTED_CLIENT.REQUEST})
        const requestData = {
            "clients": clients,
            "status": "activate"
        }
        axios.post(CLIENT_STATUS_UPDATE_URL(), null, {params: requestData}).then(res => {
            dispatch({type: ACTIVATE_SELECTED_CLIENT.SUCCESS, payload: clients})
        })
    }
}

export const unreadMessageUpdate = (responsData) => {
    return (dispatch, getState) => {
        axios.post(MARK_MESSAGES_READ(responsData), {}).then(({data}) => {
            dispatch({type: UPDATE_UNREAD_MESSAGE, payload: responsData});
        });
    }
}

export const handleTooltip = (value) => {
    return (dispatch, getState) => {
        dispatch({type: TOOLTIP_HANDLE, payload: value});
    }
}

export const handleTaskModal = (open, id) => {
    return (dispatch, getState) => {
        dispatch({type: TASK_ADD_MODAL_OPEN, payload: {open: open, id: id}});
    }
}

export const handleMealPlanModal = (open, id) => {
    return (dispatch, getState) => {
        dispatch({type: MEAL_PLAN_MODAL_OPEN, payload: {open: open, id: id}});
    }
}

export const addNewTask = (data) => {
    return (dispatch, getState) => {
        dispatch({type: ADD_NEW_TASK.REQUEST});
        axios.post(ADD_NEW_TASK_URL(), null, {params: data}).then(({data}) => {
            dispatch({type: ADD_NEW_TASK.SUCCESS, payload: data});
        })
            .catch(err => {
                dispatch({type: ADD_NEW_TASK.FAILURE, payload: err});
            });
    }
}

export const resolveReminder = (id, clientId) => {
    const data = new FormData();
    data.append('id', id)
    return (dispatch, getState) => {
        dispatch({type: RESOLVE_REMINDER.REQUEST});
        axios.post(RESOLVE_REMINDER_URL(), data).then(({data}) => {
            dispatch({type: RESOLVE_REMINDER.SUCCESS, payload: {id: id, clientId: clientId}});
        }).catch(err => {
            dispatch({type: RESOLVE_REMINDER.FAILURE, payload: err});
        });
    }
}

export const handleSubscriptionModal = (open, client) => {
    return (dispatch, getState) => {
        dispatch({type: SUBSCRIPTION_MODAL_OPEN, payload: {open: open, client: client}});
    }
}

export const clientPaymentUpdate = (bodyData) => {
    return (dispatch, getState) => {
        dispatch({type: UPDATE_CLIENT_PAYMENT.REQUEST});
        var form_data = new FormData();
        for (var key in bodyData) {
            form_data.append(key, bodyData[key]);
        }
        return axios.post(SET_CLIENT_SETTINGS(), form_data).then(({data}) => {
            dispatch({type: UPDATE_CLIENT_PAYMENT.SUCCESS, payload: data});
            return data
        })
            .catch(err => {
                dispatch({type: UPDATE_CLIENT_PAYMENT.FAILURE, payload: err.response.data.error});
            });
    }
}

export const handleExtendModal = (value, client, id) => {
    return (dispatch, getState) => {
        dispatch({type: EXTEND_CLIENT_MODAL_OPEN, payload: {open: value, client: client, statusId: id}})
    }
}

export const extendDuration = (bodyData, client) => {
    return (dispatch, getState) => {
        axios.post(CLIENT_DURATION_UPDATE(client), bodyData).then(({data}) => {
            const {duration, endDate, startDate} = data.client;
            dispatch({
                type: CLIENT_UPDATE_DURATION,
                payload: {client: client, duration: duration, endDate: endDate, startDate: startDate}
            });
            dispatch({type: EXTEND_CLIENT_DURATION});
        })
            .catch(err => {
                dispatch({type: UPDATE_CLIENT_PAYMENT.FAILURE, payload: err});
            });
    }
}

export const clientActiveStatusUpdate = (id) => {
    return (dispatch, getState) => {
        dispatch({type: CLIENT_ACTIVE_STATUS_UPDATE.REQUEST});
        const requestData = {
            "clients": id,
            "status": "activate"
        }

        http.post(CLIENT_STATUS_UPDATE_URL(), null, {params: requestData}).then(({data}) => {
            dispatch({type: CLIENT_ACTIVE_STATUS_UPDATE.SUCCESS});
        })
            .catch(err => {
                dispatch({type: CLIENT_ACTIVE_STATUS_UPDATE.FAILURE, payload: err});
            });
    }
}

export const clientDurationUpdate = (duration, client, type) => {
    return (dispatch, getState) => {
        let bodyData = new FormData();
        bodyData.append([type], duration);
        return axios.post(CLIENT_DURATION_UPDATE(client), bodyData).then(({data}) => {
            const {duration, endDate, startDate} = data.client;
            dispatch({
                type: CLIENT_UPDATE_DURATION,
                payload: {client: client, duration: duration, endDate: endDate, startDate: startDate}
            });
            return data;
        })
    }
}

export const openSideContent = (open, client, messageType, templateType) => {
    return (dispatch, getState) => {
        dispatch({
            type: OPEN_SIDE_CONTENT,
            payload: {open: open, messageType: messageType, templateType: templateType, client: client}
        });
    }
}

export const closeSideContent = () => {
    return (dispatch, getState) => {
        setTimeout(() => {
            dispatch({type: OPEN_SIDE_CONTENT, payload: {open: false, messageType: '', templateType: '', client: {}}});
        }, 900)
    }
}

export const sendEmail = (bodyData) => {
    return (dispatch, getState) => {
        var form_data = new FormData();
        for (var key in bodyData) {
            form_data.append(key, bodyData[key]);
        }
        return axios.post(SEND_EMAIL_URL(), form_data).then(({data}) => {
            setTimeout(() => {
                dispatch({type: SEND_EMAIL.SUCCESS, payload: {sentMail: true}});
            }, 900)
            return data;
        })
    }
}

export const multiSendMessage = (userId, clients, msg) => {
    return (dispatch, getState) => {
        const bodyData = {
            clients: clients,
            msg: msg
        }
        return axios.post(MULTIPLE_MESSAGE_SEND(userId), bodyData).then(({data}) => {
            dispatch({type: MULTI_SEND_MESSAGE.SUCCESS});
            return data;
        })
    }
}

export const generateMealPlan = (values, clientId) => {
    return (dispatch, getState) => {
        dispatch({type: GENERATE_MEAL_PLAN.REQUEST});
        http.post(GENERATE_MEAL_PLAN_URL(clientId), values).then(({data}) => {
            dispatch({type: GENERATE_MEAL_PLAN.SUCCESS});
        }).catch(({response}) => {
            dispatch({type: GENERATE_MEAL_PLAN.FAILURE, payload: response.data.error});
        });
    }
}

let updateClientInfoRequestCancelToken = null;
const debouncedUpdateClientInfo = _.debounce(updateInfo, 1000);

export const updateClientInfo = (client, bodyData) => {
    return (dispatch, getState) => {
        if (updateClientInfoRequestCancelToken !== null) {
            updateClientInfoRequestCancelToken.cancel()
        }

        if (bodyData['measuringSystem'] == 2) {
            bodyData['info'].height = GetCMFromFeetInches(bodyData['info'].feet, bodyData['info'].inches)
        } else {
            bodyData['info'].feet = GetFeetInchesFromCM(bodyData['info'].height).feet
            bodyData['info'].inches = GetFeetInchesFromCM(bodyData['info'].height).inches
        }

        updateClientInfoRequestCancelToken = axios.CancelToken.source();
        return debouncedUpdateClientInfo(dispatch, getState, client, bodyData);
    }
}

function updateInfo(dispatch, getState, client, body) {
    let clientInfoFields = CLIENT_INFO_FIELDS;
    let clientNotesFields = CLIENT_NOTES_FIELDS;
    mergeValues(clientInfoFields, body);
    mergeValues(clientNotesFields, body);

    const fields = {...clientInfoFields, ...clientNotesFields};
    let requestBody = {
        excludeIngredients: null
    };

    Object.entries(fields).map(item => {
        return Object.assign(requestBody, {[item[0]]: item[1].value})
    });

    //remove endDate from requestBody
    requestBody = _.omit(requestBody, 'endDate');

    http.post(UPDATE_CLIENT_INFO_URL(client), requestBody, {
        cancelToken: updateClientInfoRequestCancelToken.token
    }).then(({data}) => {
        toastr.success('Info has been saved.', 'Saved', {timeOut: 5000});
        dispatch({type: SET_SELECTED_CLIENT, payload: data});
        dispatch({type: UPDATE_CLIENT_DATA, payload: data});
    });
}

export const fetchClientDocs = id => {
    return (dispatch, getState) => {
        dispatch({type: GET_CLIENT_DOCS.REQUEST});
        return http.get(FETCH_CLIENTS_DOCS_URL(id)).then(({data}) => {
            dispatch({type: GET_CLIENT_DOCS.SUCCESS, payload: {docs: data}});
        })
    }
}

export const fetchClientVideos = id => {
    return (dispatch, getState) => {
        dispatch({type: GET_CLIENT_VIDEOS.REQUEST});
        return http.get(FETCH_CLIENTS_VIDEOS_URL(id)).then(({data}) => {
            dispatch({type: GET_CLIENT_VIDEOS.SUCCESS, payload: {videos: data}});
        })
    }
}

export const handleUnloadMealPlan = () => {
    return (dispatch, getState) => {
        dispatch({type: UNLOAD_MEAL_PLAN});
    }
}

export const fetchClientPaymentsLog = id => {
    return (dispatch, getState) => {
        dispatch({type: GET_CLIENT_PAYMENTS_LOG.REQUEST});
        setTimeout(() => {
            return http.get(FETCH_CLIENT_PAYMENTS_LOG_URL(), {params: {client: id}}).then(({data}) => {
                dispatch({type: GET_CLIENT_PAYMENTS_LOG.SUCCESS, payload: {paymentsLog: data}});
            });
        }, 500);
    }
}

export const fetchClientImages = id => {
    return (dispatch, getState) => {
        dispatch({type: GET_CLIENT_IMAGES.REQUEST});
        setTimeout(() => {
            return http.get(FETCH_CLIENT_IMAGES_URL(id)).then(({data}) => {
                dispatch({type: GET_CLIENT_IMAGES.SUCCESS, payload: {images: data}});
            })
        }, 500);
    }
}

export const addClientVideo = (id, clientId) => {
    return (dispatch, getState) => {
        return axios.post(ADD_CLIENT_VIDEO_URL(clientId, id), null).then(({data}) => {
            dispatch({type: ADD_CLIENT_VIDEO.SUCCESS, payload: {clientId: clientId, videoAdded: data}});
        });
    }
}

export const addClientDoc = (id, clientId) => {
    return (dispatch, getState) => {
        return axios.post(ADD_CLIENT_DOC_URL(clientId, id), null).then(({data}) => {
            dispatch({type: ADD_CLIENT_DOC.SUCCESS, payload: {clientId: clientId, docAdded: data}});
        });
    }
}

export const deleteClientVideoAction = (id) => {
    return (dispatch, getState) => {
        dispatch({type: DELETE_CLIENT_VIDEO.REQUEST});
        return http.delete(REMOVE_CLIENT_VIDEO(getState().clients.selectedClient.id, id)).then(({data}) => {
            dispatch({type: DELETE_CLIENT_VIDEO.SUCCESS, payload: id});
        })
    }
}
export const deleteClientDocAction = (id) => {
    return (dispatch, getState) => {
        dispatch({type: DELETE_CLIENT_DOC.REQUEST});
        return http.delete(REMOVE_CLIENT_DOC(getState().clients.selectedClient.id, id)).then(({data}) => {
            dispatch({type: DELETE_CLIENT_DOC.SUCCESS, payload: id});
        })
    }
}

export const fetchMoreClientImagesAction = (id) => {
    return (dispatch, getState) => {
        dispatch({type: FETCH_MORE_CLIENT_IMAGES.REQUEST});
        setTimeout(() => {
            return http.get(FETCH_CLIENT_IMAGES_URL(id), {params: {page: getState().clients.clientImagesPage}}).then(({data}) => {
                dispatch({type: FETCH_MORE_CLIENT_IMAGES.SUCCESS, payload: {images: data}});
            });
        }, 500);
    }
}

export const fetchGraphKcalsAction = (id) => {
    return (dispatch, getState) => {
        dispatch({type: GET_CLIENT_KCALS.REQUEST});
        return http.get(GET_CLIENT_KCALS_URL(getState().clients.selectedClient.id)).then(({data}) => {
            dispatch({type: GET_CLIENT_KCALS.SUCCESS, payload: {kcals: data}});
        });

    }
}

export const handleMediaTemplateModal = (open, id, type, currentMedias) => {
    return (dispatch, getState) => {
        dispatch({type: MEDIA_TEMPLATE_MODAL_OPEN.REQUEST});
        return http.get(
            type === 'video'
                ? FETCH_VIDEOS()
                : FETCH_DOCUMENTS())
            .then(({data}) => {
                    dispatch({
                        type: MEDIA_TEMPLATE_MODAL_OPEN.SUCCESS,
                        payload: {open: open, id: id, type: type, current: currentMedias, media: data.reverse()}
                    });
                }
            )
    }
}

export const postNewClientDocLibraryAction = (formData) => {
    return (dispatch, getState) => {
        dispatch({type: ADD_CLIENT_DOC_LIBRARY.REQUEST});
        return http.post(POST_CLIENT_DOC_LIBRARY(), formData).then(({data}) => {
            dispatch({type: ADD_CLIENT_DOC_LIBRARY.SUCCESS, payload: data});
        })
    }
}

