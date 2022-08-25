import React from 'react';
import WorkoutsDayOptionsList from './options-list';

export default class WorkoutsHeader extends React.Component {
    render() {
        const {url, btnClass, dayName, workoutPlanName, addExercisesClick} = this.props;
        return (
            <header className="ibox-header no-border">
                <div className="workouts-header-left">
                    <a role="button" className={btnClass} href={url}>
                        <i className="fa fa-arrow-left"></i> Back
                    </a>
                </div>
                <div className="workouts-header-left m-l">
                    <h4 className="text-center">{dayName}</h4>
                    <p className="text-center">{workoutPlanName}</p>
                </div>
                <WorkoutsDayOptionsList addExercisesClick={addExercisesClick} />
            </header>
        );
    }
}
