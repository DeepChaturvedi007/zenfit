import React, {Component, createRef, Fragment} from 'react';
import Message from "./message";
import moment from "moment";
import Spinner from "../../../spinner";
import axios from "axios";
import {MESSAGES_RECEIVE, MARK_MESSAGES_READ} from "../../../../api";
import { Scrollbars } from 'react-custom-scrollbars';

require('moment-precise-range-plugin');

export default class MessageList extends Component {

  static defaultProps = {
    threshold: 5,
    className: 'chat-window'
  };

  constructor(props) {
    super(props);

    this.chatRef = createRef();
    this.floatingDate = createRef();

    this.state = {
      isInfiniteLoading: false,
    }

    this.handleFetchMessages = this.handleFetchMessages.bind(this);
    this.onScroll = _.throttle(this.onScroll.bind(this), 100, {leading: true, trailing: true});
  }

  stickyTimeout = null;

  setStickyHeader = (header) => {
    this.floatingDate.current.innerText = header.innerText;
    this.floatingDate.current.parentNode.classList.add('visible');
    clearTimeout(this.stickyTimeout);
    this.stickyTimeout = setTimeout(() => {
      this.floatingDate.current.parentNode.classList.remove('visible');
    }, 1500);
  }

  $groupMap = {};
  $firstItem;
  stickyIndex = 0;
  lastWrapScrollTop = 0;

  componentDidMount() {
    this.addScrollListener();
    this.addClickListener();
  }

  componentWillUnmount() {
    this.removeScrollListener();
    this.removeClickListener();
  }

  shouldComponentUpdate(nextProps, nextState) {
    const { messages, className, isMessageLoading } = this.props;
    const { isInfiniteLoading } = this.state;

    return (
      messages !== nextProps.messages
      || className !== nextProps.className
      || isMessageLoading !== nextProps.isMessageLoading
      || isInfiniteLoading !== nextState.isInfiniteLoading
    );
  }

  componentWillReceiveProps(nextProps) {
    const { messages } = this.props;
    if (messages !== nextProps.messages) {
      this.$groupMap = {};
      this.stickyIndex = 0;
      this.$firstItem = null;
    }
  }

  fillGroups = (messages) => {
    let messageGroups = [];

    const messagesLastIndex = messages.length - 1;
    if (messages[messagesLastIndex]) {
      messages[messagesLastIndex]["isLast"] = true;
    }

    messages.map((item) => {
      const date = moment(item.date);
      const formattedDate = _.toUpper(date.format('ddd, MMM D, YYYY'));

      if (messageGroups[formattedDate]) {
        const lastIndexInGroup = messageGroups[formattedDate].items.length - 1;

        messageGroups[formattedDate].items[lastIndexInGroup].isLast = false;
        messageGroups[formattedDate].items.push(item);
      } else {
        messageGroups[formattedDate] = {
          header: formattedDate,
          items: [item],
        }
      }
    });

    return messageGroups;
  }

  addScrollListener = () => {
    this.chatRef.current.view.addEventListener('scroll', this.onScroll);
  }

  removeScrollListener = () => {
    this.chatRef.current.view.removeEventListener('scroll', this.onScroll);
  }

  addClickListener = () => {
    this.chatRef.current.view.addEventListener('click', this.props.markMessagesAsRead);
  }

  removeClickListener = () => {
    this.chatRef.current.view.removeEventListener('click', this.props.markMessagesAsRead);
  }

