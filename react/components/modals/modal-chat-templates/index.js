import './styles.scss';

import React, {Fragment} from 'react';
import Modal from 'react-modal';
import axios from "axios";
import {GET_DEFAULT_MESSAGES, GET_DEFAULT_MESSAGES_LOCAL} from "../../../api/default-messages";
import _ from 'lodash';
import DOMPurify from 'dompurify';
import Spinner from "../../spinner";
import ChatTemplateItem from "./components/ChatTemplateItem";

class ModalChatTemplates extends React.Component {
    static defaultProps = {
        defaultMessageType: 15,
    };

    constructor(props) {
        super(props);

        this.state = {
            isOpen: false,
            isLoading: true,
            templates: [],
            previewText: '',
            isPreviewOpen: false,
        };

        this.toggleOpen = this.toggleOpen.bind(this);
        this.templatePaste = this.templatePaste.bind(this);
        this.previewText = this.previewText.bind(this);
        this.togglePreview = this.togglePreview.bind(this);
        this.handleLocal = this.handleLocal.bind(this);
    }

    toggleOpen() {
        const {isOpen} = this.state;
        const {clientId, defaultMessageType, handleMessageType, locale} = this.props;
        this.setState({isOpen: !isOpen});
        const messageType = defaultMessageType;
        if (!this.state.isOpen) {
            this.setState({isLoading: true});
            axios.get(GET_DEFAULT_MESSAGES_LOCAL(messageType, clientId, locale))
                .then(res => {
                    if (res.data.defaultMessages) {
                        const defaultMessages = (_.isObject(res.data.defaultMessages))
                            ? Object.values(res.data.defaultMessages)
                            : res.data.defaultMessages;
                        this.setState({templates: defaultMessages});
                    }
                })
                .finally(() => this.setState({isLoading: false}));
        }
        if(handleMessageType && this.state.isOpen){
            handleMessageType(-1)
        }
    }
    handleLocal(e) {
        const {clientId, defaultMessageType} = this.props;
        this.setState({isLoading: true})
        axios.get(GET_DEFAULT_MESSAGES_LOCAL(defaultMessageType, clientId, e.target.value))
                .then(res => {
                    if (res.data.defaultMessages) {
                        const defaultMessages = (_.isObject(res.data.defaultMessages))
                            ? Object.values(res.data.defaultMessages)
                            : res.data.defaultMessages;
                        this.setState({templates: defaultMessages});
                    }
                })
                .finally(() => this.setState({isLoading: false}));
    }
    templatePaste(message, invoice) {
        const {inputRef, userId, client, locale, clientId} = this.props;
        if(inputRef){
            const contentInput = inputRef.current || {};
            if (contentInput.replace) {
                const clearMsg = DOMPurify.sanitize(message, {ALLOWED_TAGS: []});
                contentInput.replace(clearMsg);
            }
        }
        else {
            localStorage.setItem('unSentClientId', clientId);
            localStorage.setItem('unSentMsg', message);
            setTimeout(() => {
                if (window.openChatWidget) {
                    window.openChatWidget(userId, client.id, client.name, client.photo, locale, {id: 8, action: false}, client.messages.id);
                }
            }, 500)
        }

        this.toggleOpen();
    }

    previewText(text) {
        this.setState({previewText: text, isPreviewOpen: !this.state.isPreviewOpen});
    }

    togglePreview() {
        this.setState({isPreviewOpen: !this.state.isPreviewOpen});
    }

    render() {
        const {label, client} = this.props;
        const {templates, isOpen, isLoading, isPreviewOpen, previewText} = this.state;
        const messageTemplates = templates.map((item) =>
            <ChatTemplateItem
                {...item}
                key={item.id}
                messageId={item.id}
                useTemplateAction={this.templatePaste}
                previewTextAction={this.previewText}
            />
        );

        const content = (messageTemplates.length)
            ? (
                <div className="template-list" style={{ overflowX: 'hidden' }} >
                    {messageTemplates}
                </div>
            )
            : (
                <div className="no-messages alert alert-primary">
                    You don't have any templates - <a href="#" onClick={() => {Intercom('show');}}>contact support</a> to create one for you
                </div>
            )

        return (
            <Fragment>
                <Modal
                    isOpen={isOpen}
                    contentLabel="Chat Message Templates"
                    className="default-messages-modal"
                    overlayClassName="default-messages-modal-overlay"
                    appElement={document.body}
                    shouldCloseOnOverlayClick={true}
                    onRequestClose={this.toggleOpen}
                >
                    <div className="modal-top">
                        <div className="modal-title">Choose a chat template</div>
                        <a className="modal-close" onClick={() => this.toggleOpen()} />
                    </div>
                    {/* <div className="template-local-lng" onChange={this.handleLocal}>
                        <select className="form-control">
                            <option value="en">English</option>
                            <option value="da_DK">Danish</option>
                            <option value="sv_SE">Swedish</option>
                            <option value="nb_NO">Norwegian</option>
                            <option value="fi_FI">Finnish</option>
                            <option value="nl_NL">Dutch</option>
                            <option value="de_DE">German</option>
                        </select>
                    </div> */}
                    <div className="template-list-wrap">

                        {(isLoading)
                            ? <Spinner show={isLoading}/>
                            : content
                        }
                    </div>
                </Modal>
                <Modal
                    isOpen={isPreviewOpen}
                    contentLabel="Template Preview"
                    className="default-messages-preview"
                    overlayClassName="default-messages-preview-overlay"
                    appElement={document.body}
                    shouldCloseOnOverlayClick={true}
                    onRequestClose={this.togglePreview}
                >
                    <a className="modal-close" onClick={() => this.togglePreview()} />
                    <div className="preview-text">
                        {previewText}
                    </div>
                </Modal>
                <span onClick={this.toggleOpen}>{label}</span>
            </Fragment>
        );
    }
}

export default ModalChatTemplates;
