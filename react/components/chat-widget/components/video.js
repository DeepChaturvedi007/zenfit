import React from 'react';
import ReactPlayer from 'react-player'

const Video = ({ url, scrollToBottom }) => (
  <div className='player-wrapper'>
    <ReactPlayer
      className='react-player'
      url={url}
      onReady={scrollToBottom}
      width='100%'
      height={url.includes('.mp4') ? 'auto' : '40px'}
      controls
    />
  </div>
);

export default Video;
