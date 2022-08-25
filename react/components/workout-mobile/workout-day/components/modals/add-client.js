import React, {Component} from 'react';
import {Modal, ModalBody, ModalClose, ModalFooter, ModalHeader, ModalTitle} from 'react16-modal-bootstrap';
import {ADD_NEW_CLIENT} from "../../../../../api";
import axios from "axios";

export default class AddClientModal extends Component {
    static defaultProps = {
        alertClassName: 'alert alert-danger',
        newClientNameId: '#newClientName',
        newClientEmailId: '#newClientEmail'
    };
    constructor(props) {
        super(props);

        this.state = {
            name: '',
            email: '',
            isUnprocessable: false,
            reason: '',
            errorId: null
        };
    }

    shouldComponentUpdate(nextProps, nextState){
        return (
            nextProps.show !== this.props.show
            ||
            nextState.isUnprocessable !== this.state.isUnprocessable
            ||
            nextState.name !== this.state.name
            ||
            nextState.email !== this.state.email
            ||
            nextState.errorId !== this.state.errorId
        );
    }

    render() {
        const {isUnprocessable, reason, errorId, name, email} = this.state;
        const {show, alertClassName, newClientNameId, newClientEmailId} = this.props;
        return (
            <Modal className='inmodal in sm2' isOpen={show} onRequestHide={this.handleOnClose}>
                <ModalHeader closeButton>
                    <ModalClose className='close' onClick={this.handleOnClose}/>
                    {isUnprocessable ? <div className={`notify ${alertClassName}`}>{reason}</div> : ''}
                    <ModalTitle>Add New Client</ModalTitle>
                    <p>When you add a new client, we take care of inviting them to the Zenfit Mobile App, help them fill out your questionnaire etc.</p>
                </ModalHeader>
                <ModalBody>
                    <div className={`form-group ${isUnprocessable && errorId == newClientNameId ? alertClassName : ''}`}>
                        <label htmlFor="newClientName" className="control-label">Client's Name</label>
                        <input type="text"
                               className="form-control"
                               placeholder="Your Client’s Full Name"
                               value={name}
                               onChange={this.handleChangeName}
                        />
                    </div>
                    <div className="form-group">
                        <div className={isUnprocessable && errorId == newClientEmailId ? alertClassName : ''}>
                            <label htmlFor="newClientEmail" className="control-label">Client's E-mail</label>
                            <input type="text"
                                   className="form-control"
                                   placeholder="Enter Client’s E-mail"
                                   value={email}
                                   onChange={this.handleChangeEmail}
                            />
                        </div>
                        <p className="modal-description">Please double check that the e-mail is correct. We’ll send important info to this email.</p>
                    </div>
                </ModalBody>
                <ModalFooter>
                    <button className="btn btn-success btn-upper btn-block" onClick={this.handleAddNewClient}>
                        Add client
                    </button>
                </ModalFooter>
            </Modal>
        );
    }

    handleOnClose = () => {
        this.setState({
            name: '',
            email: '',
            isUnprocessable: false,
            reason: '',
            errorId: null
        }, () => {
            this.props.onClose();
        });
    };

    handleAddNewClient = () => {
        const that = this;
        const {workoutId, setTotalActiveClients} = this.props;
        const {name, email} = this.state;
        const obj = {path: 'clientInfo', clientName: name, clientEmail: email, plan: workoutId};
        axios.post(ADD_NEW_CLIENT(), obj).then(response => {
            const clientsData = response.data;
            mixpanel.track("Assigned workout template to client");
            that.props.onAdd(clientsData);
            that.handleOnClose();
            setTotalActiveClients();
        }).catch(response => {
            const data = response.response.data;
            that.setState({
                isUnprocessable: true,
                reason: data.reason,
                errorId: data.id
            })
        });
    };

    handleChangeName = event => {
        this.setState({name: event.target.value});
    };

    handleChangeEmail = event => {
        this.setState({email: event.target.value});
    };
}
