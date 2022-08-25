/*jshint esversion: 6 */
import React from 'react';
import OverlayConfirm from '../overlay-confirm';

export default class ModalTrackWeight extends React.Component {
    itemsFocus = {};

    componentWillReceiveProps(newProps) {
        const {items: newItems, show} = newProps;
        const {items: oldItems} = this.props;
        this.itemsFocus = {};

        if (show && newItems.length && oldItems.length) {
            this.itemsFocus = {};
            newItems.forEach(newItem => {
                this.itemsFocus[newItem.id] = {};
                oldItems.forEach(oldItem => {
                    if (newItem.id === oldItem.id) {
                        this.itemsFocus[newItem.id].reps = oldItem.reps === null && oldItem.reps !== newItem.reps;
                        this.itemsFocus[newItem.id].weight = oldItem.weight === null && oldItem.weight !== newItem.weight;
                    }
                });
            });
        }
    }

    render() {
        const {items, onToggleDatePicker, onAddNewSet, onSetDelete, onHistoryClick} = this.props;
        const itemsElements = items.map((item, i) => {
            const deleteBtn = i > 0
                ? <button onClick={onSetDelete.bind(null, item)}><i className="material-icons">delete</i></button>
                : null;
            const isRepsNull = item.reps === null;
            const reps = this.getStatBlock(item.id, item.reps, 'reps');
            const weight = this.getStatBlock(item.id, item.weight, 'weight');

            return (
                <tr key={i}>
                    <td><div className="wrapper">Set {i + 1}</div></td>
                    <td>
                        <div className={`wrapper ${!isRepsNull ? 'wrapper-flex' : ''}`}>
                            {reps}{!isRepsNull ? ' reps' : ''}
                        </div>
                    </td>
                    <td><div className="wrapper">{weight}</div></td>
                    <td><div className="wrapper">{deleteBtn}</div></td>
                </tr>
            );
        });
        const date = items.length ? items[0].date : '';

        return (
            <OverlayConfirm {...this.props} title="Track Weight">
                <div className="track-weight-container">
                    <div className="track-date">
                        <p>Date</p><button onClick={onToggleDatePicker}>{date}</button>
                    </div>
                    <div className="track-content">
                        <table>
                            <thead>
                            <tr>
                                <th>Set</th>
                                <th>Reps</th>
                                <th>Weight</th>
                                <th/>
                            </tr>
                            </thead>
                            <tbody>
                            {itemsElements}
                            </tbody>
                        </table>
                        <div className="track-add-btn">
                            <button onClick={onAddNewSet}>+ Add Set</button>
                        </div>
                    </div>
                    <div className="track-history-btn">
                        <button onClick={onHistoryClick}>See History</button>
                    </div>
                </div>
            </OverlayConfirm>
        );
    }

    getStatBlock(id, value, key) {
        const {onStatChange} = this.props;
        const isFocus = this.itemsFocus[id] && this.itemsFocus[id][key];

        return value !== null
            ? <input type="number"
                     name={key}
                     value={value}
                     ref={input => {isFocus && input && input.focus()}}
                     onChange={onStatChange.bind(null, id)}/>
            : <button onClick={onStatChange.bind(null, id, {target: {name: key, value: ''}})}>+ Add</button>;
    }
}
