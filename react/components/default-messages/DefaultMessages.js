import React, {Fragment} from 'react';
import {connect} from "react-redux";
import * as messagesActions from "./actions/messages-action";
import DefaultMessageList from "./components/DefaultMessageList";
import MessageModal from "./components/MessageModal";
import CreateItem from "./components/CreateItem";

class DefaultMessages extends React.Component {
    constructor(props) {
        super(props);

        this.createMessageHandler = this.createMessageHandler.bind(this);
        this.editMessageHandler = this.editMessageHandler.bind(this);
        this.deleteMessageHandler = this.deleteMessageHandler.bind(this);
        this.duplicateMessageHandler = this.duplicateMessageHandler.bind(this);
        this.closeModal = this.closeModal.bind(this);
    }

    createMessageHandler(messageBody, messageId, messageTitle, messageType, messageSubject) {
        this.props.populateForm('', messageType);
        this.props.toggleFormModal();
    }

    editMessageHandler(messageBody, messageId, messageTitle, messageType, messageSubject) {
        this.props.populateForm(messageId, messageType, messageBody, messageTitle, messageSubject);
        this.props.toggleFormModal();
    }

    deleteMessageHandler(messageBody, messageId, messageTitle, messageType, messageSubject) {
        this.props.deleteMessage(messageId);
    }

    duplicateMessageHandler(messageBody, messageId, messageTitle, messageType, messageSubject) {
        this.props.createMessage(messageType, messageTitle, messageBody, messageSubject);
    }

    closeModal() {
        this.props.toggleFormModal();
    }

    render() {
        const {messages, typeTitles, isFormModalOpen} = this.props;

        let typesLists = [];
        Object.keys(typeTitles).forEach((key) => {
            const messagesArray = (messages[key]) ? Object.values(messages[key]) : [];
            typesLists.push((
                <div className="default-message-type" key={key}>
                    <div className="type-title">
                        <span className="type-title-text">{typeTitles[key]}</span>
                        <CreateItem onClick={this.createMessageHandler} messageType={key} />
                    </div>
                    <DefaultMessageList
                        messages={messagesArray}
                        onItemClick={() => {}}
                        itemActions={{
                            edit: {
                                fn: this.editMessageHandler,
                                title: 'See / Edit Template',
                            },
                            duplicate: {
                                fn: this.duplicateMessageHandler,
                                title: 'Duplicate',
                            },
                            delete: {
                                fn: this.deleteMessageHandler,
                                title: 'Delete',
                            },
                        }}
                        messageType={key}
                        isLoading={false}
                    />
                </div>
            ));
        })

        return (
            <Fragment>
                <div className="default-messages">
                    {typesLists}
                </div>
                <MessageModal
                    isOpen={isFormModalOpen}
                    closeModal={this.closeModal}
                />
            </Fragment>
        );
    }
}

function mapStateToProps(state) {
    return {
        messages: state.messages.messages,
        isFormModalOpen: state.messages.isFormModalOpen,
        userId: state.messages.userId,
        typeTitles: state.messages.typeTitles,
    };
}

export default connect(mapStateToProps, {...messagesActions})(DefaultMessages);
