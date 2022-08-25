/*jshint esversion: 6 */
import React, { useState, useEffect, useRef, createRef } from 'react';
import Modal from '@material-ui/core/Modal';

import autosize from 'autosize';

import './styles.scss';
export default function ModalMultiSend(props) {
    const {
        show,
        clients,
        handleModalOpen,
        onSubmit
    } = props;
    let messageInputRef = createRef(null);
    const [message, setMessage] = useState('');
    const [sendMessage, setSendMessage] = useState(false);

    const changeMessage = (e) => {
        setMessage(e.target.value);
    }
    const handleSubmit = () => {
        onSubmit(message.replace(/\n/g, "<br>"))
        setSendMessage(true)
    }
    useEffect(() => {
        setMessage('');
        setSendMessage(false)
    }, [show])
    useEffect(() => {
        if(messageInputRef.current){
            messageInputRef.current.focus();
            autosize(messageInputRef.current)
        }
    }, [messageInputRef])
    return (
        <Modal open={show} style={{zIndex: 2002, overflow: 'auto'}} className="inmodal in sm2" onClose={() => handleModalOpen(false)}>
            <div className="modal-dialog" style={{marginTop: 160, outline: 'none'}}>
                <div className="modal-content modal-content-light-grey">
                    <div className="modal-header">
                        <button type="button" className="close" onClick={() => handleModalOpen(false)}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        <h4 className="modal-title">
                            Write message to multiple clients
                        </h4>
                        <p>
                            Sending to 
                            {clients.map((item, i) => {
                                return (
                                    <span key={i}>{" "+item.name+","}</span>
                                )
                            })}
                        </p>
                    </div>
                    <div className="modal-body">
                        <div className="row">
                            <div className="col-sm-12">
                                <label htmlFor="message">Message</label>
                                <textarea
                                    ref={messageInputRef}
                                    className="form-control"
                                    onChange={(e) => {changeMessage(e)}}
                                    value={message}
                                    rows={1}
                                />
                            </div>
                        </div>
                    </div>
                    <div className="modal-footer footer-button">
                        <button 
                            className="btn btn-success btn-upper"
                            onClick={handleSubmit}
                            disabled={sendMessage}
                        >
                            {sendMessage ? (
                                'Sending...'
                            ) : (
                                'Send'
                            )}
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    );
}
