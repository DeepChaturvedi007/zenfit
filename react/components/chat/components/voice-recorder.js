import React, { useEffect, useState } from 'react';

import IconButton from '@material-ui/core/IconButton';

import MicIcon from '@material-ui/icons/Mic';
import PlayArrowIcon from '@material-ui/icons/PlayArrow';
import PauseIcon from '@material-ui/icons/Pause';
import CloseIcon from '@material-ui/icons/Close';
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
    const {onSelectVoice, onClose} = props;
    const [stopRecord, setStopRecord] = useState(false);
    const [playing, setPlaying] = useState(false);

    const handleRecord = async () => {
        if(!stopRecord) {
            setStopRecord(true);
            const url = await getURL();
            audio = new Audio(url);
            audio.addEventListener('ended', () => setPlaying(false));
        }
        else {
            if(playing) {
                audio.pause();
                audio.currentTime = 0;
                audio.removeEventListener('ended', () => setPlaying(false));
            }
            else {
                audio.play();
            }
            setPlaying(!playing)
        }
    }

    const handleSend = async () => {
        const file = await getFile();
        onSelectVoice(file);
        clearRecord();
        audio.pause();
    }

    const deleteVoice = () => {
        onClose();
        clearRecord();
        audio.pause();
    }

    const actionIcon = !stopRecord ? <MicIcon /> :
        playing ? <PauseIcon /> : <PlayArrowIcon />;
    
    useEffect(() => {
        startRecording()
    }, [])
    return(
        <div className="chat-voice-cotent">
            <div style={{flex: 1}} />
            <div className={`recorder-icon ${stopRecord ? 'recorded' : 'recording'}`} onClick={handleRecord}>
                {actionIcon}
                <Timer stop={stopRecord} onStopRecord={handleRecord}/>
            </div>
            {stopRecord ? (
                <IconButton
                    aria-label="recorder"
                    size="small"
                    classes={{
                        root: 'recoder-action delete-btn'
                    }}
                    onClick={deleteVoice}
                >
                    <DeleteIcon />
                </IconButton>
            ) : (
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
            <button className="btn btn-default btn-upper send-voice" onClick={handleSend} disabled={!stopRecord}>
                send
            </button>
        </div>
    )
}

export default VoiceRecorder;