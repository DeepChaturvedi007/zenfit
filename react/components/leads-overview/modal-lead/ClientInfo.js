import React, { useEffect } from 'react'

import Grid from '@material-ui/core/Grid';
const ClientInfo = (props) => {
    const {
        classes,
        leadInfo,
        changeLeadInfo
    } = props

    const clientDataChange = (e) => {
        changeLeadInfo(e.target.value, e.target.name)
    }
    return (
        <div className={classes.clientInfoContent}>
            <Grid container spacing={3}>
                <Grid item xs={12} sm={12}>
                    <div className="form-group">
                        <label htmlFor="clientName" className="control-label">Name</label>
                        <input
                            type="text"
                            id="clientName"
                            name="name"
                            className="form-control"
                            placeholder="Lead's name"
                            value={leadInfo.name}
                            onChange={clientDataChange}
                        />
                    </div>
                </Grid>
            </Grid>
            <Grid container spacing={3}>
                <Grid item xs={12} sm={6}>
                    <div className="form-group">
                        <label htmlFor="clientEmail" className="control-label">E-mail</label>
                        <input
                            type="text"
                            id="clientEmail"
                            name="email"
                            className="form-control"
                            placeholder="Lead’s E-Mail"
                            value={leadInfo.email}
                            onChange={clientDataChange}
                        />
                    </div>
                </Grid>
                <Grid item xs={12} sm={6}>
                    <div className="form-group">
                        <label htmlFor="clientPhone" className="control-label">Phone</label>
                        <input
                            type="text"
                            id="clientPhone"
                            name="phone"
                            className="form-control"
                            placeholder="Lead’s Phone"
                            value={leadInfo.phone}
                            onChange={clientDataChange}
                        />
                    </div>
                </Grid>
            </Grid>
            <Grid item xs={12} sm={12}>
                {leadInfo.dialogMessage && (
                    <div className="form-group">
                        <label className="control-label">Info</label>
                        <p style={{ whiteSpace: 'pre-wrap' }}>{leadInfo.dialogMessage}</p>
                    </div>
                )}
            </Grid>
        </div>
    )
}

export default ClientInfo;
