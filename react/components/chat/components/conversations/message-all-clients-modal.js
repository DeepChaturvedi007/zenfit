import React, {Component} from 'react';
import ModalBootstrap from "../modal-bootstrap";
import SendField from '../messages/send-field';
import {GET_SIGNED_UPLOAD_URL, MULTIPLE_MESSAGE_SEND} from "../../../../api";
import axios from "axios/index";
import moment from 'moment';

export default class MessageAllClientsModal extends Component {

    constructor(props) {
        super(props);
        this.state = {
            isSending: false
        };

        this.handleSend = this.handleSend.bind(this);
    }

    async uploadVideo(video) {
        const [contentType] = video.type.split(';');
        let [fileType, extension = 'webm'] = contentType.split('/');
        const {
            data: {
                url,
                filename: key
            }
        } = await axios.get(GET_SIGNED_UPLOAD_URL(extension, contentType));
        await axios.put(url, video);
        return key
    }

    async handleSend(msg, video, voice) {
        const that = this;
        const {isSending} = this.state;
        const {
            q,
            messages,
            hasMore,
            clients,
            selectedClients,
            sendToAll,
            selectedConversation,
            reorderConversations,
            updateSelectedConversation,
            updateMessages,
            scrollToBottom,
            onSend
        } = this.props;

        const clientsIds = Object.keys(selectedClients).map(client => {
            return client;
        });

        const obj = {
            msg: msg,
            clients: clientsIds,
            selectedConversation: selectedConversation.id,
            createdAt: moment().format(),
        };
        if (!isSending) {
            this.setState({isSending: true}, async () => {
                if (video) {
                    const videoUrl = await this.uploadVideo(video)
                      .catch(() => {
                          this.setState({ isSending: false })
                      });
                    if(!videoUrl) return false;
                    obj.media = videoUrl;
                }
                if (voice) {
                    const voiceUrl = await this.uploadVideo(voice)
                      .catch(() => {
                          this.setState({ isSending: false })
                      });
                    if(!voiceUrl) return false;
                    obj.media = voiceUrl;
                }
                axios.post(MULTIPLE_MESSAGE_SEND(selectedConversation.userId, q), obj).then(res => {
                    const {conversations, messages: receivedMessages, selectedConversation} = res.data;
                    //const newMessages = _.uniqBy(messages.concat(_.xorBy(messages, receivedMessages, 'id')), 'id');
                    that.setState({
                        isSending: false
                    }, () => {
                        // updateMessages(receivedMessages, hasMore);
                        // reorderConversations(conversations);
                        // updateSelectedConversation(selectedConversation);
                        // onSend();
                        this.props.onClose();
                        toastr.success(res.data.msg);
                    });
                }).then(() => {
                    scrollToBottom();
                });
            });
        }
    }

    render() {
        const {show, onClose, isMobile, selectedClients, sendToAll} = this.props;
        const {isSending} = this.state;
        const numberOfClients = sendToAll ? 'all' : Object.keys(selectedClients).length;

        return (
            <div>
                <ModalBootstrap show={show}
                                onClose={onClose}
                                title="Send message to multiple clients"
                                subtitle={`You are about to send a message out to ${numberOfClients} of your clients`}
                >
                    <SendField isMobile={isMobile}
                               sendMessage={this.handleSend}
                               isSending={isSending}
                               className="chat-search-modal"
                    />
                </ModalBootstrap>
            </div>
        );
    }
}
