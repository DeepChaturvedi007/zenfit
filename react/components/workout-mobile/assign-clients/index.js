/*jshint esversion: 6 */
import 'whatwg-fetch';
import _ from 'lodash';
import React from 'react';
import ModalUsersList from '../modal-users-list';
import {GET_CLIENTS} from '../../../api';

export default class AssignClients extends React.Component {
    q = '';

    constructor(props) {
        super(props);

        this.state = {
            clients: [],
            selectedClients: {},
            isLoading: false
        };

        this.debouncedResetClients = _.debounce(this.resetClients, 1000);
    }

    componentDidMount() {
        this.fetchClients();
    }

    render() {
        const {clients, selectedClients} = this.state;
        const {show, onClickAddClient} = this.props;

        return (
            <ModalUsersList show={show}
                            onClickAddClient={onClickAddClient}
                            users={clients}
                            selectedUsers={selectedClients}
                            onSearch={this.handleSearch}
                            onSelect={this.handleSelect}
                            onConfirm={this.handleConfirm}
                            onClose={this.handleClose}/>
        );
    }

    fetchClients() {
        this.setState({isLoading: true});
        fetch(GET_CLIENTS(this.q), {
            credentials: 'include'
        }).then(response => response.json()).then(response => {
            this.setState({isLoading: false, clients: response.clients});
        });
    }

    resetClients = () => {
        this.setState({clients: [], selectedClients: {}});
        this.fetchClients();
    };

    handleClose = () => {
        this.q = '';
        this.resetClients();
        this.props.onClose();
    };

    handleConfirm = () => {
        const clients = this.state.clients.filter(client => this.state.selectedClients[client.id]);
        this.props.onAssignClients(clients);
        this.q = '';
        this.resetClients();
    };

    handleSearch = (q) => {
        this.q = q;
        this.debouncedResetClients();
    };

    handleSelect = (user) => {
        const newSelectedClients = {...this.state.selectedClients};

        if (!newSelectedClients[user.id]) {
            newSelectedClients[user.id] = true;
        } else {
            delete newSelectedClients[user.id];
        }

        this.setState({selectedClients: newSelectedClients});
    };
}
