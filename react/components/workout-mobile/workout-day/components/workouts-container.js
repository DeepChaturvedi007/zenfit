/*jshint esversion: 6 */
import React from 'react';
import {connect} from 'react-redux';
import WorkoutDetails from '../../workout-details';
import ModalTimer from '../../modal-timer';
import ModalConfirm from '../../modal-confirm';
import AddExercisesList from './add-exercises-list';
import WorkoutsList from './workouts-list';
import {DROPSET_TEXT} from '../constants';
import TrackWeightContainer from './track-weight-container';
import WorkoutsHeader from './workouts-header';
import AssignClients from '../../assign-clients';
import AddClientModal from './modals/add-client';
import AssignTemplateModal from './modals/assign-template';
import * as workoutGlobalActions from '../actions/workout-global';
import * as workoutDayActions from '../actions/workout-day';
import * as workoutTrackActions from '../actions/workout-track';

function mapStateToProps(state) {
    return {
        dayId: state.global.dayId,
        isTemplate: state.global.isTemplate,
        url: state.global.url,
        muscles: state.global.muscles,
        equipments: state.global.equipments,
        btnClass: state.global.btnClass,
        dayName: state.global.dayName,
        workoutPlanName: state.global.workoutPlanName,
        workoutId: state.global.workoutId,
        totalActiveClients: state.global.totalActiveClients,
        workouts: state.workoutDay.workouts,
        comment: state.workoutDay.comment,
        selectedWorkout: state.workoutDay.selectedWorkout,
        selectedSuperSetId: state.workoutDay.selectedSuperSetId,
        isDayDataUpdating: state.workoutDay.isDayDataUpdating,
        isLoading: state.workoutDay.isLoading,
        isShowAddExercises: state.workoutDay.isShowAddExercises,
        isShowTimer: state.workoutDay.isShowTimer,
        isShowWorkoutDetails: state.workoutDay.isShowWorkoutDetails,
        isShowWorkoutRemove: state.workoutDay.isShowWorkoutRemove,
        isShowSuperSetOptionsList: state.workoutDay.isShowSuperSetOptionsList,
        isStatDisabled: state.workoutDay.isStatDisabled,
        isShowAssignPlanBar: state.workoutDay.isShowAssignPlanBar,
        isShowClients: state.workoutDay.isShowClients,
        isShowAddNewClient: state.workoutDay.isShowAddNewClient,
        isShowAssignTemplate: state.workoutDay.isShowAssignTemplate,
        clientsData: state.workoutDay.clientsData
    };
}

class WorkoutsContainer extends React.Component {
    componentDidMount() {
        const {fetchDay} = this.props;
        fetchDay();
    }

