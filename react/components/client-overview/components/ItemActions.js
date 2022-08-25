import React from 'react';
import 'react-confirm-alert/src/react-confirm-alert.css';
import {statusAction} from "./ClientDetails/statusList";

class ItemActions extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            isOpen: false,
            disableAccessOnDeactivation: false,
        };

        this.wrapperRef = React.createRef();
        this.handleClickOutside = this.handleClickOutside.bind(this);
    }

    componentWillUnmount() {
        document.removeEventListener('mousedown', this.handleClickOutside);
    }

    handleClickOutside(event) {
        if (this.wrapperRef && !this.wrapperRef.current.contains(event.target) && this.state.isOpen) {
            this.toggleDropdown();
        }
    }

    toggleDropdown = () => {
        const {isOpen} = this.state;
        this.setState({isOpen: !this.state.isOpen});
        if (!isOpen) {
            document.addEventListener('mousedown', this.handleClickOutside);
        } else {
            document.removeEventListener('mousedown', this.handleClickOutside);
        }
    };

    deleteClient = () => {
        const {deleteAction, client, handleActionModal} = this.props;
        const msg = 'Are you sure you wish to delete: '+client.name+'?'
        handleActionModal(true, msg, () => deleteAction([client.id]));
    }

    resendQuestionaire = () => {
        this.setState({isOpen: !this.state.isOpen});
        statusAction(this.props.client,'','client.questionnaire_pending')
    }

    deactivateAction = () => {
        const {deactivateAction, client, handleActionModal} = this.props;
        const msg = 'Are you sure you wish to deactivate '+client.name+'?';
        handleActionModal(true, msg, () => deactivateAction([client.id]));
    }

    openSubscriptionModal = () => {
        const {subscriptionAction, client} = this.props;
        subscriptionAction(true, client);
    }

    render() {
        const {isOpen} = this.state;
        const {client, activateAction} = this.props;

        return (
            <div className={`client-item-more ${(isOpen) ? 'open' : ''}`} ref={this.wrapperRef}>
                <ul className={`client-item-more-list ${(isOpen) ? 'open' : ''}`}>
                    {client.active && (
                        <li className={`client-item-more-list-action`}>
                            <a href={`/client/info/${client.id}`}>Visit client</a>
                        </li>
                    )}
                    {!client.active && (
                        <li className={`client-item-more-list-action`}>
                            <span onClick={() => activateAction(client.id)}>Activate client</span>
                        </li>
                    )}
                    <li className={`client-item-more-list-action`}>
                        <span onClick={() => this.openSubscriptionModal()}>View subscription</span>
                    </li>
                    {client.active && (
                        <li className={`client-item-more-list-action`} onClick={() => this.deactivateAction()}>
                            <span>Deactivate client</span>
                        </li>
                    )}
                    <li className={`client-item-more-list-action`} onClick={() => this.deleteClient()}>
                        <span>Delete client</span>
                    </li>
                    <li className={`client-item-more-list-action`} onClick={() => this.resendQuestionaire()}>
                        <span>Resend questionaire</span>
                    </li>
                </ul>
                <span className="client-item-more-btn" onClick={this.toggleDropdown}>More</span>
            </div>
        );
    }
}

export default ItemActions;
