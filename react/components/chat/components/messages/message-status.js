import _ from 'lodash';
import React from 'react';
import { MESSAGE_FAILED, MESSAGE_PENDING, MESSAGE_READ } from '../../constants';

const MessageStatus = React.memo(({status}) => {
  const title = status ? _.capitalize(status) : 'Delivered';
  let icon = null;

  switch (status) {
    case MESSAGE_PENDING:
      icon = <path
        d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 1 0 0-16 8 8 0 0 0 0 16zm1-8h4v2h-6V7h2v5z"/>;
      break;
    case MESSAGE_READ:
      icon = <path
        d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm-.997-6l7.07-7.071-1.414-1.414-5.656 5.657-2.829-2.829-1.414 1.414L11.003 16z"/>;
  }

  return (
    <div className={`chat-message-status --${status}`} title={title}>
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
        <g>
          {icon}
        </g>
      </svg>
    </div>
  );
});

export default MessageStatus;
