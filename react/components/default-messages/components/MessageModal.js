import React from 'react';
import Modal from "react-modal";
import DefaultMessageForm from "./DefaultMessageForm";
import {Scrollbars} from "react-custom-scrollbars";

const MessageModal = (props) => {
    const {isOpen, closeModal} = props;

    return (
        <Modal
            isOpen={isOpen}
            contentLabel="Chat Message Templates"
            className="default-messages-form-modal"
            overlayClassName="default-messages-form-modal-overlay"
            appElement={document.body}
        >
            <div className="modal-top">
                <div className="modal-title">View / Edit</div>
                <a className="modal-close" onClick={() => closeModal()} />
            </div>
            <div className="modal-body">
                <Scrollbars
                    className="modal-body-inner"
                    renderView={props => (
                        <div {...props} className="modal-body-form" style={{ ...props.style, overflowX: 'hidden' }} />
                    )}
                    autoHide={true}
                    autoHideTimeout={1500}
                >
                    <DefaultMessageForm
                        cancelAction={closeModal}
                    />
                </Scrollbars>
            </div>
        </Modal>
    );
};

export default MessageModal;
