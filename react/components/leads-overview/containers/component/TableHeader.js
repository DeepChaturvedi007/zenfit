import React, { Fragment } from 'react';

import clsx from 'clsx';

const TableHeader = (props) => {
    const {
        classes,
        tableHead,
        sortField,
        handleSort
    } = props;
    return(
        <thead className="hidden-xs hidden-sm">
            <tr>
                {tableHead.map((item, i) => {
                    return(
                        <th
                            key={i}
                            style={item.sortable ? {cursor: 'pointer'}: {}}
                            onClick={() => {
                                item.sortable ? handleSort(item.sortKey) : null
                            }}
                            className={classes.thContent}
                        >
                            <span className={classes.thText}>{item.value}</span>
                            {item.sortable && (
                                <div className={classes.sortArray}>
                                    <div className={clsx({'up': true, 'active': (sortField[item.sortKey] === 'asc')})} />
                                    <div className={clsx({'down': true, 'active': (sortField[item.sortKey] === 'desc')})} />
                                </div>
                            )}
                        </th>
                    )
                })}
            </tr>
        </thead>
    )
}

export default TableHeader;