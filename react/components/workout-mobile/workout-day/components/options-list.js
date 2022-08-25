/*jshint esversion: 6 */
import React from 'react';
import ModalOptionsList from '../../modal-options-list';

export default class OptionsList extends React.Component {

    editDayNameModal = '#addPlanModal';
    deleteWorkoutDay = '#deleteModal';

    constructor(props) {
        super(props);

        this.state = {
            show: false
        };
    }

    render() {
        return (
            <div className="workouts-header-left text-right">
                <span className="position-rel">
                    <button className="btn exercises-btn options-list-btn" onClick={this.showModal.bind(this)}>
                        <span className="icon-dots" />
                    </button>
                </span>
                <ModalOptionsList show={this.state.show} closeModal={this.closeModal.bind(this)}>
                    <li onClick={this.itemClickHandler.bind(this, this.editDayNameModal)}>Rename Workout Day</li>
                    <li onClick={this.clickHandler.bind(this)}>Add New Exercise</li>
                    <li className="text-danger" onClick={this.itemClickHandler.bind(this, this.deleteWorkoutDay)}>
                        Delete Workout Day
                    </li>
                </ModalOptionsList>
            </div>
        );
    }

    showModal() {
        this.setState({show: true});
    }

    closeModal() {
        this.setState({show: false});
    }

    itemClickHandler(id) {
        this.closeModal();
        $(id).modal('show');
    }

    clickHandler() {
        const {addExercisesClick} = this.props;
        addExercisesClick();
        this.closeModal();
    }
}
