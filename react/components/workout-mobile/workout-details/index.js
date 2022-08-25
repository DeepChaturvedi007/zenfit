/*jshint esversion: 6 */
import React from 'react';
import TextareaAutosize from 'react-textarea-autosize';
import OverlayConfirm from "../overlay-confirm/index";

export default class WorkoutDetails extends React.Component {

    stats = [
        {name: 'Sets', btn: '+ Add Sets', key: 'sets', focus: false},
        {name: 'Reps', btn: '+ Add Reps', key: 'reps', focus: false},
        {name: 'Rest (s)', btn: '+ Add Rest', key: 'rest', focus: false},
        {name: 'Weight', btn: '+ Add Weight', key: 'startWeight', focus: false},
        {name: 'Tempo', btn: '+ Add Tempo', key: 'tempo', focus: false},
        {name: 'RM', btn: '+ Add RM', key: 'rm', focus: false}
    ];

    videoPosterUrl = '/bundles/app/play-video-button.jpg';

    componentWillReceiveProps(newProps) {
        const {item: newItem = null} = newProps;
        const {item: oldItem = null} = this.props;

        if (oldItem && newItem) {
            this.stats.forEach(stat => {
                stat.focus = oldItem[stat.key] === null && oldItem[stat.key] !== newItem[stat.key];
            });
        }
    }

    render() {
        const {
            show,
            isUpdating,
            isDropSet,
            isTemplate,
            onClose,
            item,
            onCommentUpdate,
            onDropSetUpdate,
            onRemove,
            onTimerClick,
            onTrackClick,
            onHistoryClick,
            onSupersetUpdate,
            onRemoveFromSuperset
        } = this.props;
        let element = null;

        if (show && (Object.keys(item).length !== 0 && item.constructor === Object)) {
            const { exercise, supers, comment } = item;
            const description = [];
            exercise.muscle && description.push(exercise.muscle.name);
            exercise.equipment && description.push(exercise.equipment.name);
            exercise.type && description.push(exercise.type.name);

            let supersetBtnClass, supersetBtnMethod, supersetBtnName;

            if (supers) {
                supersetBtnClass = supers.length ? 'dark-button' : '';
                supersetBtnMethod = onSupersetUpdate;
                supersetBtnName = supers.length ? 'Remove Superset' : 'Make Superset';
            } else {
                supersetBtnClass = 'dark-button';
                supersetBtnMethod = onRemoveFromSuperset;
                supersetBtnName = 'Remove From Superset';
            }

            const tracking = isTemplate ? null : (
                <div className="workout-details-tracking">
                    <h4>Workout Tracking</h4>
                    <div className="tracking-actions">
                        <button className="workout-action-button" onClick={onTimerClick}>Start Timer</button>
                        <button className="workout-action-button" onClick={onTrackClick}>Track weight</button>
                        <button className="workout-action-button" onClick={onHistoryClick}>See Progress</button>
                    </div>
                </div>
            );

            element = (
                <OverlayConfirm show={show}
                                isDisabled={isUpdating}
                                title={exercise.name}
                                subtitle={description.join(' / ')}
                                onClose={onClose}
                                onConfirm={onClose}>
                    <div className="workout-details-container">
                        <div className="workout-details-video">
                            { this.getVideoElement(exercise.video) }
                        </div>
                        <div className="workout-details-stats">
                            { this.getStatsBlocks(item) }
                        </div>
                        <TextareaAutosize className="workout-details-comment"
                                          onChange={ onCommentUpdate }
                                          value={ comment }
                                          placeholder="Add Comment"/>
                        <div className="workout-details-actions">
                            <button className={ `workout-action-button ${supersetBtnClass}` }
                                    onClick={ supersetBtnMethod }>
                                { supersetBtnName }
                            </button>
                            <button className={ `workout-action-button ${isDropSet && 'dark-button'}` }
                                    onClick={ onDropSetUpdate }>
                                { isDropSet ? 'Cancel Dropset' : 'Make Dropset' }
                            </button>
                        </div>
                        {tracking}
                        <button className="workout-details-remove-btn" onClick={ onRemove }>
                            Remove Exercise from Workout
                        </button>
                    </div>
                </OverlayConfirm>
            );
        }

        return element;
    }

    getStatsBlocks(item) {
        const { onStatUpdate, onStatDefault, isStatDisabled } = this.props;
        return this.stats.map((stat, i) => {
            const isFocus = stat.focus;
            const isDisabledStats = stat.key === 'sets' || stat.key === 'rest';
            const isDisabled = isDisabledStats ? isStatDisabled : false;
            const valueElement = (isDisabledStats || item[stat.key] !== null) && item[stat.key] !== undefined
                ? <input type="text"
                         name={ stat.key }
                         ref={ input => {isFocus && input && input.focus()} }
                         value={ item[stat.key] !== null ? item[stat.key] : 0 }
                         disabled={isDisabled}
                         onChange={ onStatUpdate }/>
                : <button onClick={ onStatDefault.bind(null, stat.key) } disabled={isDisabled}>{ stat.btn }</button>;
            stat.focus = false;

            return (
                <div key={ i } className="workout-details-stat-wrapper">
                    <div className="workout-details-stat">
                        <h4>{ stat.name }</h4>
                        { valueElement }
                    </div>
                </div>
            );
        });
    }

    isYouTubeVideo(url) {
        return /yout/.test(url);
    }

    isVimeoVideo(url) {
        return /^.*(?:vimeo.com)\/(?:channels\/|channels\/\w+\/|groups\/[^\/]*\/videos\/|album‌​\/\d+\/video\/|video\/|)(\d+)(?:$|\/|\?)/.test(url);
    }

    getYouTubeVideoUrl(url) {
        const result = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/.exec(url);

        return result && `https://www.youtube.com/embed/${result[1]}`;
    }

    getVimeoVideoUrl(url) {
        const result = /^.*(?:vimeo.com)\/(?:channels\/|channels\/\w+\/|groups\/[^\/]*\/videos\/|album‌​\/\d+\/video\/|video\/|)(\d+)(?:$|\/|\?)/.exec(url);

        return result && `https://player.vimeo.com/video/${result[1]}`;
    }

    getVideoElement(videoUrl) {
        let video;
        const isYouTubeVideo = this.isYouTubeVideo(videoUrl);
        const isVimeoVideo = !isYouTubeVideo && this.isVimeoVideo(videoUrl);

        if (isYouTubeVideo || isVimeoVideo) {
            const serviceVideoUrl = isYouTubeVideo ? this.getYouTubeVideoUrl(videoUrl) : this.getVimeoVideoUrl(videoUrl);
            video = <iframe width="100%"
                            height="190"
                            src={ serviceVideoUrl }
                            frameBorder="0"
                            allowFullScreen/>;
        } else {
            video = (
                <video width="100%" height="190" controls poster={this.videoPosterUrl}>
                    <source src={ videoUrl } type="video/mp4"/>
                    <source src={ videoUrl } type="video/ogg"/>
                    Your browser does not support the video tag.
                </video>
            );
        }

        return video;
    }
}
