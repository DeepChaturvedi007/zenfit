/*jshint esversion: 6 */
import React from 'react';
import ReactSortable from './react-sortable';

export default class ListSortable extends ReactSortable {
    sortableOptions = {
        delay: 300,
        group: {
            name: 'exercises',
            pull: true,
            put: true
        }
    };

    constructor(props) {
        super(props);

        this.state = {};
        this.sortableOptions.ref = props.ref || `list-sortable-${props.id}`;
        if (props.stateHandler) {
            this.sortableOptions.stateHandler = 'handleState';
            this.handleState = props.stateHandler;
        } else {
            this.state.items = props.items;
        }
    }

    render() {
        let items = this.getItems().map((item) => {
            return (
                <li key={item.id}>
                    <div className={`sortable-item-wrapper ${item.className || ''}`}>
                        <div className="sortable-item">
                            <div className="exercise-item-move handle"/>
                            {item.elem}
                        </div>
                    </div>
                </li>
            );
        });

        return (<ul className="list-sortable" ref={this.sortableOptions.ref}>{items}</ul>);
    }

    getItems() {
        return this.sortableOptions.stateHandler ? this.props.items : this.state.items;
    }

    handleUpdate(evt) {
        this.props.onUpdate && this.props.onUpdate(evt, this.getItems());
    }

    handleSort(evt) {
        this.props.onSort && this.props.onSort(evt, this.getItems());
    }

    handleAdd(evt) {
        this.props.onAdd && this.props.onAdd(evt, this.getItems());
    }

    handleRemove(evt) {
        this.props.onRemove && this.props.onRemove(evt, this.getItems());
    }
}
