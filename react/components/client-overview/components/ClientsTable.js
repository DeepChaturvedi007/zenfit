import React, { Fragment } from 'react';
import { connect } from "react-redux";
import * as clients from "../store/clients/actions";
import * as progress from "../store/progress/actions";
import ClientItem from "./ClientItem";
import { throttle } from 'lodash';
import Spinner from "../../spinner";
import ReactTooltip from "react-tooltip";
import ModalChatTemplates from "../../modals/modal-chat-templates";
import ModalConfirmNeed from './modal-confirm-need'

class ClientsTable extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            clientId: null,
            messageType: -1,
            confirmModalOpen: false,
            messageClientId: 0,
            confirmModalMessage: '',
            chatTemplateModalOpen: false
        }
        this.wrapperEl = document.getElementById('page-wrapper');
        this.modalChatRef = React.createRef();

        this.onScroll = throttle(this.onScroll.bind(this), 100, { leading: true, trailing: true });
        this.onChangeSort = this.onChangeSort.bind(this);
        this.openClientDetail = this.openClientDetail.bind(this);
        this.onChangeMessageType = this.onChangeMessageType.bind(this);
        this.onHandleConfirmModal = this.onHandleConfirmModal.bind(this);
        this.ignoreChange = this.ignoreChange.bind(this);
    }

    componentDidMount() {
        this.addScrollListener();
        window.openChatTemplate = (type, clientId) => {
            this.setState({
                messageType: type,
                messageClientId: clientId,
            }, () => this.modalChatRef.current.toggleOpen())

        }
    }
    componentDidUpdate() {
    }
    componentWillUnmount() {
        this.removeScrollListener();
    }

    addScrollListener = () => {
        this.wrapperEl.addEventListener('scroll', this.onScroll);
    }

    removeScrollListener = () => {
        this.wrapperEl.removeEventListener('scroll', this.onScroll);
    }

    openChatWidget = (client, type) => {
        const { userId, locale } = this.props;
        if (window.openChatWidget) {
            window.openChatWidget(userId, client.id, client.name, client.photo, locale, type, client.messages.id);
        }
    }
    openClientDetail = (client) => {
        const { checkIns } = this.props;
        if (this.state.clientId === client.id) {
            this.setState({ clientId: null })
        } else {
            this.props.handleSelectClient(client);
            this.setState({ clientId: client.id });
        }
    }
    onChangeMessageType = (type, clientId) => {
        this.setState({
            messageType: type,
            messageClientId: clientId
        })
    }
    onScroll() {
        const { listTotalCount, clients, isInfiniteLoading, fetchClients, handleTooltip, isListReloading } = this.props;
        const hasMore = (listTotalCount > Object.keys(clients).length);
        if ((this.wrapperEl.scrollHeight - this.wrapperEl.scrollTop) <= (this.wrapperEl.clientHeight + 50) && !isListReloading) {
            if (!isInfiniteLoading && hasMore) {
                fetchClients();
            }
        }
        handleTooltip(false)
    }

    onChangeSort(columnKey, forceSortOrder = false) {
        const { changeClientSort, sortColumn, sortOrder } = this.props;

        let newSortOrder = 'ASC';
        if (forceSortOrder) {
            newSortOrder = forceSortOrder;
        } else if (columnKey === sortColumn) {
            newSortOrder = (sortOrder === 'ASC') ? 'DESC' : 'ASC';
        }

        if (columnKey !== sortColumn || sortOrder !== newSortOrder) {
            changeClientSort(columnKey, newSortOrder);
        }
    }
    onHandleConfirmModal = (value) => {
        this.setState({
            confirmModalOpen: value
        })
    }
    ignoreChange = () => {
        const statusId = this.state.clientDetails.status.filter((item) => {
            return item.event_name === 'client.need_welcome'
        })[0].id
        this.props.ignoreStatus(statusId, this.state.clientDetails.id, 'client.need_welcome')
        this.setState({
            confirmModalOpen: false,
            confirmModalMessage: 'Are you sure you wish to activate the client without sending them a welcome message?'
        })
    }

    render() {
        const {
            clients,
            isListReloading,
            isInfiniteLoading,
            deleteClientAction,
            deactivateClientAction,
            activateClientAction,
            sortColumn,
            sortOrder,
            selectAllClients,
            selectedClients,
            selectedClient,
            handleActionModal,
            userId,
            locale,
            isActiveFilter,
            filterProperty
        } = this.props;
        let columns = [];
        if (isActiveFilter) {
            columns = [
                { name: 'check', sortable: false },
                { name: 'Name', sortable: true, sortKey: 'name' },
                { name: 'Week', sortable: true, sortKey: 'weeks' },
                { name: 'Check-In', sortable: true, sortKey: 'checkin_day' },
                { name: 'Messages', sortable: true, sortKey: 'messages' },
                { name: 'Status', sortable: true, sortKey: 'status' },
                { name: '', sortable: false },
                { name: '', sortable: false }
            ]

            if (filterProperty == 'pending') {
                //remove check-in column from Pending column + rename Week to Start Date
                const checkIn = columns.findIndex((obj => obj.name === 'Check-In'));
                columns.splice(checkIn, 1);
                const week = columns.findIndex((obj => obj.name === 'Week'));
                columns[week].name = 'Start Date';
            }
        }
        else {
            columns = [
                { name: 'check', sortable: false },
                { name: 'Name', sortable: true, sortKey: 'name' },
                { name: 'Week', sortable: true, sortKey: 'weeks' },
                { name: 'Check-In', sortable: true, sortKey: 'checkin_day' },
                { name: '', sortable: false },
                { name: '', sortable: false }
            ]
        }
        let columnItems = [];
        columns.map((item, index) => {
            let onClick = undefined;
            let className = [];
            if (item.sortable) {
                onClick = () => this.onChangeSort(item.sortKey, item.onlyAvailableOrder || false);
                className.push('sortable');
                if (sortColumn === item.sortKey) {
                    className.push('sort-active');
                    className.push((sortOrder === 'ASC') ? 'sort-asc' : 'sort-desc');
                }
            }
            if (item.name === 'check') {
                columnItems.push(<th key={index} className={className.join(' ')}>
                    <label className="client-item-checkbox">
                        <input type="checkbox" onChange={selectAllClients} checked={selectedClients.length !== 0} />
                        <span className={selectedClients.length === clients.length ? "checkmark" : "checkmark1"}></span>
                    </label>
                </th>)
            } else {
                columnItems.push(<th key={index} className={className.join(' ')} onClick={onClick}><span>{item.name}</span></th>);
            }
        });
        let clientItems = [];
        clients.forEach(client => {
            clientItems.push(
                <ClientItem
                    key={client.id}
                    client={client}
                    deleteAction={deleteClientAction}
                    deactivateAction={deactivateClientAction}
                    activateAction={activateClientAction}
                    openChatWidget={this.openChatWidget}
                    openClientDetail={this.openClientDetail}
                    selectedClientId={this.state.clientId}
                    handleMessageType={this.onChangeMessageType}
                    openConfirmModal={this.onHandleConfirmModal}
                    handleActionModal={handleActionModal}
                />
            );
        });

        return (
            (!isListReloading) ? (
                <Fragment>
                    {clients.length === 0 ? (
                        <p className="empty-clients">No clients to show here</p>
                    ) : (
                        <table className="clients-table" style={{ padding: 0 }}>
                            <thead>
                                <tr className="hidden-xs hidden-sm clients-table-header">
                                    {columnItems}
                                </tr>
                            </thead>
                            <tbody>
                                {clientItems}
                            </tbody>
                        </table>
                    )}
                    <ReactTooltip place="top" type="dark" id={'default-tooltip'} effect="solid" multiline={true} clickable={true} />
                    <ReactTooltip place="bottom" type="dark" id={'scrollable-tooltip'} delayHide={500} effect="solid" multiline={true} clickable={true} />
                    {isInfiniteLoading && <Spinner show={true} />}
                    <ModalChatTemplates
                        ref={this.modalChatRef}
                        clientId={this.state.messageClientId}
                        client={selectedClient}
                        defaultMessageType={this.state.messageType}
                        handleMessageType={this.onChnageMessageType}
                        userId={userId}
                        locale={locale}
                        show={this.state.chatTemplateModalOpen}
                    />
                    <ModalConfirmNeed
                        show={this.state.confirmModalOpen}
                        onClose={this.onHandleConfirmModal}
                        onSubmit={this.ignoreChange}
                    />
                </Fragment>
            ) : (
                <Spinner show={true} />
            )
        );
    }
}

function mapStateToProps(state) {
    return {
        clients: state.clients.clients,
        isInfiniteLoading: state.clients.isInfiniteListLoading,
        isListReloading: state.clients.isListReloading,
        listTotalCount: state.clients.listTotalCount,
        listOffset: state.clients.listOffset,
        userId: state.clients.userId,
        locale: state.clients.locale,
        sortColumn: state.clients.sortColumn,
        sortOrder: state.clients.sortOrder,
        selectedClients: state.clients.selectedClients,
        selectedClient: state.clients.selectedClient,
        isActiveFilter: state.clients.isActiveFilter,
        filterProperty: state.clients.filterProperty,
        checkIns: state.progress.clientProgress
    }
}

export default connect(mapStateToProps, { ...clients, ...progress })(ClientsTable);
