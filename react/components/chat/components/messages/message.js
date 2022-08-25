import React, { useState, useRef, useEffect } from 'react';
import DOMPurify from 'dompurify';
import moment from 'moment';
import MessageStatus from './message-status';
import Video from '../video';
import Voice from '../voice';
import { DEFAULT_IMAGE_URL, MESSAGE_PENDING } from '../../constants';

const Message = React.memo(({ message, deleteMessage, scrollToBottom }) => {
  const isPending = message.status === MESSAGE_PENDING;
  const messageElement = useRef(null);
  const [showDeleteButton, setShowDeleteButton] = useState(false);
  const [messageHeight, setMessageHeight] = useState(0);
  const [deleteBtn, setDeleteButton] = useState(null);

  let alignment = 'right';
  let content = message.content ? DOMPurify.sanitize(message.content) : null;
  let image = null;

  useEffect(() => {
    if(messageElement.current) {
      setMessageHeight(messageElement.current.clientHeight);
    }
  }, [messageElement])

  useEffect(() => {
    if (message.client) {
      return;
    }
    const style = {
      height: messageHeight
    };
    setDeleteButton(<span className="fa fa-trash delete-button" style={style} onClick={() => {deleteMessage(message.id)}} />)
  }, [messageHeight, message])

  if (message.client) {
    const src = message.clientImg ? message.clientImg : DEFAULT_IMAGE_URL;

    alignment = 'left';
    image = <img className="chat-img" src={src} alt=""/>;
  }

  if (message.isUpdate) {
    alignment = 'center';
    image = null;

    if (message.content) {
      let clientStatus = message.clientStatus;
      let feedbackGiven = '';

      if (clientStatus && clientStatus.hasOwnProperty('resolved')) {
        feedbackGiven = clientStatus.resolved ?
          <span className="label label-success">FEEDBACK GIVEN</span> :
          <span className="label label-info">NO FEEDBACK YET</span>;
      }

      content = (
        <div>
          {feedbackGiven}
          <div className="chat-text chat-update">
            <div ref={messageElement} className="update-text" dangerouslySetInnerHTML={{ __html:content }}/>
          </div>
        </div>
      );
    }
  } else {
    if (message.content) {
      content = <div ref={messageElement} className="chat-text" dangerouslySetInnerHTML={{ __html: content }}/>;
    }
  }

  let video = null;
  let statusPrefix = null;

  if (message.video) {
    if (isPending) {
      content = null;
      statusPrefix = 'Media uploading. You may leave this page.';
    } else {
      video = <Video url={message.video} scrollToBottom={scrollToBottom}/>;
    }
  }

  const now = moment(new Date().setHours(23,59,59,999));
  const date = moment(message.date);
  const { days } = moment.preciseDiff(now, date, true);
  let time = null;

  if (days > 1) {
    time = date.format('llll');
  } else if (days === 1) {
    time = `Yesterday, ${date.format('LT')}`;
  } else {
    time = `${date.format('LT')}`;
  }

  return (
    <div className={`chat-message ${alignment}`}
         onMouseEnter={() => setShowDeleteButton(true)}
         onMouseLeave={() => setShowDeleteButton(false)}
    >
      {image}
      <div className="chat-inner">
        {video}
        {content}
        {showDeleteButton && deleteBtn}
        <div className="chat-date">
          {statusPrefix}
          {!message.client && <MessageStatus status={message.status}/>}
          {time}
        </div>
      </div>
    </div>
  );
});

export default Message;
