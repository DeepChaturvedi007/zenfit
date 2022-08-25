import React, { useState } from 'react';
import Modal from '@material-ui/core/Modal';
import { bounceInRight, bounceOutRight } from 'react-animations';
import Radium, { StyleRoot } from 'radium';
import axios from 'axios';
import FormField from './FormField';
import Spinner from "../spinner";

import { prepareMessageUsingTags } from './helper';
import { GET_DEFAULT_MESSAGES_LOCAL_QUEUE } from "../../api/default-messages";
import { SAVE_TEMPLATE } from '../../api';
import './styles.scss';

const styles = {
    bounceInRight: {
        animation: 'x 1s',
        animationName: Radium.keyframes(bounceInRight, 'bounceInRight')
    },
    bounceOutRight: {
        animation: 'x 1s',
        animationName: Radium.keyframes(bounceOutRight, 'bounceOutRight')
    }
}
const SideContent = (props) => {
    const { messageType, client, locale, templateType, sentEmail, reload, onSubmit, onClose } = props;

    const [defaultMessageList, setDefaultMessageList] = useState([]);
    const [sendData, setSendData] = useState({
        to: client.email,
        title: '',
        subject: '',
        template: null,
        message: ''
    });
    const [placeholders, setPlaceholders] = useState(null);
    const [loaded, setLoaded] = useState(false);
    const [closeButton, setCloseButton] = useState(false);

    const handleChangeSendData = (value, name, data) => {
        setSendData({ ...data, [name]: value })
    }
    const selectTemplate = (value) => {
        const objIndex = defaultMessageList.findIndex((obj => obj.id === parseInt(value)));
        const data = {
            to: client.email,
            title: 'Send email to client',
            subject: prepareMessageUsingTags(
                defaultMessageList[objIndex].subject,
                defaultMessageList[objIndex].placeholders,
                false
            ),
            message: prepareMessageUsingTags(
                defaultMessageList[objIndex].message,
                defaultMessageList[objIndex].placeholders,
                false
            ),
            template: defaultMessageList[objIndex].id,
        }
        setSendData(data);
        setPlaceholders(defaultMessageList[objIndex].placeholders)
    }
    const handleSubmit = (e) => {
        e.preventDefault();
        const bodyData = {
            queue: client.queue.id,
            type: messageType,
            to: sendData.to,
            subject: sendData.subject,
            ['default-message-dropdown']: sendData.template,
            message: sendData.message,
            reload: reload
        }
        onSubmit(bodyData, 'Email will be sent to client.')
    }

    const handleClose = (e) => {
        setCloseButton(false);
        onClose()
    }

    const saveTemplate = () => {
        const data = {
            textarea: prepareMessageUsingTags(
                sendData.message,
                placeholders,
                true
            ),
            type: messageType,
            subject: prepareMessageUsingTags(
                sendData.subject,
                placeholders,
                true
            )
        };
        axios.post(SAVE_TEMPLATE(), data).then((res) => {
            toastr.success(res.data.reason);
        })
            .catch(err => {
                toastr.error(err.response.data.reason);
            })
    }

    const modalClose = React.useCallback((e) => {
        if (e.keyCode === 27) {
            setCloseButton(true)
        }
    }, [])
    React.useEffect(() => {
        axios.get(GET_DEFAULT_MESSAGES_LOCAL_QUEUE(messageType, client.id, locale, messageType === 1 ? client.payment ? client.payment.datakey : client.payments[0].datakey : client.queue.datakey))
            .then(res => {
                let data = {};
                if (res.data.defaultMessages) {
                    const defaultMessages = (_.isObject(res.data.defaultMessages))
                        ? Object.values(res.data.defaultMessages)
                        : res.data.defaultMessages;
                    setDefaultMessageList(defaultMessages);
                    data = {
                        to: client.email,
                        title: 'Send email to client',
                        subject: prepareMessageUsingTags(
                            defaultMessages[defaultMessages.length - 1].subject,
                            defaultMessages[defaultMessages.length - 1].placeholders,
                            false
                        ),
                        message: prepareMessageUsingTags(
                            defaultMessages[defaultMessages.length - 1].message,
                            defaultMessages[defaultMessages.length - 1].placeholders,
                            false
                        ),
                        template: defaultMessages[defaultMessages.length - 1].id,
                    }
                    setPlaceholders(defaultMessages[defaultMessages.length - 1].placeholders)
                }
                setSendData(data);

            })
            .finally(() => setLoaded(true));
    }, []);
    React.useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])
    return (
        <Modal open={true} style={{ zIndex: 2002, overflow: 'auto', background: 'rgba(0, 0, 0, .5)' }} className="inmodal in sm2">
            <div>
                <StyleRoot>
                    <div className="side-container show" style={sentEmail ? styles.bounceOutRight : styles.bounceInRight}>
                        <button type="button" className="close" onClick={(e) => { setCloseButton(true) }}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        {loaded ? (
                            <div className="side-container-wrapper">
                                <div className="side-container-contents">
                                    <div className="row">
                                        <div className="col-lg-12 animated fadeInRight">
                                            <FormField
                                                templateList={defaultMessageList}
                                                sendData={sendData}
                                                clientName={client.firstName}
                                                dataKey={client.queue ? client.queue.datakey : ''}
                                                changeTemplate={selectTemplate}
                                                onChange={handleChangeSendData}
                                                handleSubmit={handleSubmit}
                                                onSaveTemplate={saveTemplate}
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        ) : (
                            <div className="loading-content">
                                <Spinner show={true} />
                            </div>
                        )}
                        {closeButton && (
                            <div className="side-container-error-container">
                                <div className="side-container-error">
                                    <h3>You have unsaved changes</h3>
                                    <button className="btn btn-success" style={{ marginRight: 5 }} onClick={(e) => setCloseButton(false)}>Continue</button>
                                    <button className="btn btn-default" onClick={handleClose}>Exit</button>
                                </div>
                            </div>
                        )}
                        <div className="side-container-backdrop" style={{ display: closeButton ? 'block' : 'none' }}></div>
                    </div>
                </StyleRoot>
            </div>
        </Modal>
    )
}

export default SideContent;
