import React, { Component, Fragment, createRef } from 'react';
import PropTypes from 'prop-types';
import Modal from 'react-modal';
import { ReactMediaRecorder } from 'react-media-recorder';
import VideoLineIcon from '../../chat/components/icons/VideoLine';
import CloseLineIcon from '../../chat/components/icons/CloseLine';
import PauseCircleLineIcon from '../../chat/components/icons/PauseCircleLine';
import PauseCircleFillIcon from '../../chat/components/icons/PauseCircleFill';
import RecordCircleLineIcon from '../../chat/components/icons/RecordCircleLine';
import { getFrameRates, getVideoResolutions } from '../utils';
import { Tooltip } from 'react-tippy';

const MediaRecorder = window.MediaRecorder;
const MediaRecorderOptions = {};
if (MediaRecorder && typeof MediaRecorder.isTypeSupported == 'function') {
  if (MediaRecorder.isTypeSupported('video/webm;codecs=vp9')) {
    MediaRecorderOptions.mimeType = 'video/webm;codecs=vp9';
  } else if (MediaRecorder.isTypeSupported('video/webm;codecs=h264')) {
    MediaRecorderOptions.mimeType = 'video/webm;codecs=h264';
  } else if (MediaRecorder.isTypeSupported('video/webm')) {
    MediaRecorderOptions.mimeType = 'video/webm';
  } else {
    console.error('Browser is not support video recording');
  }
}

const modalStyles = {
  content: {
    backgroundColor: 'rgba(0,0,0,.65)',
    top: '0',
    left: '0',
    right: '0',
    bottom: '0',
  },
  overlay: {
    // in order to overlap other modals
    zIndex: 2500,
  },
};

if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
  console.warn(
      'getUserMedia() must be run from a secure origin: https or localhost.\nPlease change the protocol to https.',
  );
}

/**
 * @param {string} status
 * @param {string} needed
 * @returns {boolean}
 * @private
 */
const isStatus = (status, needed) => status === needed;

/**
 * Format seconds as a time string, H:MM:SS or M:SS
 * Supplying a guide (in seconds) will force a number of leading zeros
 * to cover the length of the guide
 * @param  {Number} seconds Number of seconds to be turned into a string
 * @param  {Number} guide   Number (in seconds) to model the string after
 * @return {String}         Time formatted as H:MM:SS or M:SS
 * @private
 */
export function formatTime(seconds, guide) {
  // Default to using seconds as guide
  guide = guide || seconds;
  var s = Math.floor(seconds % 60),
    m = Math.floor((seconds / 60) % 60),
    h = Math.floor(seconds / 3600),
    gm = Math.floor((guide / 60) % 60),
    gh = Math.floor(guide / 3600);

  // handle invalid times
  if (isNaN(seconds) || seconds === Infinity) {
    // '-' is false for all relational operators (e.g. <, >=) so this setting
    // will add the minimum number of fields specified by the guide
    h = m = s = '-';
  }

  // Check if we need to show hours
  h = h > 0 || gh > 0 ? h + ':' : '';

  // If hours are showing, we may need to add a leading zero.
  // Always show at least one digit of minutes.
  m = ((h || gm >= 10) && m < 10 ? '0' + m : m) + ':';

  // Check if leading zero is need for seconds
  s = s < 10 ? '0' + s : s;

  return h + m + s;
}

const Resolutions = new Map([
  ['480p', '640x480'],
  ['720p', '1280x720'],
  ['1080p', '1920x1080'],
  ['4K', '3840x2160'],
]);

const Bitrates = new Map([
  ['1GB bps', 8000000000],
  ['100MB bps', 800000000],
  ['1MB bps', 8000000],
  ['100KB bps', 800000],
  ['1KB bps', 8000],
  ['100B bps', 800],
]);

export default class CaptureVideo extends Component {
  constructor(props) {
    super(props);

    this.tid = 0;
    this.input = createRef();
    this.video = createRef();
    this.recorder = null;
    this.previewStream = null;
    this.state = {
      showRecorder: false,
      timeRecorded: 0,
      time: 0,
      recording: false,
    };
  }

  maxVideoMessageLength = 60 * 5; // in seconds

  componentDidUpdate() {
    const { timeRecorded } = this.state;

    if (timeRecorded >= this.maxVideoMessageLength) {
      this.handlePause();
    }
  }

  componentWillUnmount() {
    this.flush();
  }

