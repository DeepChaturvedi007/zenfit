import React, { Component } from 'react';
import { connect } from 'react-redux';
import * as chat from '../actions/chat-action';
import * as clients from '../actions/clients-action';
import Messages from '../components/messages/messages';
import ConversationsList from '../components/conversations/conversations-list';
import ConversationModal from '../components/conversations/conversation-modal';
import MessageAllClientsModal from '../components/conversations/message-all-clients-modal';
import _ from 'lodash';

const RefElement = ({ setRef }) => <div className="ref-element" type="text" ref={setRef}></div>;

class ChatContainer extends Component {
  currentWidth = null;

  constructor(props) {
    super(props);

    this.state = {
      q: '',
      tags: [],
      selectedClients: {},
      sendToAll: false,
      selectedAllClients: false
    };

    this.resize = _.debounce(this.resize, 300);
    this.searchConversation = _.debounce(this.searchConversation, 700);
  }

  async componentDidMount() {
    const { searchConversations } = this.props;

    window.addEventListener('resize', this.resize, true);
    this.resize();

    await searchConversations(this.state.q, this.state.tags, false, true);
  }

  render() {
    const {
      messages,
      userId,
      selectedConversation,
      conversations,
      isLoading,
      isClientLoading,
      isMessageLoading,
      isShowNewMessageConversation,
      isShowSendMessageToAllClients,
      clients,
      clientsQuery,
      clientsTags,
      selectedClient,
      isShowMessages,
      startFrom,
      hasMore,
      reorderConversations,
      updateSelectedConversation,
      isMobile,
      scrolled,
      updateMessages,
      markUnreadConversation,
      markConversationDone,
      local
    } = this.props;
    const { q, tags, selectedClients, sendToAll } = this.state;
    const hiddenClass = isMobile ? 'hidden' : '';
    const messagesEnd = <RefElement setRef={this.setRef} />;
    return (
      <div className="chat-board">
        <aside className={`chat-board-client-list ${isShowMessages ? hiddenClass : ''}`}>
          <ConversationsList conversations={conversations}
            q={q}
            tags={tags}
            isMobile={isMobile}
            isLoading={isLoading}
            updateConversation={this.updateConversation}
            fetchChat={this.searchConversation}
            tagFilterConversation={this.tagFilterConversation}
            handleClick={this.showNewMessageConversation.bind(this, true, clientsQuery, clientsTags)}
            selectedConversation={selectedConversation}
          />
        </aside>
        <section className={`chat-board-container ${isShowMessages ? '' : hiddenClass}`}>
          <Messages messages={messages}
            q={q}
            messagesEnd={messagesEnd}
            scrollToBottom={this.scrollToBottom}
            userId={userId}
            selectedClient={selectedClient}
            scrolled={scrolled}
            updateScrolled={this.updateScrolled}
            hasMore={hasMore}
            isMessageLoading={isMessageLoading}
            isShowMessages={isShowMessages}
            startFrom={startFrom}
            selectedConversation={selectedConversation}
            conversations={conversations}
            updateMessages={updateMessages}
            reorderConversations={reorderConversations}
            isMobile={isMobile}
            handleClick={this.showMessages.bind(this, false)}
            deleteConversation={this.deleteConversation}
            deleteMessage={this.deleteMessage}
            markUnreadConversation={markUnreadConversation}
            markConversationDone={markConversationDone}
          />
        </section>
        <ConversationModal show={isShowNewMessageConversation}
          isClientLoading={isClientLoading}
          isMobile={isMobile}
          clients={clients}
          selectedClients={selectedClients}
          selectedAllClients={this.state.selectedAllClients}
          toggleSelectAllClients={this.toggleSelectAllClients}
          onConfirm={this.confirmSelect}
          onSelect={this.selectClients}
          clientQuery={clientsQuery}
          clientTags={clientsTags}
          searchClients={this.searchClients}
          tagFilterConversation={this.tagFilterConversation}
          onClose={this.showNewMessageConversation.bind(this, false)}
        />

        <MessageAllClientsModal show={isShowSendMessageToAllClients}
          q={q}
          messages={messages}
          hasMore={hasMore}
          scrollToBottom={this.scrollToBottom}
          reorderConversations={reorderConversations}
          updateSelectedConversation={updateSelectedConversation}
          isMobile={isMobile}
          userId={userId}
          selectedConversation={selectedConversation}
          clients={clients}
          updateMessages={updateMessages}
          selectedClients={selectedClients}
          sendToAll={sendToAll}
          onClose={this.showSendMessageToAllClients.bind(this, false)}
          onSend={this.showSendMessageToAllClients.bind(this, false)}
        />
      </div>
    );
  }

