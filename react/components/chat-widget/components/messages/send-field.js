import React, { Component } from 'react';
import Button from '../button';
import CaptureVideo from '../capture-video';
import VoiceRecorder from '../voice-recorder';
import ContentEditable from './content-editable';
import _ from 'lodash';
import EmojiPicker from 'emoji-picker-react';
import JSEMOJI from 'emoji-js';
import SendIcon from "../icons/SendIcon";
import EmojiIcon from "../icons/EmojiIcon";
import VisibilityOutlinedIcon from '@material-ui/icons/VisibilityOutlined';
import DoneOutlinedIcon from '@material-ui/icons/DoneOutlined';
import MicIcon from '@material-ui/icons/Mic';
import DropOptions from "./DropOptions";
import { Tooltip } from "react-tippy";
import { browser, isMobileDevice } from "../../utils";
import ModalChatTemplates from "../../../modals/modal-chat-templates";

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
      html: localStorage.unSentMsg === undefined ?
          "" :
          parseInt(localStorage.unSentClientId) == props.clientId ? localStorage.unSentMsg.replace(/\n/g, "<br>") : '',
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
    this.selectVoice = this.selectVoice.bind(this);
  }
  componentWillReceiveProps(nextProps) {
    if (!nextProps.isSending && this.isSending) {
      this.setState({ html: '', selectedVideo: null, selectVoice: null });

      this.props.contentInput.current.flush();
      this.isSending = false;
    }
    if (nextProps.clientId !== this.props.clientId || this.state.html !== localStorage.unSentMsg) {
      this.setState({
        html: localStorage.unSentMsg === undefined ?
          "" :
          parseInt(localStorage.unSentClientId) == nextProps.clientId ? localStorage.unSentMsg : '',
      })
    }
  }

  onClickEmoji = (code, e) => {
    let emoji = jsemoji.replace_colons(`:${e.name}:`);
    const contentInput = this.props.contentInput.current || {};
    if (contentInput.paste) {
      localStorage.unSentMsg === undefined ? localStorage.setItem('unSentMsg', emoji) : localStorage.setItem('unSentMsg', localStorage.unSentMsg + emoji);
      localStorage.setItem('unSentClientId', this.props.clientId);
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
      console.log(file)
      this.handleSend();
    });
  }

  render() {

    const { html, selectedVideo, voiceRecorderOpen } = this.state;
    const { isSending, className, clientId, locale, markUnreadConversation, markConversationDone } = this.props;
    const fieldClassName = className ? className : '';
    const btnTitle = (isSending)
      ? <i className='fa fa-spinner fa-spin' />
      : <SendIcon className="arrow-icon" />
      ;

    let video = null;

    if (this.props.video) {
      video = !this.state.selectedVideo &&
        <CaptureVideo isMobile={isMobileDevice} selectVideo={this.selectVideo} />;
    }
    let recoderVoice = <span className="drop-options-list-item" onClick={this.openVoiceRecorder}>
        <i className="material-icons" >
          <MicIcon style={{ fontSize: 22 }} />
        </i>
        <span>Send voice chat</span>
      </span>;
    const unreadMark = <span className="drop-options-list-item" onClick={markUnreadConversation}>
      <i className="material-icons" >
        <VisibilityOutlinedIcon style={{ fontSize: 22 }} />
      </i>
      <span>Mark as unread</span>
    </span>;
    const doneMark = <span className="drop-options-list-item" onClick={markConversationDone}>
      <i className="material-icons" >
        <DoneOutlinedIcon style={{ fontSize: 22 }} />
      </i>
      <span>Mark as done</span>
    </span>;
    // Disable Video Capture for unsupported browsers
    const isMediaRecordSupport = !!window.MediaRecorder;

    if (!isMobileDevice && (!isMediaRecordSupport || browser.name === 'Safari')) {
      const warningTitle = (browser.name === 'Safari')
        ? 'Please use the Chrome / Firefox web browser'
        : 'This browser does not support video';
      video =
        <Tooltip
          title={warningTitle}
          position="top"
          trigger="mouseenter"
          size="big"
        >
          <span className="drop-options-list-item disabled"><i className="material-icons">video_call</i><span>Video</span></span>
        </Tooltip>
    }
    if (!isMobileDevice && (!isMediaRecordSupport || browser.name === 'Safari')) {
      const warningTitle = (browser.name === 'Safari')
        ? 'Please use the Chrome / Firefox web browser'
        : 'This browser does not support voice';
      recoderVoice =
        <Tooltip
          title={warningTitle}
          position="top"
          trigger="mouseenter"
          size="big"
        >
          <span className="drop-options-list-item disabled">
            <i className="material-icons">
              <MicIcon style={{ fontSize: 22 }} />
            </i>
            <span>Voice</span>
          </span>
        </Tooltip>
    }

    const emoji = this.state.showEmojis ? (
      <div className="emoji-container" style={{ display: this.state.showEmojis ? '' : 'none' }}>
        <EmojiPicker onEmojiClick={this.onClickEmoji} />
      </div>
    ) : null;
    const defaultMessages = <ModalChatTemplates
      clientId={clientId}
      label={<span className="drop-options-list-item"><i className="material-icons">grading</i><span>Templates</span></span>}
      inputRef={this.props.contentInput}
      locale={locale}
      defaultMessageType={this.props.messageType ? this.props.messageType.id : 15}
    />;

    return (
      voiceRecorderOpen ? (
        <VoiceRecorder
          onSelectVoice={this.selectVoice}
          onClose={this.closeVoiceRecorder}
        />
      ) : (
        <form className={`chat-search ${fieldClassName}`} method="POST" onSubmit={(event) => {
          event.preventDefault();
          this.handleSend();
        }}>
          <div className="chat-search-actions">
            <DropOptions
              items={[recoderVoice, doneMark, unreadMark, video, defaultMessages]}
            />
            {emoji}
            <button type="button" onClick={this.handleToggleEmoji} className="btn-smiles">
              <EmojiIcon className="flex-center" />
            </button>
          </div>
          <ContentEditable
            placeholder="Type a message..."
            className="form-control"
            content={html}
            onChange={this.handleChange}
            ref={this.props.contentInput}
            isOpen={this.props.isOpen}
          />
          <div className="flex-center">
            <Button btnTitle={btnTitle} disabled={!(html || selectedVideo)} />
          </div>
        </form>
      )
    );
  }

  handleToggleEmoji = () => {
    this.setState({ showEmojis: !this.state.showEmojis });
  };
  handleSend() {
    const { html, selectedVideo, selectVoice } = this.state;
    if (html || selectedVideo || selectVoice) {
      const { sendMessage } = this.props;
      this.isSending = true;
      sendMessage && sendMessage(html, selectedVideo, selectVoice);
      localStorage.removeItem('unSentMsg')
    }
  }
  handleChange(value) {
    this.setState({ html: value });
    if (this.props.clientId) {
      localStorage.setItem('unSentClientId', this.props.clientId)
      localStorage.setItem('unSentMsg', value);
    }
    _.isFunction(this.props.onType) && this.props.onType();
  }
}

SendField.defaultProps = {
  video: true,
};