  render() {
    const { showRecorder, resolution, frameRate, videoBitrates, recording } = this.state;

    let mediaConstraints = {
      video: true,
      audio: true,
    };

    mediaConstraints = getVideoResolutions(
      mediaConstraints,
      resolution === 'default' ? resolution : Resolutions.get(resolution),
    );
    mediaConstraints = getFrameRates(mediaConstraints, frameRate);

    const videoBitsPerSecond =
      videoBitrates === 'default' ? null : Bitrates.get(videoBitrates);
    return (
      <Fragment>
        <input
          onChange={this.onFileInputChange}
          style={{ display: 'hidden' }}
          type='file'
          accept='video/*'
          capture='camcorder'
          ref={this.input}
        />
        <Modal
          appElement={document.body}
          isOpen={showRecorder}
          onAfterOpen={this.onModalOpen}
          onRequestClose={this.onModalClose}
          style={modalStyles}
          contentLabel='Video Message'
        >
          <ReactMediaRecorder
            audio
            video={mediaConstraints.video}
            mediaRecorderOptions={MediaRecorderOptions}
            blobPropertyBag={{
              videoBitsPerSecond,
              type: MediaRecorderOptions.mimeType
                ? MediaRecorderOptions.mimeType
                : 'video/mp4',
            }}
            onStop={this.onRecordingStop}
            render={(renderProps) => {
              if (
                this.recorder &&
                !this.previewStream &&
                renderProps.previewStream &&
                renderProps.previewStream.active
              ) {
                this.setVideoSource(renderProps.previewStream);
                this.previewStream = renderProps.previewStream;
              }
              this.recorder = renderProps;
              const { error, status, stopRecording, pauseRecording, resumeRecording } =
                renderProps;
              return (
                <div className='chat-camera'>
                  {isStatus(error, 'no_specified_media_found') ? (
                    <div className='chat-camera-body'>
                      <h4 className='chat-camera-title'>Enable Camera and Mic</h4>
                      <p className='chat-camera-caption'>
                        Please provide us access to your camera and mic,
                        <br />
                        which is required for Video Messages
                      </p>
                    </div>
                  ) : (
                    <div className='chat-camera-preview'>
                      <video
                        onTimeUpdate={this.onTimeUpdate}
                        ref={this.video}
                        muted={recording}
                        autoPlay
                      />
                      <header className='chat-camera-header'>
                        {this.renderTime(status)}
                      </header>
                      <footer className='chat-camera-footer'>
                        {this.renderControls(status, {
                          stopRecording,
                          pauseRecording,
                          resumeRecording,
                        })}
                      </footer>
                    </div>
                  )}
                  <button
                    className='chat-camera-close'
                    onClick={this.handleClose}
                  >
                    <CloseLineIcon />
                  </button>
                </div>
              );
            }}
          />
        </Modal>
        <button className='btn-video' onClick={this.handleCapture}>
          <VideoLineIcon />
        </button>
      </Fragment>
    );
  }

  /**
   *
   * @param {string} status
   * @param {Object<string,Function>} actions
   */
  renderControls(status, actions) {
    const { timeRecorded } = this.state;

    if (status === 'idle' || (status === 'stopped' && !timeRecorded)) {
      return (
        <button
          className='chat-camera-btn --primary'
          onClick={this.handleRecord}
        >
          Start Recording (5 min max)
        </button>
      );
    }

    return status === 'stopped' ? (
      <Fragment>
        <button
          className='chat-camera-btn --icon'
          onClick={this.handleReset}
          title='Reset Video'
        >
          <RecordCircleLineIcon />
        </button>
        <button
          className='chat-camera-btn --primary'
          onClick={this.handleAttach}
        >
          Send Video Message
        </button>
      </Fragment>
    ) : (
      <Fragment>
        {status === 'paused' ? (
          timeRecorded >= this.maxVideoMessageLength ? (
            <Tooltip
              title={'Maximum video message length exceeded'}
              position='top'
              trigger='mouseenter'
              size='big'
              className='camera-btn-tooltip'
            >
              <button
                className='chat-camera-btn --icon disabled'
                title='Resume'
              >
                <PauseCircleFillIcon />
              </button>
            </Tooltip>
          ) : (
            <button
              className='chat-camera-btn --icon'
              onClick={this.handleResume}
              title='Resume'
            >
              <PauseCircleFillIcon />
            </button>
          )
        ) : (
          <button
            className='chat-camera-btn --icon'
            onClick={this.handlePause}
            title='Pause'
          >
            <PauseCircleLineIcon />
          </button>
        )}
        <button className='chat-camera-btn --danger' onClick={this.handleStop}>
          Stop Recording
        </button>
      </Fragment>
    );
  }

