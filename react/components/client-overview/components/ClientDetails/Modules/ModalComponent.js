import React, {Fragment, useEffect, useState} from "react";
import axios from "axios";
import {GET_WORKOUT_TEMPLATES} from "../../../../../api/workout-api";
import Modal from "@material-ui/core/Modal";

const ModalComponent = (props) => {
    const {
        open,
        onClose,
        className,
        title,
        titleButton,
        mediaUploadForm
    } = props;

    const modalClose = React.useCallback((e) => {
        if(e.keyCode === 27){
            onClose()
        }
    }, [])


    useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])

    return (
        <Modal open={open} style={{zIndex: 2002}} onClose={onClose} className={`inmodal in ${className}`}>
            <div className="modal-dialog" style={{outline: 'none'}}>
                <div className="modal-content modal-content-light-grey">
                    <div className="modal-header">
                        <button type="button" className="close" onClick={onClose}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        <h4 className="modal-title"> {title} </h4>
                        {titleButton}
                    </div>
                    {mediaUploadForm}
                    <div className="modal-body">
                        {props.children}
                    </div>
                    <div className="modal-footer"></div>
                </div>
            </div>
        </Modal>
    );
}
export default ModalComponent;
