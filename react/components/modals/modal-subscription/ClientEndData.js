import React, { useState } from 'react'

import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";

import moment from 'moment';

const ClientEndData = (props) => {
    const {durationTime, setDurationTime, duration, startDurationTime, onChangeData} = props;

    const changeDuration = (e) => {
        onChangeData(e.target.value, e.target.name)
    }
    const changeDurationDate = (e) => {
        onChangeData(e._d, 'startDurationTime')
    }
    return(
        <div className="checkbox">
            <label>
                <input type="checkbox" checked={durationTime} onChange={(e) => {setDurationTime(!durationTime)}}/>
                Set duration time for client.
            </label>
            {durationTime && (
                <div className="box-hidden">
                    <small>For instance if you work 3 months with your client, you can set a 3 months duration.</small>
                    <div className="row duration-time">
                        <div className="col-sm-6">
                            <label htmlFor="startDurationTime" className="control-label">Start Date</label>
                            <div className="input-group-custom">
                                <span className="input-group-addon-custom">
                                    <i className="fa fa-calendar"></i>
                                </span>
                                <DateTime
                                    name="startDurationTime"
                                    className="form-control-custom"
                                    autoComplete="off"
                                    value={startDurationTime}
                                    onChange={changeDurationDate}
                                    dateFormat="YYYY-MM-DD"
                                    timeFormat={false}
                                    closeOnSelect={true}
                                    // defaultValue={moment.utc().format('MM/DD/YYYY')}
                                />
                            </div>
                        </div>
                        <div className="col-sm-6">
                            <label htmlFor="duration">Duration</label>
                            <select
                                id="duration"
                                name="duration"
                                className="form-control select-track-progress"
                                value={duration ? duration : ''}
                                onChange={changeDuration}
                            >
                                <option value="">No end date</option>
                                <option value="1">1 Month</option>
                                <option value="2">2 Months</option>
                                <option value="3">3 Months</option>
                                <option value="4">4 Months</option>
                                <option value="5">5 Months</option>
                                <option value="6">6 Months</option>
                                <option value="7">7 Months</option>
                                <option value="8">8 Months</option>
                                <option value="9">9 Months</option>
                                <option value="10">10 Months</option>
                                <option value="11">11 Months</option>
                                <option value="12">12 Months</option>
                            </select>
                        </div>
                    </div>
                </div>
            )}
        </div>
    )
}

export default ClientEndData