import React from 'react';
import _ from 'lodash';

const ClientInfoItem = ({ title, value, changeClientInfo }) => {

    const onDataChange = e => {
        changeClientInfo(e.target.value, e.target.name)
    }

    return (
        <div className='client-info-item'>
            <span className="item-title">{_.startCase(title)}</span>
            <div style={{ flex: 1 }} />
            <input onChange={onDataChange} name={title} type='text' value={value} />
        </div>
    );
}

export default ClientInfoItem;
