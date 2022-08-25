import React from 'react';

import Tooltip from '@material-ui/core/Tooltip';

import Tags from './Tags';

const ClientForm = (props) => {
    const {
        clientData,
        handleChange,
        handleTagChange,
        error
    } = props;

    const clientDataChange = (e) => {
        handleChange(e.target.value, e.target.name);
    }
    return (
        <div>
            <div className="modal-body">
                {error !== '' && (
                    <div className="notify alert alert-danger">
                        {error}
                    </div>
                )}
                <div className="form-group">
                    <label htmlFor="newClientName" className="control-label">Client's Name</label>
                    <input
                        type="text"
                        id="newClientName"
                        name="clientName"
                        className="form-control"
                        placeholder="Your Client’s Full Name"
                        value={clientData.clientName}
                        onChange={clientDataChange}
                    />
                </div>
                <div className="form-group">
                    <label htmlFor="newClientEmail" className="control-label">Client's E-mail</label>
                    <input
                        type="text"
                        id="newClientEmail"
                        name="clientEmail"
                        className="form-control"
                        placeholder="Enter Client’s E-mail"
                        value={clientData.clientEmail}
                        onChange={clientDataChange}
                    />
                </div>
                <div className="form-group">
                    <label htmlFor="newClientEmail" className="control-label">Add tags</label>
                    <Tags
                        handleChange={handleTagChange}
                    />
                </div>
            </div>
            <div className="modal-footer">
                <button className="btn btn-success btn-upper btn-block">add client</button>
            </div>
        </div>
    )
}

export default ClientForm;