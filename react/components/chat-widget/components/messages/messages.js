import 'react-tippy/dist/tippy.css';
import React, {Component, createRef, Fragment} from 'react';
import SendField from './send-field';
import {
  MESSAGE_SEND,
  GET_SIGNED_UPLOAD_URL,
  CLIENT_STATUS_ACTION
} from '../../../../api';
import axios from 'axios';
import AnimateHeight from "react-animate-height";
import MessageList from "./MessageList";
import ModalChatTemplates from "../../../modals/modal-chat-templates";
import { DEFAULT_MESSAGE_TYPES } from '../../constants';

export default class Messages extends Component {
  scrolled = false;
  scrolledToBottom = true;

  static defaultProps = {
    maxMessagesHeight: 350,
    minMessagesHeight: 200,
    messagesHeightThreshold: 50,
  };

  constructor(props) {
    super(props);

    this.chat = createRef();
    this.state = {
      hasMore: props.hasMore,
      isInfiniteLoading: false,
      isSending: false,
      messagesHeight: props.maxMessagesHeight,
      messageType: -1,
      showAction: false,
      actionResponse: null,
      activeClient: null
    };

    this.handleSend = this.handleSend.bind(this);
    this.handleAction = this.handleAction.bind(this);
    this.openChatTemplate = this.openChatTemplate.bind(this);
    this.setMessagesWindowHeight = _.debounce(this.setMessagesWindowHeight.bind(this), 250, {leading: false, maxWait: 1000, trailing: true});
  }

  componentDidMount() {
    this.props.scrollToBottom();
    window.addEventListener('resize', this.setMessagesWindowHeight, true);
    this.setMessagesWindowHeight();
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.setMessagesWindowHeight, true);
  }

  componentWillReceiveProps(nextProps) {
    this.scrolled = nextProps.scrolled;
    if (this.state.messages !== nextProps.messages) {
      this.setState({
        messages: nextProps.messages,
        hasMore: nextProps.hasMore,
      });
    }

    if (this.state.activeClient != nextProps.clientId) {
      if (nextProps.messageType && nextProps.messageType.action){
        this.setState({ showAction: true })
      } else {
        this.setState({ showAction: false })
      }
      this.setState({ actionResponse: null })
    }
  }

  setMessagesWindowHeight() {
    if (
      (window.innerHeight < (this.props.maxMessagesHeight + this.props.messagesHeightThreshold))
      || (this.state.messagesHeight < this.props.maxMessagesHeight)
    ) {
      let height = window.innerHeight - this.props.messagesHeightThreshold;
      height = (height < this.props.minMessagesHeight) ? this.props.minMessagesHeight : height;
      height = (height > this.props.maxMessagesHeight) ? this.props.maxMessagesHeight : height;
      this.setState({ messagesHeight: height });
    }
  }
  openChatTemplate(value) {
    this.setState({
      messageType: value
    })
  }
  render() {
    const { isInfiniteLoading, isSending, messagesHeight, showAction, actionResponse } = this.state;
    const {
      messagesEnd,
      hasMore,
      startFrom,
      messages,
      clientId,
      updateMessages,
      isMessageLoading,
      markMessagesAsRead,
      markUnreadConversation,
      markConversationDone,
      locale,
      messageType,
      contentInput
    } = this.props;

    const videoMessageHeight = this.state.messagesHeight - (this.state.messagesHeight * 0.25);

    return (
        <Fragment>
          <style dangerouslySetInnerHTML={{__html: `.chat-message .chat-message-inner .player-wrapper .react-player > video {
            max-height: ${videoMessageHeight}px;
          }`}}></style>
          <AnimateHeight
            height={(this.props.isOpen) ? messagesHeight : 0}
            duration={500}
            className="chat-container"
          >
            {showAction && (
              <div className="show-chat-action">
                {actionResponse ?
                  <p className='client-action-noti-content'>{actionResponse}</p>
                :
                  <div>
                    <p className='client-action-noti-title'>{DEFAULT_MESSAGE_TYPES[messageType.id].title}</p>
                    <p className='client-action-noti-content'>{DEFAULT_MESSAGE_TYPES[messageType.id].subtitle}</p>
                    <button className="btn client-active-btn" onClick={this.handleAction}>Yes</button>
                    <button className="btn client-ignore-btn" onClick={() => this.setState({ showAction: false })}>Not yet</button>
                  </div>
                }
              </div>
            )}
            <MessageList
              messages={messages}
              isInfiniteLoading={isInfiniteLoading}
              messagesEnd={messagesEnd}
              hasMore={hasMore}
              startFrom={startFrom}
              clientId={clientId}
              updateMessages={updateMessages}
              isMessageLoading={isMessageLoading}
              markMessagesAsRead={markMessagesAsRead}
            />
            <div className="send-field-wrap">
              <SendField
                isSending={isSending}
                sendMessage={this.handleSend}
                clientId={clientId}
                messagesHeight
                isOpen={this.props.isOpen}
                locale={locale}
                contentInput={contentInput}
                messageType={messageType}
                markUnreadConversation={markUnreadConversation}
                markConversationDone={markConversationDone}
              />
            </div>
            <ModalChatTemplates
              clientId={clientId}
              inputRef={this.contentInput}
              locale={locale}
              defaultMessageType={this.state.messageType}
              handleMessageType={this.openChatTemplate}
          />
          </AnimateHeight>
        </Fragment>
    );
  }

  handleChatScroll = (e) => {
    const {
      scrollTop,
      scrollHeight,
      clientHeight
    } = e.target;
    this.scrolledToBottom = scrollHeight - (clientHeight + scrollTop) <= 0;
  };

  async handleAction() {
    const { clientId, messageType } = this.props;
    const data = new FormData();
    data.append('clientId', clientId);
    data.append('type', messageType.id);

    await axios.post(CLIENT_STATUS_ACTION(), data)
      .then(res => {
        const { msg } = res.data;
        this.setState({ actionResponse: msg, activeClient: clientId });
      });
  }

  async handleSend(msg, video, voice) {
    const { userId, updateMessages, scrollToBottom, clientId, hasMore, messageType } = this.props;
    const { isSending } = this.state;

    if (isSending) {
      return;
    }
    const data = new FormData();

    this.setState({ isSending: true });

    if (video) {
      const videoUrl = await this.uploadVideo(video)
        .catch(() => {
          this.setState({ isSending: false })
        });
      if (!videoUrl) return false;
      data.append('media', videoUrl);
    }
    if(voice){
      const voiceUrl = await this.uploadVideo(voice)
        .catch(() => {
          this.setState({ isSending: false })
        });
      if (!voiceUrl) return false;
      data.append('media', voiceUrl);
    }
    data.append('msg', msg);
    data.append('clientId', clientId);
    data.append('trainer', userId);

    await axios.post(MESSAGE_SEND(), data)
      .then(res => {
        const receivedMessages = res.data.messages;
        updateMessages(receivedMessages, hasMore);
        scrollToBottom();
      })
      .finally(() => {
        this.setState({ isSending: false });
      });
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
}
