import React, {Component} from 'react';
import {connect} from 'react-redux';
import * as chat from '../actions/chat-action';
import Messages from '../components/messages/messages';
import ChatHeader from '../components/ChatHeader';
import _ from 'lodash';
import {DEFAULT_IMAGE_URL, S3_BEFORE_AFTER_IMAGES} from "../../chat/constants";
import Pusher from "pusher-js";
import moment from "moment";

const RefElement = ({setRef}) => <div className="ref-element" type="text" ref={setRef}></div>;

class ChatWidgetContainer extends Component {

  constructor(props) {
    super(props);

    this.pusher = new Pusher('dcc6e72a783dd2f4b5e2', {
      appId: '1007100',
      cluster: 'eu',
      encrypted: true,
    });
    this.contentInput = React.createRef();
    this.markMessagesAsRead = _.debounce(this.markMessagesAsRead.bind(this), 300);
    this.markUnreadConversation = _.debounce(this.markUnreadConversation.bind(this), 300);
    this.markConversationDone = _.debounce(this.markConversationDone.bind(this), 300);
  }

  componentDidMount() {
    this.subscribe();
  }

  componentWillUnmount() {
    this.unsubscribe();
  }

  componentDidUpdate(prevProps, prevState, snapshot) {
    const {isOpen} = prevProps;
    const prevUserId = prevProps.userId;
    const {fetchMessages, clientId, clearMessages, userId} = this.props;
    const currentIsOpen = this.props.isOpen;
    if (isOpen !== currentIsOpen) {
      if (!isOpen) {
        fetchMessages(clientId)
            .then(() => {
              this.scrollToBottom();
            });

        this.markMessagesAsRead();
      } else {
        clearMessages();
      }
    }
    if (prevUserId !== userId) {
      this.unsubscribe();
      this.subscribe();
    }
  }

  render() {
    const {
      messages,
      userId,
      clientName,
      clientPhoto,
      clientId,
      isMessageLoading,
      startFrom,
      hasMore,
      updateMessages,
      unreadMessagesCount,
      initChatWidget,
      isOpen,
      locale,
      messageType,
      toggleChatWidgetOpen
    } = this.props;
    const messagesEnd = <RefElement setRef={this.setRef}/>;
    const clientPicture = (clientPhoto) ? S3_BEFORE_AFTER_IMAGES + clientPhoto : DEFAULT_IMAGE_URL;

    return (
      <div className={`${window.location.pathname.includes('dashboard/clients') ? 'hidden-xs hidden-sm' : ''} chat-widget-wrap ${(isOpen) ? 'open' : ''} ${(initChatWidget) ? 'initialized' : ''}`}>
        <ChatHeader
          clientName={clientName}
          clientPicture={clientPicture}
          onClick={toggleChatWidgetOpen}
          unreadMessagesCount={unreadMessagesCount}
          clientId={clientId}
          locale={locale}
          messageType={messageType}
          contentInput={this.contentInput}
        />
        <Messages
          messages={messages}
          messagesEnd={messagesEnd}
          scrollToBottom={this.scrollToBottom}
          userId={userId}
          clientId={clientId}
          hasMore={hasMore}
          isMessageLoading={isMessageLoading}
          startFrom={startFrom}
          updateMessages={updateMessages}
          isOpen={isOpen}
          markMessagesAsRead={this.markMessagesAsRead}
          markUnreadConversation={this.markUnreadConversation}
          markConversationDone={this.markConversationDone}
          locale={locale}
          messageType={messageType}
          contentInput={this.contentInput}
        />
      </div>
    );
  }

  markUnreadConversation() {
    const { conversationId, markUnreadConversation } = this.props;
    markUnreadConversation(conversationId)
  }
  markConversationDone() {
    const { conversationId, markConversationDone } = this.props;
    markConversationDone(conversationId)
  }
  markMessagesAsRead() {
    const { unreadMessagesCount, markMessagesAsRead, clientId } = this.props;
    if (unreadMessagesCount) {
      markMessagesAsRead(clientId);
    }
  }

  subscribe = () => {
    if (!this.chatChannel && this.props.userId) {
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

  onMessageReceive = async (data) => {
    if (data.clientId && parseInt(data.clientId) !== parseInt(this.props.clientId)) {
      return;
    }

    let messages = [...this.props.messages];
    let unreadMessagesCount = this.props.unreadMessagesCount;
    const index = _.findIndex(messages, item => item.id === data.id);
    const isUnseen = data.unseen || false;

    if (index !== -1) {
      messages.splice(index, 1, data);
    } else if (isUnseen) {
      messages.push(data);
    }

    if (isUnseen && data.client === true) {
      unreadMessagesCount++;
    }

    await this.props.updateMessages(messages, this.props.hasMore, this.props.startFrom, unreadMessagesCount);
    this.scrollToBottom();
  };

  setRef = elem => {
    this.refEl = elem;
  };

  scrollToBottom = () => {
    this.refEl.scrollIntoView({behavior: "instant", block: "end", inline: "nearest"});
  };
}

function mapStateToProps(state) {
  return {
    messages: state.chat.messages,
    hasMore: state.chat.hasMore,
    startFrom: state.chat.startFrom,
    userId: state.global.userId,
    locale: state.global.locale,
    messageType: state.global.messageType,
    scrolled: state.chat.scrolled,
    isMessageLoading: state.chat.isMessageLoading,
    unreadMessagesCount: state.chat.unreadMessagesCount,
    clientName: state.global.clientName,
    clientPhoto: state.global.clientPhoto,
    clientId: state.global.clientId,
    conversationId: state.global.conversationId,
    initChatWidget: state.global.initChatWidget,
    isOpen: state.chat.isChatWidgetOpen,
  }
}

export default connect(mapStateToProps, {...chat})(ChatWidgetContainer);
