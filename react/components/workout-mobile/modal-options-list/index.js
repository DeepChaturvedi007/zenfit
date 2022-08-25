/*jshint esversion: 6 */
import React from 'react';
import Modal from '../modal';

export default class OptionsList extends React.Component {
    render() {
        const {show, closeModal, children} = this.props;

        return (
            <Modal show={show} closeModal={closeModal}>
                <div className="modal-wrapper">
                    <div className="options-list-wrapper">
                        <ul>
                            {children}
                        </ul>
                        <button type="button" className="btn-cancel" onClick={closeModal}>Cancel</button>
                    </div>
                </div>
            </Modal>
        );
    }
}
