import React, { Component } from 'react';

import MicIcon from '@material-ui/icons/Mic';

import Button from '../button';
import EmotionLineIcon from '../icons/EmotionLine';
import CaptureVideo from '../capture-video';
import { Tooltip } from 'react-tippy';
import VideoLineIcon from '../icons/VideoLine';
import ContentEditable from './content-editable';
import _ from 'lodash';
import EmojiPicker from 'emoji-picker-react';
import JSEMOJI from 'emoji-js';
import ModalChatTemplates from "../../../modals/modal-chat-templates";
import { browser, isMobileDevice } from "../../utils";
import VoiceRecorder from '../voice-recorder';

//emoji set up
let jsemoji = new JSEMOJI();
// set the style to emojione (default - apple)
jsemoji.img_set = 'emojione';

// convert colons explicitly to unicode
jsemoji.replace_mode = 'unified';
jsemoji.allow_native = true;

// set the storage location for all emojis
jsemoji.img_sets.emojione.path = 'https://cdn.jsdelivr.net/emojione/assets/3.0/png/32/';

export default class SendField extends Component {
  isSending = false;

  constructor(props) {
    super(props);

    this.contentInput = React.createRef();
    this.state = {
      html: '',
      showEmojis: false,
      selectedVideo: null,
      selectVoice: null,
      voiceRecorderOpen: false,
      recodedVoice: null
    };
    this.handleChange = this.handleChange.bind(this);
    this.handleSend = this.handleSend.bind(this);
    this.openVoiceRecorder = this.openVoiceRecorder.bind(this);
    this.closeVoiceRecorder = this.closeVoiceRecorder.bind(this);
  }

  componentWillReceiveProps(nextProps) {
    if (!nextProps.isSending && this.isSending) {
      this.setState({ html: '', selectedVideo: null, selectVoice: null });

      this.contentInput.current.flush();
      this.isSending = false;
    }
  }

  onClickEmoji = (code, e) => {
    let emoji = jsemoji.replace_colons(`:${e.name}:`);
    const contentInput = this.contentInput.current || {};
    if (contentInput.paste) {
      contentInput.paste(emoji)
    }
    this.setState({
      showEmojis: false,
    });
  };

  selectVideo = (file) => {
    this.setState({ selectedVideo: file }, () => {
      this.handleSend();
    });
  };
  openVoiceRecorder = () => {
    this.setState({voiceRecorderOpen: true})
  }

  closeVoiceRecorder = () => {
    this.setState({voiceRecorderOpen: false})
  }

  selectVoice = (file) => {
    this.setState({ selectVoice: file, voiceRecorderOpen: false }, () => {
      this.handleSend();
    });
  }

  render() {
    const { html, selectedVideo, voiceRecorderOpen } = this.state;
    const { isMobile, isSending, className, selectedConversation } = this.props;
    const fieldClassName = className ? className : '';
    const btnTitle = isMobile
      ? (
        isSending
          ? <i className='fa fa-spinner fa-spin' />
          : <i className="material-icons material-design-icons">send</i>
      )
      : (
        isSending
          ? <span><i className='fa fa-spinner fa-spin' /> Sending</span>
          : 'Send'
      );

    let video = null;

    if (this.props.video) {
      video = !this.state.selectedVideo &&
        <CaptureVideo isMobile={isMobile} selectVideo={this.selectVideo} />;
    }
    let voiceRecorder =
      <button className='btn-templates' onClick={this.openVoiceRecorder}>
        <i className="material-icons">mic</i>
      </button>;
    // Disable Video Capture for unsupported browsers
    const isMediaRecordSupport = !!window.MediaRecorder;
    if (!isMobile && (!isMediaRecordSupport || browser.name === 'Safari')) {
      //browser does not support video
      video =
        <Tooltip
          title="This browser does not support video"
          position="top"
          trigger="mouseenter"
          size="big"
        >
          <button
            className='btn-video disabled'>
            <VideoLineIcon />
          </button>
        </Tooltip>
    }

    // Disable Voice Recorder for unsupported browsers
    if (!isMobile && (!isMediaRecordSupport || browser.name === 'Safari')) {
      //browser does not support voice
      voiceRecorder =
        <Tooltip
          title="This browser does not support voice"
          position="top"
          trigger="mouseenter"
          size="big"
        >
          <button className='btn-video disabled'>
            <i className="material-icons">mic</i>
          </button>
        </Tooltip>
    }

    const emoji = this.state.showEmojis ? (
      <div className="emoji-container" style={{ display: this.state.showEmojis ? '' : 'none' }}>
        <EmojiPicker onEmojiClick={this.onClickEmoji} />
      </div>
    ) : null;

    const clientId = (selectedConversation) ? selectedConversation.clientId : null;
    const defaultMessages = <ModalChatTemplates
      clientId={clientId}
      label={(
        <button className='btn-templates'>
          <i className="material-icons">grading</i>
        </button>
      )}
      inputRef={this.contentInput}
    />;

    return (
      voiceRecorderOpen ? (
        <VoiceRecorder
          onSelectVoice={this.selectVoice}
          onClose={this.closeVoiceRecorder}
        />
      ) : (
        <div className={`chat-search ${fieldClassName}`}>
          <ContentEditable
            placeholder="Type a message..."
            className="form-control"
            content={html}
            onChange={this.handleChange}
            ref={this.contentInput}
          />
          <div className="chat-search-actions">
            {voiceRecorder}
            {video}
            {emoji}
            {isMobile ? '' : (
              <button onClick={this.handleToggleEmoji} className="btn-smiles">
                <EmotionLineIcon />
              </button>
            )}
            {defaultMessages}
          </div>
          <Button handleClick={this.handleSend} btnTitle={btnTitle} disabled={!(html || selectedVideo)} />
        </div>
      )
    );
  }

  handleToggleEmoji = () => {
    this.setState({ showEmojis: !this.state.showEmojis });
  };

  handleSend() {
    const { html, selectedVideo, selectVoice } = this.state;
    if (html || selectedVideo || selectVoice) {
      const { sendMessage, conversations: oldConversations, selectedConversation, reorderConversations } = this.props;

      if (oldConversations) {
        const conversations = [...oldConversations];
        const index = _.findIndex(conversations, selectedConversation);
        conversations[index].message = html;
        conversations[index].sentAt = new Date();
        const reorderedConversations = conversations.move(
          index,
          0
        );
        reorderConversations && reorderConversations(reorderedConversations);
      }

      this.isSending = true;
      sendMessage && sendMessage(html, selectedVideo, selectVoice);
    }
  }

  handleChange(value) {
    this.setState({ html: value });
    _.isFunction(this.props.onType) && this.props.onType();
  }
}

SendField.defaultProps = {
  video: true,
};
