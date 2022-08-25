/*jshint esversion: 6 */
import React from 'react';
import OverlayConfirm from '../overlay-confirm';

export default class ModalExerciseList extends React.Component {

    static defaultProps = {
        threshold: 100
    };

    constructor(props) {
        super(props);
        this.state = {
            q: '',
            muscleId: '',
            equipmentId: '',
            equipments: [],
            muscles: []
        };

        this.scrollListener = this.scrollListenerMethod.bind(this);
        this.handleChange = this.handleChange.bind(this);
        this.handleSelect = this.handleSelect.bind(this);
        this.handleClose = this.handleClose.bind(this);
        this.handleConfirm = this.handleConfirm.bind(this);
    }

    componentDidMount() {
        this.attachScrollListener();
    }

    componentWillUnmount() {
        this.detachScrollListener();
    }

    render() {
        const {
            exercises,
            youtube,
            selectedExercises,
            onSelect,
            listStyle,
            muscles,
            equipments,
            onExerciseCreate,
            emptyResult,
            onYoutubeSearch
        } = this.props;
        const {q, muscleId, equipmentId} = this.state;
        const isDisabled = !Object.keys(selectedExercises).length;

        const musclesItems = muscles.map(muscle => {
            return <option key={muscle.id} value={muscle.id}>{muscle.name}</option>;
        });
        const equipmentsItems = equipments.map(equipment => {
            return <option key={equipment.id} value={equipment.id}>{equipment.name}</option>;
        });

        let exercisesItems = exercises.map((exercise, i) => {
            const isSelected = selectedExercises[exercise.id];
            const description = [];
            exercise.muscleGroup && description.push(exercise.muscleGroup.name);
            exercise.equipment && description.push(exercise.equipment.name);
            exercise.exerciseType && description.push(exercise.exerciseType.name);

            return (
                <li ref={i} key={i} onClick={onSelect.bind(null, exercise)}>
                    <div className="exercise-image" data-exercise-href={`/api/exercises/exerciseInfo/${exercise.id}`}>
                        <img src={exercise.picture_url || '/images/exercise_thumbnail.png'}/>
                    </div>
                    <div className="exercise-text">
                        <h4>{exercise.name}</h4>
                        <span>{description.join(' / ')}</span>
                    </div>
                    <div className={`exercise-status ${isSelected ? 'selected' : ''}`}>
                        <span>{isSelected ? 'Selected' : 'Select'}</span>
                    </div>
                </li>
            );
        });

        const youtubeItems = youtube.map(item => {
            const thumbnail = item.snippet.thumbnails.default.url;
            const video = `https://www.youtube.com/watch?v=${item.id.videoId}`;
            const preview = `/api/exercises/youtubeExerciseInfo/${item.id.videoId}`;

            return (
                <li key={item.id.videoId}>
                    <div className="exercise-image" data-exercise-href={preview}>
                        <img src={thumbnail} alt=""/>
                    </div>
                    <div className="exercise-text" data-exercise-href={preview}>
                        <h4>{item.snippet.title}</h4>
                    </div>
                    <div className="plan-item-description" onClick={onExerciseCreate}>
                        <a data-toggle="modal"
                           data-type="new"
                           data-target="#user_exercise_modal"
                           data-title={q}
                           data-video={video}>Create as Exercise</a>
                    </div>
                </li>
            );
        });

        const youtubeHeader = (
            <li key="plans-search-youtube">
                <div className="plans-search-youtube">
                    <div className="plans-search-youtube-header">
                        <div className="plans-search-youtube-logo">
                            <svg viewBox="0 0 56 24" preserveAspectRatio="xMidYMid meet">
                                <g viewBox="0 0 56 24" preserveAspectRatio="xMidYMid meet" className="style-scope yt-icon">
                                    <path id="you-path"
                                          d="M20.9 19.3h-2.12v-1.24c-.8.94-1.5 1.4-2.23 1.4-.66 0-1.1-.3-1.34-.87-.12-.35-.22-.88-.22-1.67V7.9h2.12V17.16c.05.3.18.42.45.42.4 0 .78-.37 1.23-1V7.9h2.12v11.4M13.4 11.62c0-1.22-.23-2.13-.66-2.7-.56-.8-1.45-1.1-2.35-1.1-1.02 0-1.8.3-2.35 1.1-.44.57-.67 1.5-.67 2.7v4.07c0 1.2.2 2.04.64 2.6.56.8 1.48 1.2 2.37 1.2.9 0 1.82-.4 2.4-1.2.4-.56.6-1.4.6-2.6V11.6zm-2.1 4.3c.1 1.13-.25 1.7-.9 1.7-.66 0-1-.57-.9-1.7V11.4c-.1-1.13.24-1.66.9-1.66.65 0 1 .53.9 1.66v4.52zM5.03 13.1v6.2H2.8v-6.2S.47 5.46 0 4.04h2.35L3.92 10l1.56-5.95h2.34l-2.8 9.04"
                                          className="style-scope yt-icon"></path>
                                    <g id="tube-paths" className="style-scope yt-icon">
                                        <path
                                            d="M42.74 9.7c-.33 0-.7.2-1.05.52v6.86c.33.34.7.5 1.04.5.6 0 .85-.42.85-1.55v-4.86c0-1.13-.27-1.46-.86-1.46M51.08 11.07c0-1.05-.27-1.36-.94-1.36-.67 0-.96.3-.96 1.35v1.25h1.9v-1.23"
                                            className="style-scope yt-icon"></path>
                                        <path
                                            d="M55.67 5.28s-.33-2.3-1.33-3.33C53.07.6 51.64.6 51 .53 46.33.2 39.32.2 39.32.2h-.02s-7 0-11.67.33c-.65.08-2.08.08-3.35 1.42-1 1.02-1.32 3.33-1.32 3.33s-.34 2.72-.34 5.44v2.55c0 2.72.34 5.43.34 5.43s.32 2.32 1.32 3.34c1.27 1.34 2.94 1.3 3.68 1.43 2.67.26 11.35.34 11.35.34s7.03 0 11.7-.34c.65-.08 2.07-.08 3.34-1.42 1-1.02 1.33-3.34 1.33-3.34S56 16 56 13.27v-2.55c0-2.72-.33-5.44-.33-5.44zM29.95 19.3h-2.23v-13h-2.35V4.18h7.04V6.3h-2.45v13zm8.05 0h-2.12v-1.24c-.8.94-1.5 1.4-2.23 1.4-.66 0-1.1-.3-1.34-.87-.12-.35-.22-.88-.22-1.67V8h2.12v9.17c.05.3.18.42.45.42.4 0 .78-.37 1.23-1V8H38v11.3zm7.7-3.38c0 1.04-.07 1.78-.2 2.26-.28.84-.87 1.27-1.67 1.27-.72 0-1.46-.44-2.14-1.28v1.14h-2.02V4.18h2V9.1c.66-.8 1.4-1.27 2.15-1.27.8 0 1.34.47 1.6 1.3.15.47.28 1.2.28 2.27v4.52zm4.46 1.67c.5 0 .8-.28.9-.83.02-.1.02-.6.02-1.42h2.12v.32c0 .66-.05 1.13-.07 1.33-.07.46-.23.87-.47 1.23-.56.82-1.4 1.22-2.45 1.22-1.05 0-1.85-.38-2.44-1.16-.43-.57-.7-1.4-.7-2.6v-3.96c0-1.2.25-2.14.68-2.72.58-.77 1.4-1.18 2.42-1.18s1.82.4 2.4 1.18c.4.58.65 1.46.65 2.67V14H49.2v2.02c0 1.05.3 1.57.98 1.57z"
                                            className="style-scope yt-icon"></path>
                                    </g>
                                </g>
                            </svg>
                        </div>
                        <h5>Exercise Videos</h5>
                    </div>
                    <p className="plans-search-youtube-description">Use Youtube Videos to Create Custom Exercises!</p>
                </div>
            </li>
        );

        const youtubeFooter = (
            <li className="plans-search-youtube-footer" key="search-youtube-footer">
                <a onClick={onYoutubeSearch}>See More Youtube Results</a>
            </li>
        );

        if (youtubeItems.length) {
            exercisesItems = exercisesItems.concat(youtubeHeader).concat(youtubeItems).concat(youtubeFooter);
        }

        let searchEmptyResult = '';
        if (emptyResult) {
            let description;
            if (exercises.length) {
                description = 'There are no more exercises matching your search query. Fortunately, you can create your own exercise!';
            } else {
                description = 'Sorry, we couldn\'t find any exercises matching your search query. Fortunately, you can create your own exercise!';
            }

            searchEmptyResult = (
                <li key="search-result-empty-state">
                    <div className="search-result-empty-state no-drag">
                        <p>{description}</p>
                        <button
                            className="btn btn-success"
                            data-toggle="modal"
                            data-type="new"
                            data-target="#user_exercise_modal"
                            data-title={q}>Create Exercise</button>
                    </div>
                </li>
            );
            exercisesItems = exercisesItems.concat(searchEmptyResult);
        }

        return (
            <OverlayConfirm {...this.props}
                            onConfirm={this.handleConfirm}
                            onClose={this.handleClose}
                            isDisabled={isDisabled}>
                <div className="exercises-list-search">
                    <input type="text" value={q} placeholder="&#xf002; Search" onChange={this.handleChange}/>
                </div>
                <div className="exercises-list-search plans-search-filters">
                    <div className="plans-search-filter">
                        <select className="form-control"
                                value={muscleId}
                                data-key="muscleId"
                                onChange={this.handleSelect}>
                            <option value="">Filter by Muscle Group</option>
                            {musclesItems}
                        </select>
                    </div>
                    <div className="plans-search-filter">
                        <select className="form-control"
                                value={equipmentId}
                                data-key="equipmentId"
                                onChange={this.handleSelect}>
                            <option value="">Filter by Equipment</option>
                            {equipmentsItems}
                        </select>
                    </div>
                    <div className="plans-search-title">
                        <p>Exercises</p>
                        <a onClick={onExerciseCreate}
                           data-toggle="modal"
                           data-type="new"
                           data-target="#user_exercise_modal"
                           data-title=""
                           data-video="">Create Your Own Exercise</a>
                    </div>
                </div>
                <ul className="exercises-list" ref={input => { this.list = input; }} style={listStyle}>
                    {exercisesItems}
                </ul>
            </OverlayConfirm>
        );
    }

