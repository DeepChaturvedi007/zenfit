import React, { useState, Fragment } from 'react';

import Collapse from '@material-ui/core/Collapse';
import ArrowForwardIosIcon from '@material-ui/icons/ArrowForwardIos';

import moment from "moment";
import { DEFAULT_IMAGE_URL, S3_BEFORE_AFTER_IMAGES } from "../const";
import { getWeeks } from "../helpers";
import ItemActions from "./ItemActions";
import PaymentStatus from "./PaymentStatus";
import ItemStatus from "./ItemStatus";
import ClientDetail from './ClientDetails';

import { connect } from 'react-redux';
import {
    selectClient,
    unreadMessageUpdate,
    handleSubscriptionModal,
    clientActiveStatusUpdate,
    deleteClientAction
} from "../store/clients/actions";
import * as clients from "../store/clients/actions";

const ClientItem = (props) => {
    const {
        client,
        deleteAction,
        deactivateAction,
        activateAction,
        openChatWidget,
        openClientDetail,
        unreadMessageUpdate,
        selectedClientId,
        selectClient,
        deleteSelectedClients,
        deactivateSelectedClients,
        selectedClients,
        userId,
        handleSubscriptionModal,
        isActiveFilter,
        filterProperty,
        clientActiveStatusUpdate,
        handleActionModal
    } = props;
    const [mobile, setMobile] = useState(false);
    const src = client.photo ? S3_BEFORE_AFTER_IMAGES + client.photo : DEFAULT_IMAGE_URL;
    let weeksContent = getWeeks(client, false, filterProperty == 'pending' ? true : false);

    let checkIn = '-';
    if (client.bodyProgressUpdated) {
        checkIn = moment.utc(client.bodyProgressUpdated.date).fromNow();
    }

    const openChat = (client, type) => {
        openChatWidget(client, type);
        if (client.unreadMessagesCount !== 0) {
            unreadMessageUpdate(client.id)
        }
    }

    let messageContentText = null;
    let messageContent = null;
    let mobileMsgContent = null;
    let unansweredCountLabel = null;
    let unreadCountLabel = null;

    const unreadMessagesCount = client.messages.unreadCount;
    const unansweredMessagesCount = client.messages.unansweredCount;
    const oldestUnreadMessageDate = client.messages.oldestUnreadMessage;
    const lastMessageSentDate = client.messages.lastMessageSent;
    const needWelcomeEvent = client.status.filter((item) => {
        return (item.event_name === 'client.need_welcome' && !item.resolved);
    })

    if (unreadMessagesCount > 0) {
        unreadCountLabel = <span className="message-badge unread">{(unreadMessagesCount < 10) ? unreadMessagesCount : '+'}</span>
        messageContentText = <Fragment>{unreadCountLabel}<p>{moment.utc(oldestUnreadMessageDate).fromNow()}</p></Fragment>;
        messageContent = <span className="client-item-message" onClick={() => { openChat(client) }}>{messageContentText}</span>;
    } else if (unansweredMessagesCount > 0) {
        unansweredCountLabel = <span className="message-badge unanswered">{(unansweredMessagesCount < 10) ? unansweredMessagesCount : '+'}</span>
        messageContentText = <Fragment>{unansweredCountLabel}<p>{moment.utc(lastMessageSentDate).fromNow()}</p></Fragment>;
        messageContent = <span className="client-item-message" onClick={() => { openChat(client) }}>{messageContentText}</span>;
    } else {
        messageContentText = <Fragment><p>Write message</p></Fragment>;
        messageContent = <span className="client-item-message" onClick={() => { openChat(client) }}>{messageContentText}</span>;
    }

    if (needWelcomeEvent.length !== 0) {
        let extraLabel = unreadCountLabel ? unreadCountLabel : (unansweredCountLabel ? unansweredCountLabel : '');
        messageContentText = <Fragment><p>{extraLabel} Activate client</p></Fragment>;
        messageContent = <span className="client-item-message" onClick={() => { openChat(client, { id: 13, action: true }) }}>{messageContentText}</span>;
    }
    if (unreadMessagesCount > 0) {
        mobileMsgContent = <span className="client-item-mobile-message-badge unread" onClick={() => { openChat(client) }}>{(unreadMessagesCount < 10) ? unreadMessagesCount : '+'}</span>;
    } else if (unansweredMessagesCount > 0) {
        mobileMsgContent = <span className="client-item-mobile-message-badge unanswered" onClick={() => { openChat(client) }}>{(unansweredMessagesCount < 10) ? unansweredMessagesCount : '+'}</span>;
    }
    let tags = [];
    if (client.tags) {
        client.tags.forEach((tag, i) => {
            tags.push(<span className="client-item-tagslist-item" key={i}>{tag}</span>);
        });
    }
    const _client = selectedClients.filter(item => {
        return item.id === client.id
    })
    const submitClientPayment = (data) => {
        clientPaymentUpdate(data).then(data => {
        })
    }
    const goClientPage = (id) => {
        window.location.href = '/client/info/' + id
    }
    const activeClient = (id) => {
        const msg = `Are you sure you want to activate: ${client.name}?`
        handleActionModal(true, msg, () => clientActiveStatusUpdate([id]))
    }

    React.useEffect(() => {
        function updateSize() {
            if (window.innerWidth < 600) {
                setMobile(true)
            }
            else {
                setMobile(false);
            }
        }
        window.addEventListener('resize', updateSize);
        updateSize();
        return () => window.removeEventListener('resize', updateSize);
    }, [])
    return (
        <React.Fragment>
            <tr className={selectedClientId === client.id ? "client-item-conent client-table-body" : "client-item-conent1 client-table-body"}>
                <td className="hidden-xs hidden-sm">
                    <label className="client-item-checkbox">
                        <input type="checkbox" onChange={() => { selectClient(client) }} checked={_client.length !== 0} />
                        <span className="checkmark"></span>
                    </label>
                </td>
                <td>
                    <span className="flex-vert-center">
                        <span className="client-item-photo" style={{ cursor: 'pointer' }} onClick={() => { openClientDetail(client) }}><img src={src} alt={client.name} /></span>
                        <div className='flex-mobile-center'>
                            <span className="client-item-name" onClick={() => { openClientDetail(client) }}>{client.name}</span>
                            <PaymentStatus payments={client.payments} />
                            <span className="client-item-tagslist hidden-xs hidden-sm">{tags}</span>
                        </div>
                    </span>
                </td>
                <td className="hidden-xs hidden-sm"><span className="client-item-weeks">{weeksContent}</span></td>
                {filterProperty != 'pending' && (
                    <td className="hidden-xs hidden-sm"><span className="client-item-checkin">{checkIn}</span></td>
                )}
                {isActiveFilter ? (
                    <React.Fragment>
                        <td>
                            <div className="hidden-xs hidden-sm">
                                {messageContent}
                            </div>
                            <div className="visible-xs visible-sm">
                                {mobileMsgContent}
                            </div>
                        </td>
                        <td style={{ cursor: 'pointer' }} onClick={() => { openClientDetail(client) }}>
                            <ItemStatus statuses={client.status} reminders={client.reminders} />
                        </td>
                        <td style={{ width: 100 }}>
                            <ItemActions
                                client={client}
                                deleteAction={deleteSelectedClients}
                                deactivateAction={deactivateSelectedClients}
                                activateAction={activateAction}
                                subscriptionAction={handleSubscriptionModal}
                                handleSubmit={submitClientPayment}
                                handleActionModal={handleActionModal}
                            />
                        </td>
                        <td style={{ width: 50 }} className="hidden-xs hidden-sm">
                            <ArrowForwardIosIcon className='client-go-arrow' onClick={() => goClientPage(client.id)} />
                        </td>
                    </React.Fragment>
                ) : (
                    <td style={{ width: 100 }}>
                        <button className="btn btn-success btn-sm" onClick={() => { activeClient(client.id) }}>Activate</button>
                    </td>
                )}
            </tr>
            <tr className='client-detail-content'>
                <td colSpan={mobile ? "4" : "8"}>
                    <Collapse in={selectedClientId === client.id}>
                        <div className='client-detail-main'>
                            {
                                selectedClientId === client.id &&
                                    <ClientDetail
                                        clientDetail={client}
                                        selectedClientId={selectedClientId}
                                        {...props} />
                            }
                        </div>
                    </Collapse>
                </td>
            </tr>
        </React.Fragment>
    );
};

function mapStateToProps(state) {
    return {
        isClientDetailLoading: state.clients.isClientDetailLoading,
        isClientDetailLoaded: state.clients.isClientDetailLoaded,
        selectedClients: state.clients.selectedClients,
        userId: state.clients.userId,
        isActiveFilter: state.clients.isActiveFilter,
        filterProperty: state.clients.filterProperty
    }
}

export default connect(mapStateToProps, {...clients, selectClient, unreadMessageUpdate, handleSubscriptionModal, clientActiveStatusUpdate })(ClientItem);
