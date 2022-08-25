import React, { useState, useEffect } from 'react';

import MicIcon from '@material-ui/icons/Mic';
import PlayArrowIcon from '@material-ui/icons/PlayArrow';
import PauseIcon from '@material-ui/icons/Pause';


const Timer = (props) => {
    const {stopRecord, onStopRecord, onPauseAudio, onPlayAudio, playing} = props;
    const [time, setTime] = useState(0);
    
    const min = parseInt(time / 60) < 10 ? '0'+parseInt(time / 60) : parseInt(time / 60);
    const sec = time % 60 < 10 ? '0'+time % 60 : time % 60;
    const actionButton = !stopRecord ? <MicIcon /> :
    playing ? <PauseIcon /> : <PlayArrowIcon />;

    const action = () => {
        if(!stopRecord) {
            onStopRecord()
        }
        else {
            if(playing) {
                onPauseAudio();
            }
            else {
                onPlayAudio();
            }
        }
    }
    useEffect(() => {
        let timer = null;
        if(!stopRecord) {
            timer = window.setTimeout(() => {
                setTime(time+1)
            }, 1000)
        }
        if(stopRecord && timer) {
            clearTimeout(timer)
        }
        if(time === 300) {
            onStopRecord();
        }
        return () => {
            window.clearTimeout(timer)
        };
    }, [time, stopRecord])
    return(
        <div className={`timer-content ${stopRecord ? 'stop' : ''}`} onClick={action}>
            {actionButton}
            <span>{min}:{sec}</span>
        </div>
    )
}

export default Timer;