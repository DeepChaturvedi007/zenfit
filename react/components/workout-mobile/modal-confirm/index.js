/*jshint esversion: 6 */
import React from 'react';
import Modal from '../modal';

export default class ModalConfirm extends React.Component {
    static TYPE_WARNING = 1;

    render() {
        const {show, onClose, onConfirm, title, description, btnText} = this.props;
        const btnClass = this.getButtonClassByType();

        return (
            <Modal show={show} closeModal={onClose}>
                <div className="modal-confirm-wrapper">
                    <div className="modal-confirm-text">
                        <h4>{title}</h4>
                        <p>{description}</p>
                    </div>
                    <div className="modal-confirm-buttons">
                        <button onClick={onClose}>Cancel</button>
                        <button className={btnClass} onClick={onConfirm}>{btnText}</button>
                    </div>
                </div>
            </Modal>
        );
    }

    getButtonClassByType() {
        switch(this.props.type) {
            case ModalConfirm.TYPE_WARNING:
                return 'modal-confirm-warning-btn';
            default:
                return '';
        }
    }
}
