/*jshint esversion: 6 */
import React from 'react';

export default class ModalTimer extends React.Component {

    warningThreshold = 3000;

    constructor(props) {
        super(props);

        this.state = {
            leftMilliseconds: props.milliseconds
        };

        this.play = this.play.bind(this);
        this.pause = this.pause.bind(this);
        this.restart = this.restart.bind(this);
        this.closeModal = this.closeModal.bind(this);
    }

    componentWillMount() {
        this.audio = new Audio('/beep_long.mp3');
    }

    componentWillReceiveProps(newProps) {
        if (newProps.show !== this.props.show && newProps.show) {
            this.setState({leftMilliseconds: newProps.milliseconds});
        }
    }

    render() {
        const {leftMilliseconds} = this.state;
        const {show} = this.props;

        const actionBtnState = this.getActionBtnState();

        const isTimeOver = leftMilliseconds <= 0;
        const isWarning = leftMilliseconds <= this.warningThreshold;

        const hiddenClass = show ? '' : 'hidden';
        const timerHiddenClass = isTimeOver ? 'hidden' : '';
        const timeOverHiddenClass = isTimeOver ? '' : 'hidden';
        const timerWarningClass = isWarning ? 'warning-text' : '';

        return (
            <div className={`modal-timer-container ${hiddenClass}`}>
                <div className="modal-timer-close-button">
                    <button onClick={this.closeModal}><i className="material-icons">clear</i></button>
                </div>
                <div className="modal-timer-content-wrapper">
                    <div className="modal-timer-content">
                        <h4 className={timerHiddenClass}>Rest</h4>
                        <p className={`${timerHiddenClass} ${timerWarningClass}`}>{this.getTime()}</p>
                        <h3 className={timeOverHiddenClass}>Time is up!</h3>
                    </div>
                </div>
                <div className="modal-timer-action-button">
                    <button onClick={actionBtnState.callback}>{actionBtnState.text}</button>
                </div>
            </div>
        );
    }

    play() {
        if (!this.timeout) {
            this.prev = Date.now();
            this.counting();
            this.audio.play();
            this.audio.pause();
        }
    }

    pause() {
        clearTimeout(this.timeout);
        this.timeout = null;
        this.forceUpdate();
    }

    restart() {
        this.setState({leftMilliseconds: this.props.milliseconds}, this.play);
    }

    counting() {
        if (this.state.leftMilliseconds > 0) {
            this.timeout = setTimeout(() => {
                this.countDown();
                this.counting();
            }, 1000);
            if (this.state.leftMilliseconds <= this.warningThreshold) {
                this.playBeep();
            }
        } else {
            this.pause();
            this.playLongBeep();
        }
    }

    countDown() {
        const now = Date.now();
        const offset = now - this.prev;
        const newLeftMilliseconds = this.state.leftMilliseconds - offset;

        this.prev = now;

        this.setState({leftMilliseconds: newLeftMilliseconds > 0 ? newLeftMilliseconds : 0});
    }

    getTime() {
        const {leftMilliseconds} = this.state;
        const minutes = Math.floor(leftMilliseconds / 1000 / 60);
        const seconds = Math.round(leftMilliseconds / 1000 % 60);

        return `${minutes}:${seconds > 9 ? seconds : '0'+seconds}`;
    }

    closeModal() {
        this.pause();
        this.props.onClose();
    }

    getActionBtnState() {
        const state = {};

        if (this.timeout && this.state.leftMilliseconds > 0) {
            state.callback = this.pause;
            state.text = 'Pause Timer';
        } else if (!this.timeout && this.state.leftMilliseconds === 0) {
            state.callback = this.restart;
            state.text = 'Continue';
        } else {
            state.callback = this.play;
            state.text = 'Run Timer';
        }

        return state;
    }

    playBeep() {
        this.audio.playbackRate = 3;
        this.audio.play();
    }

    playLongBeep() {
        this.audio.playbackRate = 1;
        this.audio.play();
    }
}
