import React, { useEffect, useState } from 'react';

const Timer = (props) => {
    const {stop, onStopRecord} = props;
    const [time, setTime] = useState(0);
    
    const min = parseInt(time / 60) < 10 ? '0'+parseInt(time / 60) : parseInt(time / 60);
    const sec = time % 60 < 10 ? '0'+time % 60 : time % 60;
    
    useEffect(() => {
        let timer = null;
        if(!stop) {
            timer = window.setTimeout(() => {
                setTime(time+1)
            }, 1000)
        }
        if(stop && timer) {
            clearTimeout(timer)
        }
        if(time === 300) {
            onStopRecord();
        }
        return () => {
            window.clearTimeout(timer)
        };
    }, [time, stop])
    return(
        <div className="timer-content">
            <span>{min}:{sec}</span>
        </div>
    )
}

export default Timer;