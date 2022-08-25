import React from 'react'

const FieldHelp = (props) => {
    const {
        title,
        content
    } = props;
    
    return (
        <div className="text-left">
            <h4 className="modal-title">{title}</h4>
            <p>{content[0]}</p>
            <p>{content[1]}</p>
        </div>
    )
}

export default FieldHelp;