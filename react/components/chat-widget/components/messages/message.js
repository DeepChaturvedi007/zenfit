import React, { useState, useRef, useEffect } from 'react';
import DOMPurify from 'dompurify';
import moment from 'moment';
import Video from '../video';
import Voice from '../voice';
import { MESSAGE_PENDING } from '../../constants';
import MessageStatus from "./message-status";

const Message = ({ message, scrollToBottom }) => {
  const isPending = message.status === MESSAGE_PENDING;
  const messageElement = useRef(null);

  let alignment = 'right';
  let content = message.content ? DOMPurify.sanitize(message.content) : null;

  if (message.client) {
    alignment = 'left';
  }

  let chatMessageClass = 'chat-message-inner';
  let chatMessageProgressClass = '';

  if (message.isUpdate) {
    alignment = 'center';

    if (message.content) {
      content = (
        <div>
          <div className="chat-message-text">
            <div ref={messageElement} className="chat-message-update-text" dangerouslySetInnerHTML={{ __html: urlify(content) }}/>
          </div>
        </div>
      );

      chatMessageClass = 'chat-message-update';
      chatMessageProgressClass = 'chat-message-progress';
    }
  } else {
    if (message.content) {
      content = <div ref={messageElement} className="chat-message-text" dangerouslySetInnerHTML={{ __html: urlify(content) }}/>;
    }
  }

  let video = null;
  let statusPrefix = null;

  if (message.video) {
    if (isPending) {
      content = null;
      statusPrefix = 'Media is uploading. You may leave this page.';
    } else {
      video = <Video url={message.video} scrollToBottom={scrollToBottom} />;

      if (alignment == 'right') {
        alignment = `right-video`;
      } else {
        alignment = `left-video`;
      }
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
  function urlify(text) {
    var urlRegex = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlRegex, function(url) {
      return '<a href="' + url + '" class="chat-message-url" target="_blank">' + url + '</a>';
    })
  }
  return (
    <div
      className={`chat-message ${alignment} ${chatMessageProgressClass}`}
      id={`chat_widget_message_${message.id}`}
    >
      {(statusPrefix !== null) ? (
          <div className="chat-message-status">
            {statusPrefix}
          </div>
      ) : (
          <div className={chatMessageClass}>
            {video}
            {content}
          </div>
      )}
      {(message.isLast === true) && (
        <div className="chat-message-status">
          {!message.client && <MessageStatus status={message.status}/>}
          <span className="chat-message-status-time">{time}</span>
        </div>
      )}
    </div>
  );
};

export default Message;
