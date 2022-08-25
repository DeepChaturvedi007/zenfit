import React from 'react';
import ChatIcon from "./icons/ChatIcon";

const ChatHeader = (props) => {
  const {
    clientName,
    clientPicture,
    onClick,
    unreadMessagesCount,
  } = props;

  return (
    <div className="chat-widget-header" onClick={() => onClick()}>
      <div className="col-left">
        <img className="client-photo" src={clientPicture} alt={`${clientName} photo`} />
        <div className="client-name">{clientName}</div>
      </div>
      <div className="col-right">
        {(unreadMessagesCount > 0) && (<div className="new-message-counter">{unreadMessagesCount}</div>)}
        <a href="#" className="chat-icon"><ChatIcon /></a>
        <i className="material-icons arrow-icon">keyboard_arrow_up</i>
      </div>
    </div>
  );
};

export default ChatHeader;
