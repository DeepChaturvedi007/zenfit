import React, { useState, useEffect } from "react";

import IconButton from '@material-ui/core/IconButton'
import PlayArrowIcon from '@material-ui/icons/PlayArrow';
import PauseIcon from '@material-ui/icons/Pause';

import {PLAY_BUTTON_URL, PAUSE_BUTTON_URL, WAVE_URL} from '../constants'

const useAudio = url => {
  const [audio] = useState(new Audio(url));
  const [playing, setPlaying] = useState(false);

  const toggle = () => setPlaying(!playing);

  useEffect(() => {
      playing ? audio.play() : audio.pause();
    },
    [playing]
  );

  useEffect(() => {
    audio.addEventListener('ended', () => setPlaying(false));
    return () => {
      audio.removeEventListener('ended', () => setPlaying(false));
    };
  }, []);

  return [playing, toggle];
};

const VoicePlayer = ({ url }) => {
  const [playing, toggle] = useAudio(url);

  return (
    <div className={`voice-content ${playing ? 'playing' : ''}`}>
      <div
        className="play-button"
        onClick={toggle}
      >
        {playing ? (
          <img src={PAUSE_BUTTON_URL} alt="pause" />
        ) : (
          <img src={PLAY_BUTTON_URL} alt="play" />
        )}
      </div>
      <div style={{flex: 1}}>
        <img src={WAVE_URL} className="wave-image" alt="wave" />
      </div>
    </div>
  );
};

export default VoicePlayer;