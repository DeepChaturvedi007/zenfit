import _ from 'lodash';
import 'react-tippy/dist/tippy.css';
import React, { Component, createRef } from 'react';
import Pusher from 'pusher-js';
import SendField from './send-field';
import moment from 'moment';
import { Tooltip } from 'react-tippy';
import ChatStatus from './chat-status';
import ArrowLeftSIcon from '../icons/ArrowLeftS';
import ChatCheckLineIcon from '../icons/ChatCheckLine';
import EyeLineIcon from '../icons/EyeLine';
import CheckMarkIcon from '../icons/CheckMark';
import DeleteBinLineIcon from '../icons/DeleteBinLine';
import Message from './message';
import Spinner from '../../../spinner';
import {
  MESSAGE_SEND,
  MESSAGES_RECEIVE,
  CONVERSATIONS_RECEIVE,
  GET_SIGNED_UPLOAD_URL
} from '../../../../api';
import axios from 'axios';

require('moment-precise-range-plugin');

Array.prototype.move = function (from, to) {
  const x = this[from];

  this.splice(from, 1);
  this.splice(to, 0, x);

  return this;
};

export default class Messages extends Component {
  scrolled = false;

  scrolledToBottom = true;

  static defaultProps = {
    threshold: 5
  };

  constructor(props) {
    super(props);

    this.chat = createRef();
    this.state = {
      messages: props.messages,
      hasMore: props.hasMore,
      height: 44,
      startFrom: props.startFrom,
      isInfiniteLoading: false,
      selectedConversation: null,
      isSending: false
    };

    this.pusher = new Pusher('dcc6e72a783dd2f4b5e2', {
      appId: '1007100',
      cluster: 'eu',
      encrypted: true,
    });

    this.handleSize = this.handleSize.bind(this);
    this.onScroll = this.onScroll.bind(this);
    this.handleSend = this.handleSend.bind(this);
    this.scrollListener = this.scrollListenerMethod.bind(this);
  }

  componentDidMount() {
    this.props.scrollToBottom();
    this.attachScrollListener();
    this.subscribe();
  }

  componentWillReceiveProps(nextProps) {
    this.scrolled = nextProps.scrolled;
    if (
      this.state.selectedConversation !== nextProps.selectedConversation
      || this.state.messages !== nextProps.messages
    ) {
      this.setState({
        q: nextProps.q,
        messages: nextProps.messages,
        hasMore: nextProps.hasMore,
        startFrom: nextProps.startFrom,
        selectedConversation: nextProps.selectedConversation
      });
    }
  }

  componentDidUpdate() {
    if (this.state.startFrom === 0 && !this.scrolled && !this.props.isMessageLoading) {
      this.props.scrollToBottom();
      this.props.updateScrolled(true);
      this.scrolled = true;
    }
  }

  componentWillUnmount() {
    this.detachScrollListener();
    this.unsubscribe();
  }

  render() {
    const { messages, height, isInfiniteLoading, isSending } = this.state;
    const {
      isMobile,
      conversations,
      selectedClient,
      selectedConversation,
      reorderConversations,
      isMessageLoading,
      messagesEnd,
      handleClick,
      locale
    } = this.props;
    const lastMessageDate = messages.length ? moment(messages[messages.length - 1].date) : null;
    const emptyLastMessage = selectedClient && !isMessageLoading
      ? <div>You have not had a chat with {selectedClient.name} yet, start now!</div>
      : null;

    const sendField = selectedConversation
      ? <SendField isMobile={isMobile}
        onType={() => this.handleType()}
        isSending={isSending}
        sendMessage={this.handleSend}
        handleSize={this.handleSize}
        conversations={conversations}
        selectedConversation={selectedConversation}
        reorderConversations={reorderConversations}
        locale={locale}
      />
      : '';

    const clientUrl = selectedClient ? `/client/info/${selectedClient.id}` : null;

    return (
      <div className="chat-container">
        {
          selectedClient
            ? (<div className="chat-info">
              <div className="d-flex">
                <button
                  className="chat-info-btn left"
                  type="button"
                  onClick={handleClick}>
                  <ArrowLeftSIcon />
                </button>
              </div>
              <div className="flex-grow-1">
                <a href={clientUrl}>
                  <b className="chat-name">{selectedClient.name}</b>
                </a>
                <ChatStatus lastMessageDate={lastMessageDate} emptyLastMessage={emptyLastMessage} />
              </div>
              <div className="d-flex">
                <Tooltip
                  title="Delete Conversation"
                  position="bottom"
                  trigger="mouseenter"
                  size="big"
                >
                  <button
                    className="chat-info-btn"
                    type="button"
                    onClick={this.handleDeleteConversation}>
                    <DeleteBinLineIcon />
                  </button>
                </Tooltip>

                <Tooltip
                  title="Mark As Unread"
                  position="bottom"
                  trigger="mouseenter"
                  size="big"
                >
                  <button
                    className="chat-info-btn"
                    type="button"
                    title="Mark as unread"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    onClick={this.handleMarkAsUnread}
                  >
                    <EyeLineIcon />
                  </button>
                </Tooltip>

                <Tooltip
                  title="Mark As Done"
                  position="bottom"
                  trigger="mouseenter"
                  size="big"
                >
                  <button
                    className="chat-info-btn"
                    type="button"
                    title="Mark conversation done"
                    data-toggle="tooltip"
                    data-placement="bottom"
                    onClick={this.handleMarkAsDone}
                  >
                    <CheckMarkIcon />
                  </button>
                </Tooltip>
              </div>
            </div>)
            : null
        }
        <div className="chat-window" ref={this.chat} onScroll={this.handleChatScroll}>
          {isMessageLoading ? null : <Spinner show={isInfiniteLoading} />}
          {isMessageLoading ? <Spinner show={isMessageLoading} /> : this.getMessagesList(messages)}
          {messagesEnd}
        </div>
        {sendField}
      </div>
    )
  }

