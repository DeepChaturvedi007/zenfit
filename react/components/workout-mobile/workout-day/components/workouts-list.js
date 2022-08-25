/*jshint esversion: 6 */

import React from 'react';
import ListSortable from '../../list-sortable';
import Spinner from '../../../spinner';
import TextareaAutosize from 'react-textarea-autosize';
import ModalOptionsList from '../../modal-options-list';

export default class WorkoutsList extends React.Component {
    iterator = 0;

    render() {
        const {
            dayId,
            workouts,
            isLoading,
            dayComment,
            onSortableUpdate,
            onSortableAdd,
            onCommentChange,
            isShowOptionsList,
            onCloseOptionsList,
            onAddToSuperSet,
            onDeleteSuperSet
        } = this.props;
        const items = this.getWorkoutItems();
        const noExercisesClass = workouts.length ? '' : 'add-exercise-btn-empty';

        return (
            <div>
                <div className="position-rel">
                    <ListSortable id={dayId}
                                  items={items}
                                  onUpdate={onSortableUpdate}
                                  onAdd={onSortableAdd}
                    />
                </div>
                <ModalOptionsList show={isShowOptionsList}
                                  closeModal={onCloseOptionsList}>
                    <li onClick={onAddToSuperSet}>Add Exercise(s) To Super Set</li>
                    <li className="text-danger" onClick={onDeleteSuperSet}>Delete Super Set</li>
                </ModalOptionsList>
                <Spinner show={isLoading}/>
                <div className={`add-exercise-btn ${noExercisesClass}`}>
                    <div className="position-rel">
                        <button onClick={this.handleClick.bind(this)}>
                            + Add Exercise(s)
                        </button>
                    </div>
                </div>
                <TextareaAutosize className="workout-day-comment"
                                  placeholder="Comment to Workout Day"
                                  value={dayComment}
                                  onChange={onCommentChange}
                />
            </div>
        );
    }

    getWorkoutItems() {
        const {
            workouts,
            onSortableUpdate,
            onSortableAdd,
            onShowOptionsList
        } = this.props;

        return workouts.map((item, i) => {
            if (item.supers.length) {
                const supersItems = item.supers.map((superItem) => {
                    return this.getSortableItem(superItem, item.id);
                });
                return {
                    id: this.getItemID(item),
                    className: 'workout-super-set',
                    elem: (
                        <div>
                            <div className="workout-super-set-header">
                                <span>Super Set</span>
                                <button className="exercises-btn"
                                        onClick={onShowOptionsList.bind(null, item.id)}
                                >
                                    <i className="icon-dots"/>
                                </button>
                            </div>
                            <div className="sortable-item-wrapper">
                                <div className="sortable-item">
                                    <div className="exercise-item-move handle"/>
                                    {this.getSortableItem(item, null, true).elem}
                                </div>
                            </div>
                            <ListSortable items={supersItems}
                                          id={item.id}
                                          parentId={item.id}
                                          onUpdate={onSortableUpdate}
                                          onAdd={onSortableAdd}/>
                            <span>End of Super Set</span>
                        </div>
                    )
                };
            } else {
                return this.getSortableItem(item);
            }
        });
    }

    getSortableItem(item, parentId, isSuperSetWorkout = false) {
        const {onItemClick} = this.props;
        return {
            id: this.getItemID(item),
            elem: (
                <div className="day-workout-item" onClick={onItemClick.bind(null, item.id, parentId, isSuperSetWorkout)}>
                    <div className="workout-item-image">
                        <img src={item.exercise.picture}/>
                    </div>
                    <div className="workout-item-text">
                        <h4>{item.exercise.name}</h4>
                        <table>
                            <thead>
                            <tr>
                                <th>Sets</th>
                                <th>Reps</th>
                                <th>Rest (s)</th>
                                <th>Weight</th>
                                <th>Tempo</th>
                                <th>RM</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{item.sets || 0}</td>
                                <td>{item.reps || 0}</td>
                                <td>{item.rest || 0}</td>
                                <td>{item.startWeight || '-'}</td>
                                <td>{item.tempo || '-'}</td>
                                <td>{item.rm || '-'}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            )
        };
    }

    getItemID(item) {
        ++this.iterator;
        return `${item.id}-${item.exercise.id}-${this.iterator}`;
    }

    handleClick() {
        const {addExercisesClick} = this.props;
        addExercisesClick();
    }
}