    render() {
        const {
            dayId,
            workoutId,
            isTemplate,
            url,
            muscles,
            equipments,
            btnClass,
            dayName,
            workoutPlanName,
            workouts,
            comment,
            isLoading,
            isDayDataUpdating,
            selectedWorkout,
            selectedSuperSetId,
            isShowAddExercises,
            isShowTimer,
            isShowWorkoutDetails,
            isShowWorkoutRemove,
            isShowSuperSetOptionsList,
            isStatDisabled,
            isShowAssignPlanBar,
            isShowClients,
            isShowAddNewClient,
            isShowAssignTemplate,
            clientsData,
            totalActiveClients,
            hideAssignPlanBar,
            setTotalActiveClients,
            showAddExercises,
            showWorkoutDetails,
            hideAddExercises,
            hideWorkoutDetails,
            toggleTimer,
            toggleWorkoutDelete,
            showTodayTrackWeight,
            showWeightHistory
        } = this.props;

        const renderInfoMessage = () => {
            if (workouts.length == 0) {
                return null;
            } else {
                return (
                  <div className="alert alert-info">
                      This is a workout day. Go add some exercises to this workout day.
                  </div>
                )
            }
        }

        const selectedItemRest = (selectedWorkout.rest * 1000);
        const addSupersetSubtitle = selectedWorkout.exercise && selectedWorkout.exercise.name;
        const addExercisesTitle = selectedWorkout.id && selectedSuperSetId ? 'Create Superset' : 'Select Exercise(s)';
        const addExercisesSubtitle = selectedWorkout.id && selectedSuperSetId ? 'Select exercise(s) to combine with' : '';
        const addExercisesSubtitleBold = selectedWorkout.id && selectedSuperSetId ? addSupersetSubtitle : '';
        const isDropSet = selectedWorkout.comment && selectedWorkout.comment.indexOf(DROPSET_TEXT) !== -1;
        const trackingContainer = isTemplate ? null : <TrackWeightContainer/>;
        const infoMessage = isLoading ? null : renderInfoMessage();

        return (
            <div>
                {infoMessage}
                <div className="ibox float-e-margins">
                    <WorkoutsHeader url={url}
                                    btnClass={btnClass}
                                    dayName={dayName}
                                    workoutPlanName={workoutPlanName}
                                    addExercisesClick={showAddExercises.bind(null, null)}
                    />
                    <div className="ibox-content m-n-p-x m-n-p-y">
                        <div className="project-list">
                            <WorkoutsList dayId={dayId}
                                          isLoading={isLoading}
                                          dayComment={comment}
                                          workouts={workouts}
                                          addExercisesClick={showAddExercises.bind(null, null)}
                                          onSortableUpdate={this.handleSortableUpdate}
                                          onSortableAdd={this.handleSortableAdd}
                                          onCommentChange={this.handleDayCommentChange}
                                          onItemClick={showWorkoutDetails}
                                          onSupersetClick={showAddExercises}
                                          isShowOptionsList={isShowSuperSetOptionsList}
                                          onShowOptionsList={this.handleShowOptionsList}
                                          onCloseOptionsList={this.handleCloseOptionsList}
                                          onAddToSuperSet={this.handleAddToSuperSet}
                                          onDeleteSuperSet={this.handleDeleteSuperSet}
                            />
                            <WorkoutDetails show={isShowWorkoutDetails}
                                            item={selectedWorkout}
                                            isStatDisabled={isStatDisabled}
                                            isDropSet={isDropSet}
                                            isUpdating={isDayDataUpdating}
                                            isTemplate={isTemplate}
                                            onClose={hideWorkoutDetails}
                                            onStatUpdate={this.handleStatChange}
                                            onStatDefault={this.handleStatDefaultValue}
                                            onCommentUpdate={this.handleWorkoutCommentUpdate}
                                            onDropSetUpdate={this.handleDropSet}
                                            onRemove={toggleWorkoutDelete}
                                            onTimerClick={toggleTimer}
                                            onTrackClick={showTodayTrackWeight}
                                            onHistoryClick={showWeightHistory}
                                            onSupersetUpdate={this.handleSupersetClick}
                                            onRemoveFromSuperset={this.handleRemoveFromSuperset}
                            />
                            <AddExercisesList show={isShowAddExercises}
                                              title={addExercisesTitle}
                                              muscles={muscles}
                                              equipments={equipments}
                                              subtitle={addExercisesSubtitle}
                                              subtitleBold={addExercisesSubtitleBold}
                                              onClose={hideAddExercises}
                                              onAddExercises={this.handleAddExercises}
                            />
                            <ModalTimer show={isShowTimer}
                                        milliseconds={selectedItemRest}
                                        onClose={toggleTimer}
                            />
                            <ModalConfirm show={isShowWorkoutRemove}
                                          title="Remove Exercise"
                                          description="Do you really want to remove this exercise from a workout plan?"
                                          btnText="Remove"
                                          type={ModalConfirm.TYPE_WARNING}
                                          onClose={toggleWorkoutDelete}
                                          onConfirm={this.handleWorkoutRemove}
                            />
                            {trackingContainer}
                        </div>
                    </div>
                </div>

                <AddClientModal show={isShowAddNewClient}
                                onClose={this.closeAddNewClient}
                                onAdd={this.handleAddNewClient}
                                workoutId={workoutId}
                                setTotalActiveClients={setTotalActiveClients}
                />
            </div>
        );
    }

