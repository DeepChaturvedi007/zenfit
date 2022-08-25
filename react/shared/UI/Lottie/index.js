import React, {Fragment, useEffect} from 'react';
import Lottie from 'react-lottie';

const ZFLottie = (props) => {
    const {
        lottie,
        height = 400,
        width = 400,
        loop = true,
        autoplay = true,
        delay = 0,
        speed = 1,
    } = props;

    const [play, setPlay] = React.useState(false);

    useEffect(() => {
        delay !== 0 && autoplay === false
            ? setTimeout(() => setPlay(true), delay)
            : setPlay(true);
    });

    const defaultOptions = {
        loop: loop,
        delay: delay,
        autoplay: autoplay,
        animationData: lottie,
        rendererSettings: {
            preserveAspectRatio: 'xMidYMid slice'
        }
    };

    return (
        <Fragment>
            <Lottie
                speed={speed}
                isStopped={!play}
                options={defaultOptions}
                height={height}
                width={width}
            />
        </Fragment>
    )
}

export default ZFLottie;


