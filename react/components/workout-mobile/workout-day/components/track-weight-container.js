/*jshint esversion: 6 */

import React from 'react';
import { connect } from 'react-redux';
import dateService from '../../services/date';
import DatePicker from 'react-mobile-datepicker';
import ModalTrackWeight from '../../modal-track-weight';
import ModalWeightHistory from '../../modal-weight-history';
import {
    addNewTrackWeight,
    updateWeightStat,
    updateWeightDate,
    deleteWeight,
    fetchHistory,
    showTrackWeight,
    showWeightHistory,
    hideTrackWeight,
    hideWeightHistory,
    toggleDatePicker,
    saveWeights
} from '../actions/workout-track';

function mapStateToProps(state) {
    return {
        history: state.workoutTrack.history,
        selectedDate: state.workoutTrack.selectedDate,
        isShowTrackWeight: state.workoutTrack.isShowTrackWeight,
        isShowHistory: state.workoutTrack.isShowHistory,
        isOpenDatePicker: state.workoutTrack.isOpenDatePicker,
        isUpdatingWeight: state.workoutTrack.isUpdatingWeight,

        selectedWorkout: state.workoutDay.selectedWorkout,
        isShowWorkoutDetails: state.workoutDay.isShowWorkoutDetails
    };
}

class TrackWeightContainer extends React.Component {
    componentWillReceiveProps(newProps) {
        const isShouldFetchHistory = newProps.isShowWorkoutDetails
            && newProps.isShowWorkoutDetails !== this.props.isShowWorkoutDetails;

        if (isShouldFetchHistory) {
            this.props.fetchHistory(newProps.selectedWorkout.id);
        }
    }

    render() {
        const {
            history,
            selectedWorkout,
            selectedDate,
            isShowTrackWeight,
            isShowHistory,
            isOpenDatePicker,
            isUpdatingWeight,

            showTrackWeight,
            showWeightHistory,
            hideTrackWeight,
            hideWeightHistory,
            toggleDatePicker
        } = this.props;

        const selectedWorkoutName = selectedWorkout.exercise ? selectedWorkout.exercise.name : '';
        const now = new Date();
        const datePickerValue = selectedDate ? new Date(selectedDate) : now;
        const selectedWeightGroup = selectedDate
            ? history.filter(item => item.date === selectedDate).map(item => ({...item}))
            : [];

        return (
            <div>
                <ModalTrackWeight show={isShowTrackWeight}
                                  subtitle={selectedWorkoutName}
                                  items={selectedWeightGroup}
                                  isDisabled={isUpdatingWeight}
                                  onAddNewSet={this.handleAddNewWeight}
                                  onSetDelete={this.handleWeightDelete}
                                  onStatChange={this.handleWeightStatChange}
                                  onToggleDatePicker={toggleDatePicker}
                                  onHistoryClick={showWeightHistory}
                                  onClose={hideTrackWeight}
                                  onConfirm={this.saveWeights}/>
                <ModalWeightHistory show={isShowHistory}
                                    subtitle={selectedWorkoutName}
                                    history={history}
                                    onEditClick={showTrackWeight}
                                    onClose={hideWeightHistory}
                                    onConfirm={hideWeightHistory}/>
                <DatePicker isOpen={isOpenDatePicker}
                            value={datePickerValue}
                            max={now}
                            onSelect={this.handleWeightDateUpdate}
                            onCancel={toggleDatePicker}
                            dateFormat={['YYYY', 'MM', 'DD']}
                            confirmText="Select"
                            cancelText="Cancel"/>
            </div>
        );
    }

    saveWeights = () => {
        this.props.saveWeights();
    }

    handleAddNewWeight = () => {
        const {selectedWorkout, selectedDate, addNewTrackWeight} = this.props;
        addNewTrackWeight(selectedWorkout.id, selectedDate);
    }

    handleWeightDelete = (deleteWeight) => {
        this.props.deleteWeight(deleteWeight.id);
    }

    handleWeightStatChange = (id, evt) => {
        this.props.updateWeightStat(id, evt.target.name, evt.target.value);
    }

    handleWeightDateUpdate = (dateObj) => {
        this.props.updateWeightDate(dateObj);
    }
}

export default connect(mapStateToProps, {
    addNewTrackWeight,
    updateWeightStat,
    updateWeightDate,
    deleteWeight,
    fetchHistory,
    showTrackWeight,
    showWeightHistory,
    hideTrackWeight,
    hideWeightHistory,
    toggleDatePicker,
    saveWeights
})(TrackWeightContainer);