  showSendMessageToAllClients = status => {
    this.setState({ sendToAll: status }, () => {
      if (!status) this.setState({ selectedClients: {} });
      this.props.showNewMessageConversation(false);
      this.props.showSendMessageToAllClientsConversation(status);
    });
  };

  toggleSelectAllClients = () => {
    this.setState({ selectedAllClients: !this.state.selectedAllClients }, () => {
      if (this.state.selectedAllClients) {
        let allClients = {};
        for (var i = 0; i < this.props.clients.length; i++) {
          allClients[this.props.clients[i].id] = true;
        }
        this.setState({ selectedClients: allClients });
      } else {
        this.setState({ selectedClients: {} });
      }
    });
  };

  confirmSelect = () => {
    this.props.showNewMessageConversation(false);
    let selectedClients = Object.keys(this.state.selectedClients);
    if (selectedClients.length > 1) {
      this.props.showSendMessageToAllClientsConversation(true);
    } else {
      this.getConversation(selectedClients[0]);
    }
  };

  searchConversation = (params) => {
    this.setState({ q: params.q }, () => {
      this.updateScrolled(false);
      this.props.searchConversations(params.q, params.tags);
    });
  };

  tagFilterConversation = (params) => {
    this.setState({ tags: params.tags }, () => {
      this.updateScrolled(false);
      this.props.tagFilterConversation(params.q, params.tags);
    })
  }

  searchClients = (params) => {
    this.props.searchClients(params);
  };

  deleteConversation = () => {
    this.props.deleteConversation();
  };

  deleteMessage = id => {
    if (window.confirm('Are you sure you wish to delete this message?')) {
      this.props.deleteMessage(id);
    }
  };

  updateConversation = conversation => {
    this.updateScrolled(false);
    this.props.updateConversation(conversation, true, true);
  };

  showNewMessageConversation = (status, clientsQuery = '', clientsTags = null) => {
    //fire the request only when user list is initially empty
    if (this.props.clients.length === 0) {
      this.searchClients({ query: clientsQuery, tags: clientsTags });
    }

    if (!status) this.setState({ selectedClients: {} });
    this.props.showNewMessageConversation(status);
  };

  showMessages = status => {
    const { showMessages } = this.props;
    this.updateScrolled(!status);
    showMessages(status);
  };

  selectClients = (client) => {
    const newSelectedClients = {
      ...this.state.selectedClients
    };

    if (!newSelectedClients[client.id]) {
      newSelectedClients[client.id] = true;
    } else {
      delete newSelectedClients[client.id];
    }

    this.setState({ selectedClients: newSelectedClients });
  };

  getConversation = client => {
    this.setState({ q: '' }, () => {
      this.props.getConversation(client, this.state.q)
    });
  };

  resize = () => {
    if (this.currentWidth !== window.innerWidth) {
      const { isMobileView } = this.props;
      this.currentWidth = window.innerWidth;
      isMobileView(this.currentWidth <= 767);
    }
  };

  updateScrolled = status => {
    this.props.setScrolled(status);
  };

  setRef = elem => {
    this.refEl = elem;
  };

  scrollToBottom = () => {
    this.refEl.scrollIntoView({ behavior: 'instant' });
  };
}

function mapStateToProps(state) {
  return {
    selectedConversation: state.chat.selectedConversation,
    messages: state.chat.messages,
    conversations: state.chat.conversations,
    isLoading: state.chat.isLoading,
    isClientLoading: state.clients.isLoading,
    isMessageLoading: state.chat.isMessageLoading,
    isShowNewMessageConversation: state.chat.isShowNewMessageConversation,
    isShowMessages: state.chat.isShowMessages,
    selectedClient: state.chat.selectedClient,
    hasMore: state.chat.hasMore,
    startFrom: state.chat.startFrom,
    clients: state.clients.clients,
    clientsQuery: state.clients.query,
    clientsTags: state.clients.tags,
    userId: state.global.userId,
    isMobile: state.chat.isMobile,
    scrolled: state.chat.scrolled,
    isShowSendMessageToAllClients: state.chat.isShowSendMessageToAllClients
  }
}

export default connect(mapStateToProps, { ...chat, ...clients })(ChatContainer);