  /**
   * @param {string} status
   * @returns {null|*}
   */
  renderTime(status) {
    const { current: video } = this.video;
    const { timeRecorded } = this.state;

    if (!video || status === 'stopped') {
      return null;
    }

    let time = [];

    switch (status) {
      case 'paused':
      case 'recording':
        time.push(formatTime(timeRecorded));

        if (status === 'paused') {
          time.push('Paused');
        }
        break;
      default:
        time.push(formatTime(video.currentTime));
        time.push(formatTime(timeRecorded));
    }

    return (
      <div className={`chat-camera-time --${status}`}>{time.join(' / ')}</div>
    );
  }

  handleCapture = () => {
    if (this.props.isMobile) {
      this.input.current.click();
    } else {
      this.setState({
        showRecorder: true,
      });
    }
  };

  handleClose = () => {
    this.setState(
      {
        showRecorder: false,
      },
      () => {
        this.flush();
      },
    );
  };

  /*
  * Enable audio before start, so no delay for audio
  * Discovered issue is related to audio devices that isn't ready to use the audio
  * */
  prepareAudio = () => {
    navigator.mediaDevices.getUserMedia({audio : true}).then(() => {}).catch(err => {
      err.includes("NotFoundError: Requested device not found")
          ? console.log("Mic not detected")
          : console.log("Error recording audio")
    })
  }

  handleRecord = () => {
    const recorder = this.recorder;
    if (!recorder) {
      return;
    }
    this.prepareAudio()
    setTimeout(() => {
      recorder.startRecording();
      this.setState({ recording: true });
      this.recordTimeTick();
    },1000)
  };

  handlePause = () => {
    const recorder = this.recorder;
    recorder.pauseRecording();
    this.tid = clearInterval(this.tid);
  };

  handleResume = () => {
    const recorder = this.recorder;
    recorder.resumeRecording();
    this.recordTimeTick();
  };

  handleAttach = () => {
    this.props.selectVideo(this.file);
    this.handleClose();
  };

  handleReset = () => {
    this.flush();
    this.handleRecord();
  };

  handleStop = () => {
    const recorder = this.recorder;
    recorder.stopRecording();
    this.tid = clearInterval(this.tid);
    this.previewStream = null;
  };

  onModalOpen = () => {};

  onModalClose = () => {};

  /**
   * @param {Event} e
   */
  onFileInputChange = (e) => {
    this.props.selectVideo(e.target.files[0]);
  };

  /**
   * @param {string} url
   * @param {Blob} blob
   *
   * @returns {Promise<void>}
   */
  onRecordingStop = (url, blob) => {
    this.setState({ recording: false });
    this.file = blob;
    this.setVideoSource(url);
  };

  onTimeUpdate = () => {
    if (this.tid) {
      return;
    }

    this.setState({
      time: this.video.current.currentTime,
    });
  };

  recordTimeTick = () => {
    if (this.tid) {
      return this.setState({
        timeRecorded: this.state.timeRecorded + 1,
      });
    }

    this.tid = setInterval(this.recordTimeTick, 1000);
  };

  /**
   * @param {MediaStream|string} source
   * @param {boolean} autoplay
   */
  setVideoSource = (source, autoplay = false) => {
    const { current: video } = this.video;

    if (!video) {
      return;
    }

    let isStream = source instanceof MediaStream;

    video.srcObject = isStream ? source : undefined;
    video.src = isStream ? '' : source;

    this.setVideoTime(0);
    if (autoplay) {
      video.play();
    }
  };

  /**
   * @param {number} percent
   * @param {boolean} isPercent
   */
  setVideoTime(percent, isPercent = false) {
    if (isPercent && percent > 100) {
      return;
    }

    const { current: video } = this.video;
    let time = isPercent ? (percent * video.duration) / 100 : percent;

    if (video.fastSeek) {
      video.fastSeek(time);
    } else {
      video.currentTime = time;
    }
  }

  flush = () => {
    const recorder = this.recorder;
    if (recorder) {
      recorder.stopRecording();
    }

    this.file = null;
    this.tid = clearInterval(this.tid);

    this.setState({
      time: 0,
      timeRecorded: 0,
    });
  };
}

CaptureVideo.defaultProps = {
  isMobile: false,
  videoBitrates: 'default',
  resolution: '480p',
  frameRate: 30,
  constraints: {
    audio: true,
    video: true,
  },
};

CaptureVideo.propTypes = {
  bitrates: PropTypes.oneOf(['default', ...Bitrates.keys()]),
  resolution: PropTypes.oneOf(['default', ...Resolutions.keys()]),
  frameRate: PropTypes.oneOf(['default', 5, 15, 24, 30, 60]),
};
