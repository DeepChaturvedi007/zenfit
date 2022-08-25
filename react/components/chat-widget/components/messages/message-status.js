import _ from 'lodash';
import React from 'react';
import { MESSAGE_FAILED, MESSAGE_PENDING, MESSAGE_READ } from '../../constants';

const MessageStatus = React.memo(({status}) => {
  const title = status ? _.capitalize(status) : 'Delivered';
  let icon = null;

  switch (status) {
    case MESSAGE_PENDING:
      icon = <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm1-8h4v2h-6V7h2v5z"/></g></svg>;
      break;
    case MESSAGE_READ:
      icon = <svg height="512" viewBox="0 0 515.556 515.556" width="512" xmlns="http://www.w3.org/2000/svg"><path d="m0 274.226 176.549 176.886 339.007-338.672-48.67-47.997-290.337 290-128.553-128.552z"/></svg>;
  }

  return (
    <div className={`chat-message-status-icon --${status}`} title={title}>
      {icon}
      <span className="status-label">{title}</span>
    </div>
  );
});

export default MessageStatus;
