import React, {Component} from 'react';
import {Modal, ModalBody, ModalClose, ModalFooter, ModalHeader, ModalTitle} from 'react16-modal-bootstrap';

export default class AssignTemplateModal extends Component {
    render() {
        const {show, onClose, clientsData} = this.props;
        const clients = clientsData.map(client => {
            return (
                <div key={client.clientId} className="assign-client">
                    <div className="assign-client-info">
                        <div className="assign-client-name">
                            <a href={`/workout/clients/${client.clientId}`}>{client.clientName}</a>
                        </div>
                    </div>
                </div>
            );
        });
        return (
            <Modal className='inmodal in sm2' isOpen={show} onRequestHide={onClose}>
                <ModalHeader closeButton>
                    <ModalClose className='close' onClick={onClose}/>
                    <ModalTitle>Template Successfully Assigned</ModalTitle>
                    <p>The Template is now available for customization by visiting the client(s).</p>
                </ModalHeader>
                <ModalBody>
                    <div className="user-list">
                        {clients}
                    </div>
                </ModalBody>
                <ModalFooter>
                    <button type="submit" className="btn btn-success btn-upper" onClick={onClose}>
                        Done
                    </button>
                </ModalFooter>
            </Modal>
        );
    }
}