  onScroll() {
    const { hasMore } = this.props;
    const { isInfiniteLoading } = this.state;

    const topScrollPos = this.chatRef.current.view.scrollTop;
    const containerFixedHeight = this.chatRef.current.view.offsetHeight;
    const bottomScrollPos = topScrollPos + containerFixedHeight;

    if ((bottomScrollPos - containerFixedHeight) < this.props.threshold) {
      if (!isInfiniteLoading && hasMore) {
        this.handleFetchMessages();
      }
    }

    const { $groupMap, stickyIndex, lastWrapScrollTop } = this;

    const wrapScrollTop = this.chatRef.current.view.scrollTop;
    const goDown = wrapScrollTop - lastWrapScrollTop > 0;
    this.lastWrapScrollTop = wrapScrollTop;

    const $stickyGroup = $groupMap[stickyIndex];
    if (!$stickyGroup) {
      return;
    }
    const $stickyHeader = $stickyGroup.firstChild;
    const nextIndex = goDown ? stickyIndex + 1 : stickyIndex - 1;
    const $nextGroup = $groupMap[nextIndex];

    const updateHeader = () => {
      if ($nextGroup) {
        const $nextHeader = $nextGroup.firstChild;

        const updateToNextSticky = () => {
          this.setStickyHeader($nextHeader);
          this.stickyIndex = nextIndex;
        }

        const { offsetTop: groupOffsetTop, offsetHeight: groupHeight } = $nextGroup;
        if (goDown) {
          if (wrapScrollTop >= groupOffsetTop) {
            updateToNextSticky();
            return;
          }
        } else {
          if (wrapScrollTop <= groupOffsetTop + groupHeight) {
            updateToNextSticky();
            return;
          }
        }
      }

      this.setStickyHeader($stickyHeader);
    }

    updateHeader();
  }

  handleFetchMessages() {
    const that = this;
    const { updateMessages, messages } = this.props;

    const currentFirstMessage = (this.$groupMap[0]) ? this.$groupMap[0].querySelector('.chat-message') : null;
    const currentFirstMessageId = (currentFirstMessage) ? currentFirstMessage.id : null;
    const currentFirstMessagePos = (currentFirstMessage) ? currentFirstMessage.getBoundingClientRect().top : null;

    this.setState({
      isInfiniteLoading: true
    }, () => {
      const obj = {startFrom: this.props.startFrom, isViewed: false};
      axios.post(MESSAGES_RECEIVE(this.props.clientId), obj)
        .then(res => {
          const {messages: receivedMessages, hasMore} = res.data;
          that.setState({
            isInfiniteLoading: false,
          }, () => {
            updateMessages([...receivedMessages, ...messages], hasMore, this.props.startFrom + receivedMessages.length);
          });
        })
        .then(() => {
          if (currentFirstMessage) {
            const oldFirstMessage = document.getElementById(currentFirstMessageId);
            const newMessagePos = (oldFirstMessage) ? oldFirstMessage.getBoundingClientRect().top : null;
            that.chatRef.current.view.scrollTop = (newMessagePos - currentFirstMessagePos);
          }
        });
    });
  }

  render() {
    const { messages, className, isMessageLoading, messagesEnd } = this.props;
    const { isInfiniteLoading } = this.state;
    const { $groupMap } = this;
    const messageGroups = this.fillGroups(messages);

    return (
      <Fragment>
        <div className="floating-date"><div className="floating-date-inner" ref={this.floatingDate}></div></div>
        <Scrollbars
          className={className}
          ref={this.chatRef}
          onScroll={this.handleChatScroll}
          renderView={props => (
            <div {...props} style={{ ...props.style, overflowX: 'hidden' }} />
          )}
          autoHide={true}
          autoHideTimeout={1500}
        >
          {isMessageLoading ? null : <div className="lazy-loader"><Spinner show={isInfiniteLoading}/></div>}
          {isMessageLoading
            ? <Spinner show={isMessageLoading}/>
            : (
              <Fragment>
                {Object.values(messageGroups).map(({ header, items, key }, index) => (
                  <div
                    className={`${className}-group`}
                    key={key !== undefined ? key : index}
                    ref={$group => {
                      $groupMap[index] = $group;
                    }}
                  >
                    <div className={`${className}-group-header`}>
                      <div className={`${className}-group-header-inner`}>
                        {header}
                      </div>
                    </div>
                    <div
                      className={`${className}-items`}
                    >
                      {items.map((message) => (
                        <Message
                          message={message}
                          key={`message_${message.id}`}
                        />
                      ))}
                    </div>
                  </div>
                ))}
              </Fragment>
            )
          }
          {messagesEnd}
        </Scrollbars>
      </Fragment>
    )
  }
}
