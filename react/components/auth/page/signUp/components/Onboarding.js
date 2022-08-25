import React, {Fragment, useEffect, useRef, useState} from 'react';
import {GET_IMAGE_ASSET} from "../../../../../shared/helper/validators";
import {OnboardingStyled} from "./OnboardingStyled";
import {GEARS} from '../Lotties/Gears';
import ZFLottie from "../../../../../shared/UI/Lottie";
import {CHECK} from "../Lotties/Check";
import {Spring, animated} from "react-spring";
import ZFButton from "../../../../../shared/UI/Button";
import _ from "lodash";
import ReactPlayer from "react-player";
import {CTA_COLORS_BG} from "../../../../../shared/UI/Theme/_color";
import CheckIcon from "@material-ui/icons/Check";

const Onboarding = () => {
    const DESKTOP = GET_IMAGE_ASSET('auth/signup/Desktop.jpeg')
    const [view, setView] = useState(0)
    const [hideBtn, setHideBtn] = useState(true)
    const [stopAnimation, setStopAnimation] = useState(true)
    const [demoVideo, setDemoVideo] = useState({
        playing: false,
        ended: false,
        duration: 0,
        durationLeft: 0
    })

    const videoRef = useRef()
    const animationDelay = 1200

    const AINIMATION_STEPS = [
        'Creating your coach account',
        'Creating your demo client, John Doe',
        'Setting up onboarding flow',
        'Pumping last set to get you started',
    ]

    const NEXT_STEPS = [
        'Watch the introduction video',
        'create a meal plan for the demo client',
        'create a workoutplan for the demo client',
        'create your first client',
        'connect to stripe',
    ]


    useEffect(() => {
        setTimeout(() => {
            setStopAnimation(false)
        }, animationDelay)
    }, []);

    const handleVideo = (name, value) => {
        setDemoVideo({...demoVideo, [name]: value})
    }

    const content = (view) => {
        switch (view) {
            case 0:
                return (
                    <Fragment>
                        <div className="welcome">
                            <h2>Setting up</h2>
                            <h1>Welcome to <span>Zenfit</span></h1>
                            <span className="InitalStep">
                                Hang on, while we are setting up your account.
                            </span>
                            <div className="steps">
                                {AINIMATION_STEPS.map((step, index) => {
                                        const delayed = (index + index + index) * 400
                                        return (
                                            <Spring
                                                key={index}
                                                to={{opacity: 1}}
                                                from={{opacity: 0}}
                                                delay={delayed}
                                                pause={stopAnimation}
                                                onRest={() => AINIMATION_STEPS.length === index + 1 && setHideBtn(false)}
                                            >
                                                {styles => (
                                                    <animated.div style={styles}>
                                                        <div className={`step step_${index}`} key={index}>
                                                            {`${index + 1}. ${_.capitalize(step)}`}
                                                            <ZFLottie
                                                                delay={(delayed + 400) + animationDelay}
                                                                speed={5}
                                                                loop={false}
                                                                autoplay={false}
                                                                lottie={CHECK}
                                                                width={30}
                                                                height={30}
                                                            />
                                                        </div>
                                                    </animated.div>

                                                )}
                                            </Spring>
                                        )
                                    }
                                )}
                            </div>
                            <Spring from={{opacity: 0}} to={{opacity: 1}} delay={500} pause={hideBtn}>
                                {
                                    styles => (
                                        <animated.div style={styles}>
                                            <ZFButton
                                                size={"bigBoi"}
                                                onClick={() => window.location.replace("/dashboard")}
                                            >
                                                Get started
                                            </ZFButton>
                                        </animated.div>
                                    )
                                }
                            </Spring>
                        </div>
                        <div className="Gears">
                            <ZFLottie
                                lottie={GEARS}
                                loop={true}
                                width={300}
                                height={300}
                            />
                        </div>
                    </Fragment>
                )
            case 1:
                return (
                    <div className={"chooseVideo"}>
                        <h2>Choose your video</h2>
                        <span>
                            Watch our 90 second video to get started.
                        </span>
                        <ReactPlayer
                            ref={videoRef}
                            /*onDuration={(e) => handleVideo('duration', e)}
                            onProgress={(e) => handleVideo('durationLeft', e.playedSeconds)}*/
                            onEnded={() => handleVideo('ended', true)}
                            playing={demoVideo.playing}
                            width={'100%'}
                            url={'https://player.vimeo.com/video/652130873?h=08966b40a8&amp;badge=0&amp;autopause=0&amp;player_id=0&amp;app_id=58479'}
                        />
                        <div className="btnSection">
                            <ZFButton
                                color={CTA_COLORS_BG}
                                onClick={() =>
                                    demoVideo.ended
                                        ? setView(view + 1)
                                        : handleVideo('playing', !demoVideo.playing)
                                }
                            >
                                {demoVideo.ended
                                    ? ' Go to next step'
                                    : (
                                        demoVideo.playing
                                            ? 'Pause video'
                                            : 'Watch video'
                                    )
                                }
                            </ZFButton>
                        </div>
                        <span onClick={() => setView(view + 1)} style={{cursor:'pointer'}}>
                            Skip video
                        </span>
                    </div>
                )
            case 2:
                return (
                    <div className="endStep">
                        <h2>Lets get started</h2>
                        <span className={"nextSteps"}>
                            {
                                NEXT_STEPS.map((step, index) => (
                                    <div className={"nextStep"} key={index}>
                                        <span className={`num num_${index} ${index === 0 ? 'active' : ''} ${index === 1 ? 'semiActive' : ''}`}>
                                            { index === 0 ? <CheckIcon/> : index+1}
                                        </span>
                                        {
                                            index !== NEXT_STEPS.length -1
                                                && <span className={`line ${index === 0 ? 'active' : ''}`}/>
                                        }
                                        <span className={"title"}>
                                            {_.capitalize(step)}
                                        </span>
                                    </div>
                                ))
                            }
                        </span>
                        <div className="btnSection">
                            <ZFButton
                                color={CTA_COLORS_BG}
                                onClick={() => window.location.replace("/dashboard")}
                            >
                                Continue
                            </ZFButton>
                        </div>
                    </div>
                )
            default:
                return <h2>No step provided</h2>
        }
    }

    return (
        <OnboardingStyled style={{backgroundImage: `url(${DESKTOP}`}}>
            <div className="inner">
                {content(view)}
            </div>
        </OnboardingStyled>
    )
}

export default Onboarding;


