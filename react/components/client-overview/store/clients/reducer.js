import {
    CHANGE_ACTIVE_FILTER,
    CHANGE_FILTER,
    CHANGE_SEARCH_QUERY,
    CLIENTS_FETCH,
    ADD_NEW_CLIENT,
    DELETE_CLIENT,
    FILTER_COUNT_FETCH,
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
    MEAL_PLAN_MODAL_OPEN,
    ADD_NEW_TASK,
    RESOLVE_REMINDER,
    SUBSCRIPTION_MODAL_OPEN,
    UPDATE_CLIENT_PAYMENT,
    DEACTIVATE_SELECTED_CLIENT,
    EXTEND_CLIENT_MODAL_OPEN,
    EXTEND_CLIENT_DURATION,
    CLIENT_ACTIVE_STATUS_UPDATE,
    CLIENT_UPDATE_DURATION,
    OPEN_SIDE_CONTENT,
    SEND_EMAIL,
    SUBSCRIPTION_UPDATE,
    MULTI_SEND_MESSAGE,
    MESSAGE_COUNT_UPDATE,
    MEDIA_TEMPLATE_MODAL_OPEN,
    GET_CLIENT_VIDEOS,
    UNLOAD_MEAL_PLAN,
    GET_CLIENT_DOCS,
    GET_CLIENT_PAYMENTS_LOG,
    UPDATE_CLIENT_PAYMENT_LOG,
    GET_CLIENT_IMAGES,
    SET_SELECTED_CLIENT,
    GENERATE_MEAL_PLAN,
    ADD_CLIENT_VIDEO,
    ADD_CLIENT_DOC,
    CLIENT_WORKOUT_ADD_COUNT,
    DELETE_CLIENT_VIDEO, DELETE_CLIENT_DOC,
    CLIENT_WORKOUT_SUBTRACT_COUNT,
    FETCH_MORE_CLIENT_IMAGES,
    UPDATE_CLIENT_DATA,
    ACTIVATE_SELECTED_CLIENT,
    ADD_CLIENT_DOC_LIBRARY,
    GET_CLIENT_KCALS
} from "./types";

import produce from 'immer';

export const INITIAL_STATE = {
    selectedClient: null,
    filterCounts: {},
    isActiveFilter: true,
    clients: [],
    clientDocs: [],
    libraryMedia: [],
    mediaLibraryLoading: false,
    clientVideos: [],
    clientPaymentsLog: [],
    clientImages: [],
    clientImagesPage: 2,
    clientImagesMax: false,
    docsLoading: false,
    videosLoading: false,
    mealPlanModalLoading: false,
    paymentslogLoading: false,
    imagesLoading: false,
    filterProperty: 'all',
    listTotalCount: 0,
    listOffset: 0,
    isInfiniteListLoading: false,
    isListReloading: true,
    isClientDetailLoading: false,
    isClientDetailLoaded: false,
    searchQuery: '',
    tagFilter: [],
    userId: null,
    sortColumn: 'default',
    sortOrder: 'default',
    selectedClients: [],
    selectedClientsDelete: false,
    clientAdding: false,
    clientAddError: '',
    isDeleting: false,
    isTooltipShow: false,
    isTaskModalOpen: false,
    isMealPlanModalOpen: false,
    mealPlanModalError: null,
    loadMealPlansFor: null,
    reloadForUpdate: false,
    isMediaTemplateModalOpen: false,
    mediaTemplateModalType: null,
    clientIdTask: 0,
    statusUpdateFlg: false,
    statusId: '',
    addTaskFlg: false,
    isSubModal: false,
    clientSub: {},
    isPaymentUpdating: false,
    paymentError: '',
    isExtendModalOpen: false,
    openSideContent: false,
    sideContentId: '',
    sideContentMsgType: '',
    sideContentTmpType: '',
    sideContentEmail: '',
    sideContentClient: {}
};

