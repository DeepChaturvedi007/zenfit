import React, {Component, Fragment, createRef} from 'react';
import PropTypes from 'prop-types';
import BaseReactMediaRecorder from 'react-media-recorder';

export default class ReactMediaRecorder extends BaseReactMediaRecorder {
  initMediaRecorder = stream => {
    const { mediaRecorderOptions } = this.props;
    const recorder = new MediaRecorder(stream, mediaRecorderOptions);

    recorder.ondataavailable = this.onRecordingActive;
    recorder.onstop = this.onRecordingStop;
    recorder.onerror = () => {
      this.setState({ status: 'recorder_error' })
    };

    return recorder;
  };

  onRecordingStop = () => {
    const blob = new Blob(this.chunks, this.blobPropertyBag);
    const url = URL.createObjectURL(blob);
    if (this.props.whenStopped) {
      this.props.whenStopped(url, blob);
    }
    this.setState({ mediaBlob: url, status: "stopped" });
  };
}

ReactMediaRecorder.defaultProps = {
  ...BaseReactMediaRecorder.defaultProps,
  mediaRecorderOptions: null
};

ReactMediaRecorder.propTypes = {
  ...BaseReactMediaRecorder.propTypes,
  mediaRecorderOptions: PropTypes.object
};
