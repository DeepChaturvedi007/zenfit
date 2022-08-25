/*jshint esversion: 6 */
import React from 'react';
import ReactDOM from 'react-dom';

export default class Modal extends React.Component {
    componentDidMount(){
        this.modal = document.createElement('div');
        document.body.appendChild(this.modal);
        this.renderModalContent();
    }

    componentWillUnmount(){
        ReactDOM.unmountComponentAtNode(this.modal);
        document.body.removeChild(this.modal);
    }

    componentDidUpdate(){
        this.renderModalContent();
    }

    renderModalContent(){
        const {show, closeModal, children} = this.props;
        const hiddenClass = show ? '' : 'hidden';

        ReactDOM.render(
            <div className={`modal-container ${hiddenClass}`}>
                <div className="modal-overlay" onClick={closeModal}/>
                {children}
            </div>,
            this.modal
        );
    }

    render() {
        return (null);
    }
}
