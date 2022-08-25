import React from 'react';

import ModalBootstrap from '../modal-bootstrap';
import ConversationClient from './conversation-client';
import Spinner from '../../../spinner';

const ConversationModal = React.memo(props => {
  let clientsList;
  let tagSuggestions = [];

  if (!props.isClientLoading) {
    if (props.clients.length) {
      clientsList = (
        <ul className="users-list">
          {props.clients.map(client => <ConversationClient
            key={`client_${client.id}`}
            client={client}
            active={!!props.selectedClients[client.id]}
            onSelect={props.onSelect} />)}
        </ul>
      );

      const tags = props.clients.reduce(function (suggestions, client) {
        return [...suggestions, ...client.tags];
      }, []);

      tagSuggestions = [...new Set(tags)].map(tag => {
        return {
          id: tag,
          name: tag,
        };
      });
    } else {
      clientsList = (
        <div className="user-notification">
          <p>You have not added any clients.</p>
          <p>Please add your first client to start conversation.</p>
        </div>
      );
    }
  } else {
    clientsList = (<Spinner show={true} />);
  }

  return (
    <ModalBootstrap
      className="modal-chat-clients"
      show={props.show}
      isMobile={props.isMobile}
      onClose={props.onClose}
      title="Choose one or more clients"
      selectedClients={props.selectedClients}
      onConfirm={props.onConfirm}
      toggleSelectAllClients={props.toggleSelectAllClients}
      selectedAllClients={props.selectedAllClients}
      clientQuery={props.clientQuery}
      clientTags={props.clientTags}
      searchClients={props.searchClients}
      tagFilterConversation={props.tagFilterConversation}
      tagSuggestions={tagSuggestions}
    >
      {clientsList}
    </ModalBootstrap>
  );
});

export default ConversationModal;