    attachScrollListener() {
        this.list.addEventListener('scroll', this.scrollListener, true);
    }

    detachScrollListener() {
        this.list.removeEventListener('scroll', this.scrollListener, true);
    }

    scrollListenerMethod() {
        const {threshold, onScrollBottom} = this.props;
        let topScrollPos = this.list.scrollTop;
        let totalContainerHeight = this.list.scrollHeight;
        let containerFixedHeight = this.list.offsetHeight;
        let bottomScrollPos = topScrollPos + containerFixedHeight;

        if ((totalContainerHeight - bottomScrollPos) < threshold) {
            onScrollBottom();
        }
    }

    handleConfirm() {
        this.props.onConfirm();
        this.setState({
            q: '',
            muscleId: '',
            equipmentId: ''
        });
        this.refs[0].scrollIntoView();
    }

    handleClose () {
        this.props.onClose();
        this.setState({
            q: '',
            muscleId: '',
            equipmentId: ''
        });
        if(this.refs[0]) {
            this.refs[0].scrollIntoView();
        }
    }

    handleChange(event) {
        this.setState({
            q: event.target.value
        });
        const {muscleId, equipmentId} = this.state;
        const {onSearch} = this.props;
        onSearch && onSearch({q: event.target.value, muscleId: muscleId, equipmentId: equipmentId});
    }

    handleSelect(event) {
        this.setState({
            [event.target.dataset.key]: event.target.value
        }, function () {
            const {q, muscleId, equipmentId} = this.state;
        const {onSearch} = this.props;
            onSearch && onSearch({q: q, muscleId: muscleId, equipmentId: equipmentId});
        });
    }
}
