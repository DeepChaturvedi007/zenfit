import React, { useState, useEffect } from 'react';

import IconButton from '@material-ui/core/IconButton';

import MicIcon from '@material-ui/icons/Mic';
import CloseIcon from '@material-ui/icons/Close';
import SendIcon from '@material-ui/icons/Send';
import DeleteIcon from '@material-ui/icons/Delete';

import Timer from './timer';

import {
    startRecording,
    getFile,
    getURL,
    clearRecord
} from '../recorder-util';

let audio = null;

const VoiceRecorder = (props) => {
    const {
        onSelectVoice,
        onClose
    } = props
    const [startRecord, setStartRecord] = useState(false);
    const [stopRecord, setStopRecord] = useState(false);
    const [playing, setPlaying] = useState(false);

    const onRecord = () => {
        setStartRecord(true);
        startRecording();
    }
    const onRemoveVoice = () => {
        onClose();
        clearRecord();
        audio.pause();
    }
    const sendMessage = async () => {
        const file = await getFile();
        onSelectVoice(file);
        clearRecord();
        audio.pause();
    }
    const onStopRecord = () => {
        setStopRecord(true)
        // playAudio()
    }
    const onPlayAudio = async () => {
        const url = await getURL();
        audio = new Audio(url);
        audio.addEventListener('ended', () => setPlaying(false));
        audio.play();
        setPlaying(true);
    }
    const onPauseAudio = () => {
        audio.pause();
        audio.currentTime = 0;
        audio.removeEventListener('ended', () => setPlaying(false));
        setPlaying(false);
    }
    return (
        <div className="voice-recorder-container">
            {startRecord ? (
                <Timer
                    onStopRecord={onStopRecord}
                    stopRecord={stopRecord}
                    playing={playing}
                    onPlayAudio={onPlayAudio}
                    onPauseAudio={onPauseAudio}
                />
            ) : (
                <IconButton
                    aria-label="recorder"
                    size="small"
                    classes={{
                        root: 'recoder-action recorder-btn'
                    }}
                    onClick={onRecord}
                >
                    <MicIcon />
                </IconButton>
            )}
            <div style={{ flex: 1 }} />
            {startRecord ? (
                <IconButton
                    aria-label="recorder"
                    size="small"
                    classes={{
                        root: 'recoder-action remove-btn'
                    }}
                    onClick={onRemoveVoice}
                >
                    <DeleteIcon />
                </IconButton>
            ):(
                <IconButton
                    aria-label="recorder"
                    size="small"
                    classes={{
                        root: 'recoder-action close-btn'
                    }}
                    onClick={onClose}
                >
                    <CloseIcon />
                </IconButton>
            )}
            <IconButton
                aria-label="recorder"
                size="small"
                classes={{
                    root: 'recoder-action send-btn'
                }}
                disabled = {!startRecord}
                onClick={sendMessage}
            >
                <SendIcon />
            </IconButton>
        </div>
    )
}

export default VoiceRecorder;