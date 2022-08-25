import React from 'react'

import Grid from '@material-ui/core/Grid';
import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";
import { green } from '@material-ui/core/colors';

const FollowUp = (props) => {
    const {
        classes,
        leadInfo,
        followUp,
        changeLeadInfo,
        handleFollow
    } = props;
    return(
        <div className={classes.followUpContent}>
            <div className="checkbox">
                <label>
                    <input
                        type="checkbox"
                        checked={followUp}
                        id="durationTime"
                        name="durationTime"
                        onChange={(e) => {handleFollow(!followUp)}}
                    />
                    Follow up on this lead.
                </label>
            </div>
            {followUp && (
                <Grid container spacing={3}>
                    <Grid item xs={12} sm={12}>
                        <div className="input-group-custom">
                            <span className="input-group-addon-custom input-group-addon">
                                <i className="fa fa-calendar"></i>
                            </span>
                            <DateTime
                                name="startDurationTime"
                                className="form-control-custom"
                                autoComplete="off"
                                value={leadInfo.followUpDate}
                                onChange={(e) => {changeLeadInfo(e._d, 'followUpDate')}}
                                dateFormat="YYYY-MM-DD"
                                timeFormat="H:mm"
                                closeOnSelect={true}
                            />
                        </div>
                    </Grid>
                </Grid>
            )}
            <br />
            <div className="form-group">
                <label className="control-label">Sales notes</label>
                <textarea
                    className="form-control"
                    value={leadInfo.salesNotes}
                    name="salesNotes"
                    style={{height: '100px'}}
                    onChange={(e) => {changeLeadInfo(e.target.value, e.target.name)}}
                />
            </div>
        </div>
    )
}

export default FollowUp;