export default function (state = INITIAL_STATE, { type, payload }) {
    switch (type) {
        case SET_SELECTED_CLIENT: {
            return { ...state, selectedClient: payload, clientImagesMax: false, clientImagesPage: 2 }
        }
        case UPDATE_CLIENT_DATA: {
            return produce(state, draftState => {
                const objIndex = state.clients.findIndex(client => client.id === payload.id);
                const selectedClientData = state.clients[objIndex];
                Object.keys(selectedClientData).map(key => {
                    if (payload[key] !== undefined) {
                        draftState.clients[objIndex][key] = payload[key];
                    }
                })
            });
        }
        case FILTER_COUNT_FETCH.SUCCESS: {
            const currentProperty = state.filterProperty;
            const listTotalCount = payload[currentProperty] || 0;

            return { ...state, filterCounts: payload, listTotalCount: listTotalCount, isInfiniteListLoading: false };
        }
        case CLIENTS_FETCH.REQUEST: {
            return { ...state, isInfiniteListLoading: true };
        }
        case CLIENTS_FETCH.ERROR: {
            return { ...state, isInfiniteListLoading: false, isListReloading: false };
        }
        case CLIENTS_FETCH.SUCCESS: {
            return { ...state, clients: payload, selectedClients: [], isInfiniteListLoading: false, isListReloading: false, listOffset: payload.length };
        }
        case CHANGE_FILTER: {
            return produce(state, draftState => {
                if (payload === 'all') {
                    draftState.sortColumn = 'name';
                    draftState.sortOrder = 'ASC';
                } else if (payload === 'missing-checkin' || payload === 'progress') {
                    draftState.sortColumn = 'checkin_day'
                    draftState.sortOrder = payload === 'missing-checkin' ? 'ASC' : 'DESC';
                } else if (payload === 'unanswered') {
                    draftState.sortColumn = 'messages';
                    draftState.sortOrder = 'ASC';
                } else if (payload === 'ending') {
                    draftState.sortColumn = 'weeks';
                    draftState.sortOrder = 'ASC';
                } else if (payload === 'pending') {
                    draftState.sortColumn = 'weeks';
                    draftState.sortOrder = 'ASC';
                }

                if (payload != draftState.filterProperty) {
                    draftState.filterProperty = payload;
                    const listTotalCount = draftState.filterCounts[payload] || 0;
                    draftState.listTotalCount = listTotalCount;
                    draftState.isListReloading = true;
                }
            });
        }
        case CHANGE_SEARCH_QUERY: {
            return { ...state, searchQuery: payload, isListReloading: true };
        }
        case CLIENT_TAG_FILTER: {
            return { ...state, tagFilter: payload, isListReloading: true };
        }
        case CHANGE_ACTIVE_FILTER: {
            return { ...state, filterProperty: 'all', isActiveFilter: payload, isListReloading: true };
        }
        case CHANGE_SORT: {
            return { ...state, sortColumn: payload.column, sortOrder: payload.order, isListReloading: true };
        }
        case ADD_NEW_CLIENT.REQUEST: {
            return { ...state, clientAdding: true };
        }
        case ADD_NEW_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.clientAdding = false;
                draftState.selectedClientsDelete = !state.selectedClientsDelete;
            });
        }
        case ADD_NEW_CLIENT.FAILURE: {
            return { ...state, clientAdding: false, clientAddError: payload }
        }
        case DELETE_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients = draftState.clients.filter(client => client.id !== payload);
                if (draftState.listOffset > 0) {
                    draftState.listOffset--;
                }
                draftState.clientsCount[(draftState.isActiveFilter) ? 'active' : 'inactive']--;
            });
        }
        case SUBSCRIPTION_UPDATE.SUCCESS: {
            return produce(state, draftState => {
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.client));
                draftState.clients[objIndex].payments[0].active = false;

                if (payload.type === 'unsubscribe') {
                    draftState.clients[objIndex].payments[0].canceled = true;
                    draftState.clients[objIndex].payments[0].status = 'canceled';
                } else if (payload.type === 'pause') {
                    draftState.clients[objIndex].payments[0].paused_until = payload.trialEnd;
                    draftState.clients[objIndex].payments[0].status = 'paused';
                }
            })
        }
        case SUBSCRIPTION_UPDATE.FAILURE: {
            window.toastr.error('An error occurred, please contact Zenfit support.');
            return { ...state };
        }
        case DEACTIVATE_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients = draftState.clients.filter(client => client.id !== payload);
                if (draftState.listOffset > 0) {
                    draftState.listOffset--;
                }
                draftState.clientsCount['active']--;
                draftState.clientsCount['inactive']++;
            });
        }
        case ACTIVATE_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients = draftState.clients.filter(client => client.id !== payload);
                if (draftState.listOffset > 0) {
                    draftState.listOffset--;
                }
                draftState.clientsCount['inactive']--;
                draftState.clientsCount['active']++;
            });
        }
        case IGNORE_CLIENT_STATUS: {
            return produce(state, draftState => {
                draftState.statusId = payload.statusId;
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.id));
                draftState.clients[objIndex].status = payload.status;
                draftState.clients = draftState.clients;
                if (payload.eventName === 'client.need_welcome') {
                    draftState.selectedClientsDelete = !state.selectedClientsDelete;
                }

                draftState.statusUpdateFlg = !state.statusUpdateFlg;
            });
        }
        case SELECTED_CLIENT_COUNT: {
            return produce(state, draftState => {
                const objIndex = draftState.selectedClients.findIndex((obj => obj.id === payload.id));
                if (objIndex === -1) {
                    draftState.selectedClients.push(payload)
                }
                else {
                    draftState.selectedClients.splice(objIndex, 1);
                }
            });
        }
        case SELECTED_ALL_CLIENTS: {
            return produce(state, draftState => {
                if (draftState.selectedClients.length === draftState.clients.length) {
                    draftState.selectedClients = [];
                }
                else {
                    draftState.selectedClients = draftState.clients;
                }
            });
        }
        case CLIENT_STATUS_UPDATE: {
            return produce(state, draftState => {
                draftState.statusId = payload.id;
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.client.id));
                if (objIndex !== -1) {
                    const objIndex1 = draftState.clients[objIndex].status.findIndex((obj => obj.id === payload.id));
                    draftState.clients[objIndex].status[objIndex1].resolved = true;
                    draftState.statusUpdateFlg = !state.statusUpdateFlg;
                }
            });
        }
        case DELETE_SELECTED_CLIENTS.REQUEST: {
            return { ...state, isDeleting: true };
        }
        case DELETE_SELECTED_CLIENTS.SUCCESS: {
            return produce(state, draftState => {
                draftState.listOffset = parseInt((draftState.clients.length - draftState.selectedClients.length) / 25) + 1;
                draftState.clientsCount[(draftState.isActiveFilter) ? 'active' : 'inactive'] -= draftState.selectedClients.length;
                draftState.selectedClients = [];
                draftState.selectedClientsDelete = !state.selectedClientsDelete;
                draftState.isSubModal = false;
            });
        }
        case DEACTIVATE_SELECTED_CLIENT.REQUEST: {
            return { ...state, isDeleting: true };
        }
        case DEACTIVATE_SELECTED_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                if (draftState.listOffset > 0) {
                    draftState.listOffset -= payload.length;
                }
                draftState.clientsCount['active'] -= payload.length;
                draftState.clientsCount['inactive'] = parseInt(state.clientsCount['inactive']) + payload.length;
                draftState.selectedClientsDelete = !state.selectedClientsDelete;
                draftState.isDeleting = false;
            });
        }
        case ACTIVATE_SELECTED_CLIENT.REQUEST: {
            return { ...state, isDeleting: true };
        }
        case ACTIVATE_SELECTED_CLIENT.SUCCESS: {
            return produce(state, draftState => {
                if (draftState.listOffset > 0) {
                    draftState.listOffset -= payload.length;
                }
                draftState.clientsCount['active'] += payload.length;
                draftState.clientsCount['inactive'] = parseInt(state.clientsCount['inactive']) - payload.length;
                draftState.selectedClientsDelete = !state.selectedClientsDelete;
                draftState.isDeleting = false;
            });
        }
        case UPDATE_UNREAD_MESSAGE: {
            return produce(state, draftState => {
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload));
                draftState.clients[objIndex].messages.unreadCount = 0;
            });
        }
        case TOOLTIP_HANDLE: {
            return { ...state, isTooltipShow: payload };
        }
        case TASK_ADD_MODAL_OPEN: {
            return { ...state, isTaskModalOpen: payload.open, clientIdTask: payload.id };
        }
        case MEAL_PLAN_MODAL_OPEN: {
            return { ...state, isMealPlanModalOpen: payload.open, mealPlanModalError: null };
        }
        case MEDIA_TEMPLATE_MODAL_OPEN.REQUEST: {
            return { ...state };
        }
        case MEDIA_TEMPLATE_MODAL_OPEN.SUCCESS: {
            return {
                ...state,
                isMediaTemplateModalOpen: payload.open,
                mediaTemplateModalType: payload.type,
                currentMedias: payload.current,
                libraryMedia: payload.media
            };
        }
        case ADD_NEW_TASK.SUCCESS: {
            return produce(state, draftState => {
                const objIndex = draftState.clients.findIndex((obj => obj.id === draftState.clientIdTask));
                draftState.clients[objIndex].reminders = [...draftState.clients[objIndex].reminders, payload];
                draftState.isTaskModalOpen = false;
                draftState.addTaskFlg = !state.addTaskFlg
            });
        }
        case RESOLVE_REMINDER.SUCCESS: {
            return produce(state, draftState => {
                draftState.statusId = payload.id;
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.clientId));
                const objIndex1 = draftState.clients[objIndex].reminders.findIndex((obj => obj.id === payload.id));
                draftState.clients[objIndex].reminders[objIndex1].resolved = true;
                draftState.statusUpdateFlg = !state.statusUpdateFlg;
            });
        }
        case SUBSCRIPTION_MODAL_OPEN: {
            return { ...state, isSubModal: payload.open, clientSub: payload.client };
        }
        case UPDATE_CLIENT_PAYMENT.REQUEST: {
            return { ...state, isPaymentUpdating: true }
        }
        case UPDATE_CLIENT_PAYMENT.SUCCESS: {
            return produce(state, draftState => {
                draftState.isPaymentUpdating = false;
                draftState.isSubModal = false;
            });
        }
        case UPDATE_CLIENT_PAYMENT.FAILURE: {
            return { ...state, isPaymentUpdating: false, paymentError: payload }
        }
        case EXTEND_CLIENT_MODAL_OPEN: {
            return { ...state, isExtendModalOpen: payload.open, clientSub: payload.client, statusId: payload.statusId }
        }
        case EXTEND_CLIENT_DURATION: {
            return produce(state, draftState => {
                draftState.isExtendModalOpen = false;
                draftState.clientSub = {};
                draftState.filterCounts.ending -= 1;
                draftState.statusUpdateFlg = !state.statusUpdateFlg;
            })
        }
        case CLIENT_ACTIVE_STATUS_UPDATE.SUCCESS: {
            return produce(state, draftState => {
                draftState.clientsCount['active'] += 1;
                draftState.clientsCount['inactive'] -= 1;
                draftState.selectedClientsDelete = !state.selectedClientsDelete;
            })
        }
        case CLIENT_UPDATE_DURATION: {
            return produce(state, draftState => {
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.client));
                draftState.clients[objIndex].duration = payload.duration;

                if (payload.endDate) {
                    const end_date = {
                        date: payload.endDate
                    }
                    draftState.clients[objIndex].endDate = end_date;
                } else {
                    draftState.clients[objIndex].endDate = null;
                }

                if (payload.startDate) {
                    const start_date = {
                        date: payload.startDate
                    }
                    draftState.clients[objIndex].startDate = start_date;
                } else {
                    draftState.clients[objIndex].startDate = null;
                }

            })
        }
        case OPEN_SIDE_CONTENT: {
            return {
                ...state,
                sideContentMsgType: payload.messageType,
                sideContentTmpType: payload.templateType,
                openSideContent: payload.open,
                sideContentClient: payload.client
            }
        }
        case SEND_EMAIL.SUCCESS: {
            return { ...state, openSideContent: false }
        }
        case MULTI_SEND_MESSAGE.SUCCESS: {
            return { ...state, selectedClients: [] }
        }
        case MESSAGE_COUNT_UPDATE: {
            return produce(state, draftState => {
                const objIndex = draftState.clients.findIndex((obj => obj.id === payload.clientId));
                if (objIndex !== -1) {
                    draftState.clients[objIndex].messages.unreadCount = payload.count;
                    draftState.clients[objIndex].messages.unansweredCount = 0;
                    draftState.statusUpdateFlg = !state.statusUpdateFlg;
                }
            });
        }
        case GENERATE_MEAL_PLAN.REQUEST: {
            return { ...state, mealPlanModalError: null, mealPlanModalLoading: true };
        }
        case GENERATE_MEAL_PLAN.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients.find(client => client.id === state.selectedClient.id).master_meal_plans_count += 1;
                draftState.loadMealPlansFor = state.selectedClient.id;
                draftState.isMealPlanModalOpen = false;
                draftState.mealPlanModalLoading = false;
                draftState.reloadForUpdate = !draftState.reloadForUpdate;
            })
        }
        case GENERATE_MEAL_PLAN.FAILURE: {
            return { ...state, mealPlanModalError: payload, mealPlanModalLoading: false };
        }
        case GET_CLIENT_DOCS.REQUEST: {
            return { ...state, docsLoading: true };
        }
        case GET_CLIENT_DOCS.SUCCESS: {
            return { ...state, clientDocs: payload.docs, docsLoading: false };
        }
        case GET_CLIENT_VIDEOS.REQUEST: {
            return { ...state, videosLoading: true };
        }
        case GET_CLIENT_VIDEOS.SUCCESS: {
            return { ...state, clientVideos: payload.videos, videosLoading: false };
        }
        case UNLOAD_MEAL_PLAN: {
            return { ...state, loadMealPlansFor: null };
        }
        case GET_CLIENT_PAYMENTS_LOG.REQUEST: {
            return { ...state, paymentslogLoading: true };
        }
        case GET_CLIENT_PAYMENTS_LOG.SUCCESS: {
            return { ...state, clientPaymentsLog: payload.paymentsLog, paymentslogLoading: false };
        }
        case UPDATE_CLIENT_PAYMENT_LOG: {
            return produce(state, draftState => {
                draftState.clientPaymentsLog.unshift(payload);
            });
        }
        case GET_CLIENT_IMAGES.REQUEST: {
            return { ...state, imagesLoading: true };
        }
        case GET_CLIENT_IMAGES.SUCCESS: {
            return { ...state, clientImages: payload.images, imagesLoading: false };
        }
        case FETCH_MORE_CLIENT_IMAGES.REQUEST: {
            return { ...state, imagesLoading: true };
        }
        case FETCH_MORE_CLIENT_IMAGES.SUCCESS: {
            return produce(state, draftState => {
                draftState.clientImages.push(...payload.images);
                draftState.clientImagesPage += 1;
                draftState.imagesLoading = false;
                if (payload.images.length === 0) {
                    draftState.clientImagesMax = true;
                }
            })
        }
        case ADD_CLIENT_VIDEO.REQUEST: {
            return { ...state };
        }
        case ADD_CLIENT_VIDEO.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients.find(client => client.id === payload.clientId).videos_count += 1
                draftState.clientVideos.unshift(payload.videoAdded);
            });
        }
        case DELETE_CLIENT_VIDEO.REQUEST: {
            return {
                ...state
            };
        }
        case DELETE_CLIENT_VIDEO.SUCCESS: {
            return produce(state, draftState => {
                const objIndex = draftState.clientVideos.findIndex((obj => obj.id === payload));
                draftState.clientVideos.splice(objIndex, 1);
                draftState.clients.find(client => client.id === draftState.selectedClient.id).videos_count -= 1
            });
        }
        case DELETE_CLIENT_DOC.REQUEST: {
            return {
                ...state
            };
        }
        case DELETE_CLIENT_DOC.SUCCESS: {
            return produce(state, draftState => {
                const objIndex = draftState.clientDocs.findIndex((obj => obj.id === payload));
                draftState.clientDocs.splice(objIndex, 1);
                draftState.clients.find(client => client.id === draftState.selectedClient.id).documents_count -= 1
            });
        }
        case ADD_CLIENT_DOC_LIBRARY.REQUEST: {
            return {
                ...state,
                mediaLibraryLoading: true
            };
        }
        case ADD_CLIENT_DOC_LIBRARY.SUCCESS: {
            return produce(state, draftState => {
                draftState.libraryMedia.unshift(payload);
                draftState.mediaLibraryLoading = false;
            });
        }
        case ADD_CLIENT_DOC.REQUEST: {
            return { ...state };
        }
        case ADD_CLIENT_DOC.SUCCESS: {
            return produce(state, draftState => {
                draftState.clients.find(client => client.id === payload.clientId).documents_count += 1
                draftState.clientDocs.unshift(payload.docAdded);
            });
        }
        case GET_CLIENT_KCALS.REQUEST: {
            return { ...state };
        }
        case GET_CLIENT_KCALS.SUCCESS: {
            return produce(state, draftState => {
                draftState.selectedClientKcals = payload.kcals
            });
        }
        case CLIENT_WORKOUT_ADD_COUNT: {
            return produce(state, draftState => {
                draftState.clients.find(client => state.selectedClient.id === payload).workout_plans_count += 1
                draftState.isMediaTemplateModalOpen = false;
            });
        }
        case CLIENT_WORKOUT_SUBTRACT_COUNT: {
            return produce(state, draftState => {
                draftState.clients.find(client => state.selectedClient.id === payload).workout_plans_count -= 1
                draftState.isMediaTemplateModalOpen = false;
            });
        }
        default:
            return state;
    }
}