  getMessagesList(messages) {
    const { scrollToBottom } = this.props;

    return messages.map(message => <Message
      message={message}
      deleteMessage={this.props.deleteMessage}
      scrollToBottom={scrollToBottom}
      key={`message_${message.id}`}
    />);
  }

  onScroll() {
    const { isInfiniteLoading, hasMore } = this.state;
    if (!isInfiniteLoading && hasMore && !this.props.isMessageLoading) {
      this.handleFetchMessages();
    }
  }

  attachScrollListener() {
    this.chat.current.addEventListener('scroll', this.scrollListener, true);
  }

  detachScrollListener() {
    this.chat.current.removeEventListener('scroll', this.scrollListener, true);
  }

  scrollListenerMethod() {
    let topScrollPos = this.chat.current.scrollTop;
    let containerFixedHeight = this.chat.current.offsetHeight;
    let bottomScrollPos = topScrollPos + containerFixedHeight;

    if ((bottomScrollPos - containerFixedHeight) < this.props.threshold && this.scrolled) {
      this.onScroll();
    }
  }

  handleMarkAsUnread = () => {
    const { markUnreadConversation, selectedConversation } = this.props;

    markUnreadConversation(selectedConversation.id);
  };

  handleMarkAsDone = () => {
    const { markConversationDone, selectedConversation } = this.props;

    markConversationDone(selectedConversation.id);
  };

  handleDeleteConversation = () => {
    if (window.confirm('Are you sure you wish to delete this conversation?')) {
      this.props.deleteConversation()
    }
  };

  handleType = () => {
    const scrollable = this.chat.current;
    if (!scrollable) return;
    if (this.scrolledToBottom) {
      scrollable.scrollTop = scrollable.scrollHeight;
    }
  };

  handleChatScroll = (e) => {
    const {
      scrollTop,
      scrollHeight,
      clientHeight
    } = e.target;
    this.scrolledToBottom = scrollHeight - (clientHeight + scrollTop) <= 0;
  };

  handleSize(height) {
    this.setState({
      height: height
    });
  }

  handleFetchMessages() {
    const that = this;
    const { conversations, selectedConversation, updateMessages } = this.props;
    if (selectedConversation && conversations.length) {
      const conversation = conversations.filter(conversation => conversation.id === selectedConversation.id).shift();
      const clientId = conversation ? conversation.clientId : null;
      if (clientId) {
        this.setState({
          isInfiniteLoading: true,
          startFrom: this.state.messages.length
        }, () => {
          const obj = { startFrom: this.state.startFrom, isViewed: false };
          axios.post(MESSAGES_RECEIVE(clientId), obj).then(res => {
            const { messages: receivedMessages, hasMore } = res.data;
            that.setState({
              isInfiniteLoading: false,
              hasMore: hasMore
            }, () => {
              updateMessages([...receivedMessages, ...that.state.messages], hasMore);
            });
          });
        });
      }
    }
  }

  async handleSend(msg, video, voice) {
    const { conversations, selectedConversation, userId, updateMessages, scrollToBottom } = this.props;
    const clientId = conversations.filter(conversation => conversation.id === selectedConversation.id).shift().clientId;
    const { isSending, hasMore } = this.state;

    if (isSending) return;
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
    if (voice) {
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
      .finally(() => this.setState({ isSending: false }));
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

  trackMessages = () => {
    const that = this;
    const { selectedConversation, userId, reorderConversations, isShowMessages } = this.props;
    if (selectedConversation) {
      const { q } = this.state;
      const obj = { startFrom: 0, isViewed: isShowMessages };
      axios.post(MESSAGES_RECEIVE(selectedConversation.clientId), obj).then(res => {
        const receivedMessages = res.data.messages;
        //const newMessages = _.uniqBy(messages.concat(_.xorBy(messages, receivedMessages, 'id')), 'id');
        that.setState({ messages: receivedMessages }, () => {
          axios.get(CONVERSATIONS_RECEIVE(userId, q)).then(res => {
            reorderConversations && reorderConversations(res.data.conversations);
          });
        });
      });
    }
  };

  subscribe = () => {
    if (!this.chatChannel) {
      this.chatChannel = this.pusher.subscribe(`messages.trainer.${this.props.userId}`);
      this.chatChannel.bind('pusher:subscription_succeeded', () => {
        this.chatChannel.bind('message', this.onMessageReceive);
      });
    }
  };

  unsubscribe = () => {
    if (this.chatChannel) {
      this.chatChannel.unbind_all();
      this.chatChannel.unsubscribe();
    }
    this.chatChannel = null;
  };

  onMessageReceive = (data) => {
    if (data.clientId && data.clientId !== parseInt(this.state.selectedConversation.clientId)) {
      return;
    }

    const messages = [...this.state.messages];
    const index = _.findIndex(messages, item => item.id === data.id);

    if (index !== -1) {
      messages.splice(index, 1, data);
    } else {
      messages.push(data);
    }

    this.setState({ messages }, () => {
      this.props.updateMessages(messages, this.state.hasMore);
      this.props.scrollToBottom();
    });
  };
}
