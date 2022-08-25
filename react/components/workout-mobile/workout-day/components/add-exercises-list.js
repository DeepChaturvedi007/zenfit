/*jshint esversion: 6 */
import 'whatwg-fetch';
import _ from 'lodash';
import React from 'react';
import ModalExerciseList from '../../modal-exercise-list';
import {GET_EXERCISES} from '../../../../api/workout-api';

export default class AddExercisesList extends React.Component {
    page = 1;
    q = '';
    muscleId = '';
    equipmentId = '';
    isMore = true;
    limit = 10;
    youtubeApiKey = 'AIzaSyDynLURbt0cWbdCye1Ax_q33eM6W1CUdUc';
    youtubeParams = {
        nextPageToken: null,
        prevPageToken: null,
        pageInfo: {},
    };

    constructor(props) {
        super(props);

        this.state = {
            exercises: [],
            youtube: [],
            selectedExercises: {},
            loading: false
        };

        this.onScroll = this.onScroll.bind(this);
        this.handleSearch = this.handleSearch.bind(this);
        this.handleClose = this.handleClose.bind(this);
        this.handleConfirm = this.handleConfirm.bind(this);
        this.selectExercises = this.selectExercises.bind(this);
        this.handleExerciseCreate = this.handleExerciseCreate.bind(this);
        this.handleYoutubeSearch = this.handleYoutubeSearch.bind(this);

        this.debouncedResetExercises = _.debounce(this.resetExercises.bind(this), 1000);
    }

    componentDidMount() {
        this.fetchExercises();
    }

    render() {
        const {show, title, subtitle, subtitleBold, muscles, equipments} = this.props;
        const {exercises, youtube, selectedExercises} = this.state;
        const listStyle = {
            maxHeight: this.getListMaxHeight()
        };

        return (
            <ModalExerciseList show={show}
                               muscles={muscles}
                               equipments={equipments}
                               emptyResult={!this.isMore}
                               exercises={exercises}
                               youtube={youtube}
                               selectedExercises={selectedExercises}
                               title={title}
                               subtitle={subtitle}
                               subtitleBold={subtitleBold}
                               onSearch={this.handleSearch}
                               onSelect={this.selectExercises}
                               onScrollBottom={this.onScroll}
                               onConfirm={this.handleConfirm}
                               onClose={this.handleClose}
                               onExerciseCreate={this.handleExerciseCreate}
                               onYoutubeSearch={this.handleYoutubeSearch}
                               listStyle={listStyle}/>
        );
    }

    fetchExercises() {
        this.setState({loading: true});
        const newState = {};

        fetch(GET_EXERCISES(this.q, this.page, this.muscleId, this.equipmentId), {
            credentials: 'include'
        }).then(response => {
            response.json().then(data => {
                newState.loading = false;
                if (data.length) {
                    newState.exercises = this.state.exercises.concat(data);
                } else {
                    this.isMore = false;
                }
                if (this.q && (newState.exercises == undefined || newState.exercises.length < this.limit)) {
                    this.isMore = false;
                    return api.searchYoutube(this.q, this.youtubeApiKey, 10, 'snippet', this.youtubeParams);
                } else {
                    newState.youtube = []
                }
                return newState;
            }).then(response => {
                let nextYoutubeParams = {
                    ...this.youtubeParams,
                };
                if (response.data && Array.isArray(response.data.items)) {
                    nextYoutubeParams = {
                        ...nextYoutubeParams,
                        nextPageToken: response.data.nextPageToken,
                        prevPageToken: response.data.prevPageToken,
                        pageInfo: response.data.prevPageToken,
                    };
                    newState.youtube = response.data.items
                        .filter(item => item.id.kind === 'youtube#video');
                }
                this.youtubeParams = nextYoutubeParams;
                this.setState(newState);
            });
        });
    }

    resetExercises() {
        this.isMore = true;
        this.page = 1;

        this.setState({exercises: [], selectedExercises: {}});

        this.fetchExercises();
    }

    handleSearch(data) {
        this.q = data.q;
        this.muscleId = data.muscleId;
        this.equipmentId = data.equipmentId;
        this.debouncedResetExercises();
    }

    handleClose() {
        this.setState({selectedExercises: {}});
        this.props.onClose();
        this.q = '';
        this.muscleId = '';
        this.equipmentId = '';
        this.debouncedResetExercises();
    }

    onScroll() {
        if (!this.state.loading && this.isMore) {
            this.page++;
            this.fetchExercises();
        }
    }

    selectExercises(exercise) {
        const newSelectedExercises = {
            ...this.state.selectedExercises
        };

        if (!newSelectedExercises[exercise.id]) {
            newSelectedExercises[exercise.id] = true;
        } else {
            delete newSelectedExercises[exercise.id];
        }

        this.setState({selectedExercises: newSelectedExercises});
    }

    handleConfirm() {
        const selectedExercises = this.state.exercises.filter(exercise => {
            return this.state.selectedExercises[exercise.id];
        });

        this.props.onAddExercises(selectedExercises);

        this.setState({selectedExercises: {}});
        this.q = '';
        this.muscleId = '';
        this.equipmentId = '';
        this.debouncedResetExercises();
    }

    getListMaxHeight() {
        const {subtitle, subtitleBold} = this.props;
        if (!subtitle && !subtitleBold) {
            return 'calc(100% - 184px)';
        } else {
            return 'calc(100% - 124px)';
        }
    }

    handleExerciseCreate() {
        const self = this;
        const modal = $('#user_exercise_modal');
        const form = modal.find('#userExerciseForm');
        form.on('submit', function (e) {
            e.preventDefault();
            const $this = $(this);

            fetch($this.attr('action'), {
                method: 'post',
                credentials: 'include',
                body: JSON.stringify({
                    name: $this.find('#exerciseTitle').val(),
                    exerciseTypeId: $this.find('#exerciseType').val(),
                    muscleGroupId: $this.find('#muscleGroup').val(),
                    workoutTypeId: $this.find('#workoutType').val(),
                    equipmentId: $this.find('#equipment').val(),
                    videoUrl: $this.find('[name="videoUrl"]').val(),
                    videoThumbnail: $this.find('[name="videoThumbnail"]').val(),
                    execution: $this.find('[name="execution"]').val()
                })
            }).then(response => response.json()).then(response => {
                self.resetExercises();
                modal.modal('hide');
                form.unbind('submit');
            });
        });
    }

    handleYoutubeSearch() {
        this.youtubeSearch(this.q, this.youtubeApiKey, 10, 'snippet', this.youtubeParams).then(response => {
            let newState = {};
            let nextYoutubeParams = {
                ...this.youtubeParams,
            };
            if (response.data && Array.isArray(response.data.items)) {
                nextYoutubeParams = {
                    ...nextYoutubeParams,
                    nextPageToken: response.data.nextPageToken,
                    prevPageToken: response.data.prevPageToken,
                    pageInfo: response.data.prevPageToken,
                };
                newState.youtube = this.state.youtube.concat(response.data.items
                    .filter(item => item.id.kind === 'youtube#video'));
            }
            this.youtubeParams = nextYoutubeParams;
            this.setState(newState);
        });
    }

    youtubeSearch(q, key, maxResults = 5, part = 'snippet', params = {}) {
        return new Promise(function(resolve, reject) {
            resolve(api.searchYoutube(q, key, maxResults, part, params));
        });
    }
}
