/*jshint esversion: 6 */
import React from 'react';
import Dialog from '@material-ui/core/Dialog';
import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";
import moment from 'moment';
import './styles.scss';

export default function ModalConfirmNeed(props) {
    const {
        show,
        message,
        onSubmit,
        onClose
    } = props;


    return (
        <Dialog aria-labelledby="simple-dialog-title" open={show} style={{zIndex: 2002}}>
            <div className="modal inmodal in sm2" style={{display: (show ? 'block' : 'none')}}>
                <div className="modal-dialog" style={{marginTop: 160}}>
                    <div className="modal-content modal-content-light-grey">
                        <div className="modal-header">
                            <button type="button" className="close" onClick={() => onClose(false)}>
                                <span aria-hidden="true">×</span>
                                <span className="sr-only">Close</span>
                            </button>
                            <h4 className="modal-title">
                                {message ? message : 'Are you sure you wish to activate the client without sending them a welcome message?'}
                            </h4>
                        </div>
                        {!message && (
                            <div className="modal-body">
                                <div>The client will be moved to your active clients.</div>
                            </div>
                        )}
                        <div className="modal-footer footer-button">
                            <button className="btn btn-default btn-upper" onClick={() => onClose(false)}>No</button>
                            <button className="btn btn-success btn-upper" onClick={onSubmit}>Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </Dialog>
    );
}
