import React from 'react';

const CreateItem = (props) => {
    const {onClick, messageType} = props;

    return (
        <span className="create-item" onClick={() => {
            onClick('', '', '', messageType);
        }}>
            Create new
        </span>
    );
};

export default CreateItem;
