/*jshint esversion: 6 */
import 'whatwg-fetch';

import React from 'react';
import ListSortable from '../../list-sortable';
import Spinner from '../../../spinner';
import {
  GET_WORKOUT_PLAN_DAYS,
  SAVE_WORKOUT_PLAN,
} from '../../../../api/workout-api';

export default class DaysList extends React.Component {
    commentLength = 120;

    constructor(props) {
        super(props);

        this.state = {
            items: [],
            isLoading: true
        };
    }

    componentWillMount() {
        const { isTemplate, planId } = this.props;

        const url = GET_WORKOUT_PLAN_DAYS(planId);
        fetch(url, {
            credentials: 'include'
        }).then(response => {
            response.json().then(data => {
                this.setState({items: data, isLoading: false});
            });
        });

        this.modalAddDay = $('#addPlanModal');
    }

    render() {
        let items = this.state.items.map((item) => {
            const comment = item.comment && item.comment.length > this.commentLength
                ? item.comment.substring(0, this.commentLength) + '...'
                : item.comment;
            const url = this.props.itemUrl.replace(':id', item.id);

            return {
                id: item.id,
                comment: item.comment,
                elem: (
                    <div className="workout-days-item" data-day-href={url}>
                        <div className="workout-days-text">
                            <h4>{item.name}</h4>
                            {comment && <p>{comment}</p>}
                        </div>
                        <div>
                            <a href="#" className="view-link">
                                <i className="fa fa-angle-right" aria-hidden="true"/>
                            </a>
                        </div>
                    </div>
                )
            };
        });

        return (
            <div>
                <ListSortable items={items} id={this.props.planId} onUpdate={this.handleUpdate.bind(this)}/>
                <Spinner show={this.state.isLoading}/>
                <div className="add-day-btn">
                    <button onClick={this.handleAddDay.bind(this)}>+ Add workout day</button>
                </div>
            </div>
        );
    }

    handleUpdate(evt, items) {
        const {planId, isTemplate} = this.props;
        const data = {
            results: items.map((item, i) => {
                return {
                    day_id: item.id,
                    workoutDayComment: item.comment,
                    order: ++i
                };
            })
        };
        const url = SAVE_WORKOUT_PLAN(planId);

        fetch(url, {
            method: 'post',
            credentials: 'include',
            body: JSON.stringify(data)
        });
    }

    handleAddDay() {
        this.modalAddDay.modal('show');
    }
}
