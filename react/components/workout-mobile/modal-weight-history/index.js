/*jshint esversion: 6 */
import React from 'react';
import OverlayConfirm from '../overlay-confirm';

export default class ModalWeightHistory extends React.Component {
    render() {
        const {history, show, onEditClick} = this.props;
        const dateItems = show ? history.reduce((dateGroups, item) => {
            const lastKey = dateGroups.length - 1;
            if (!dateGroups.length || dateGroups[lastKey][0].date !== item.date) {
                dateGroups.push([item]);
            } else {
                dateGroups[lastKey].push(item);
            }
            return dateGroups;
        }, []).map(dateGroup => {
            const date = dateGroup[0].date;
            const items = dateGroup.map((item, j) => (
                <li key={j}>
                    <p className="weight-set">Set {j + 1}</p>
                    <span className="weight-data">{item.reps || 0} reps x {item.weight || 0}</span>
                </li>
            ));

            return (
                <div key={date} className="weight-item">
                    <div className="weight-header">
                        <p>{date}</p><button onClick={onEditClick.bind(null, date)}>Edit</button>
                    </div>
                    <ul>{items}</ul>
                </div>
            );
        }) : null;

        return (
            <OverlayConfirm {...this.props} title="Weight Lifted History">
                <div className="weight-history-container">{dateItems}</div>
            </OverlayConfirm>
        );
    }
}