    handleSortableUpdate = (evt) => {
        const {updateSortableWorkouts} = this.props;
        const parentId = evt.component.props.parentId;
        updateSortableWorkouts(evt.oldIndex, evt.newIndex, parentId);
    };

    handleSortableAdd = (evt) => {
        const {addSortableWorkouts} = this.props;
        const fromParentId = evt.fromComponent.props.parentId;
        const toParentId = evt.toComponent.props.parentId;
        addSortableWorkouts(evt.oldIndex, evt.newIndex, fromParentId, toParentId);
    };

    handleDayCommentChange = (evt) => {
        const {updateDayComment} = this.props;
        updateDayComment(evt.target.value);
    };

    handleWorkoutCommentUpdate = (evt) => {
        const {updateWorkoutComment} = this.props;
        updateWorkoutComment(evt.target.value);
    };

    handleStatChange = (evt) => {
        const {updateWorkoutStat} = this.props;
        updateWorkoutStat(evt.target.name, evt.target.value);
    };

    handleStatDefaultValue = (key) => {
        const {updateWorkoutStatToDefault} = this.props;
        updateWorkoutStatToDefault(key);
    };

    handleDropSet = () => {
        const {updateWorkoutDropSet} = this.props;
        updateWorkoutDropSet();
    };

    handleWorkoutRemove = () => {
        const {removeWorkout} = this.props;
        removeWorkout();
    };

    handleSupersetClick = () => {
        const {selectedWorkout, removeWorkoutSuperSet, showAddExercises} = this.props;

        if (selectedWorkout.supers.length) {
            removeWorkoutSuperSet();
        } else {
            showAddExercises(selectedWorkout.id);
        }
    };

    handleAddExercises = (exercises) => {
        const {createWorkouts} = this.props;
        createWorkouts(exercises);
    };

    handleRemoveFromSuperset = () => {
        const {removeWorkoutFromSuperSet} = this.props;
        removeWorkoutFromSuperSet();
    };

    handleShowOptionsList = (superSetId) => {
        this.props.showSuperSetOptionsList(superSetId);
    };

    handleCloseOptionsList = () => {
        this.props.hideSuperSetOptionsList();
    };

    handleAddToSuperSet = () => {
        const {selectedSuperSetId, showAddExercises, hideSuperSetOptionsList} = this.props;
        hideSuperSetOptionsList();
        showAddExercises(selectedSuperSetId);
    };

    handleDeleteSuperSet = () => {
        const {selectedSuperSetId, removeSuperSetWorkoutId, hideSuperSetOptionsList} = this.props;
        removeSuperSetWorkoutId(selectedSuperSetId);
        hideSuperSetOptionsList();
    };

    handleAssignBar = show => {
        const {hideAssignPlanBar, showAssignClientsList} = this.props;
        hideAssignPlanBar();
        showAssignClientsList(show);
    };

    closeAssignClientsList = () => {
        this.props.showAssignClientsList(false);
    };

    handleAssignClients = clients => {
        this.closeAssignClientsList();
        this.props.handleAssignClients(clients);
    };

    handleClickAddClient = () => {
        this.closeAssignClientsList();
        this.props.showAddNewClient(true);
    };

    closeAddNewClient = () => {
        this.props.showAddNewClient(false);
    };

    handleAddNewClient = clientsData => {
        this.props.setAssignClientsData(clientsData);
        this.props.showAssignTemplate(true);
    };

    closeAssignTemplate = () => {
        this.props.setAssignClientsData([]);
        this.props.showAssignTemplate(false);
    };
}

export default connect(
    mapStateToProps, {...workoutDayActions, ...workoutTrackActions, ...workoutGlobalActions}
)(WorkoutsContainer);
