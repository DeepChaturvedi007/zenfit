import React from 'react';

import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";

import { Col, Row } from "../../../../shared/components/Grid";
import { duration, durationMonth } from '../help-const';

const Duration = (props) => {
    const {
        value,
        durationData,
        handleHelpView,
        handleCheckBox,
        handleDurationChange
    } = props;

    return (
        <div className="checkbox">
            <label>
                <input
                    type="checkbox"
                    checked={value}
                    id="durationTime"
                    name="durationTime"
                    onChange={(e) => { handleCheckBox(!value, e.target.name) }}
                />
                Set client's duration.
            </label>
            <a className="read-more" onClick={() => { handleHelpView(duration.title, duration.content) }}>What is the client's duration?</a>
            {value && (
                <div className="description-box">
                    <small>For instance if you work 3 months with your client, you should set a 3 months duration.</small>
                    <Row className='client-expand-grid'>
                        <Col size={6}>
                            <label htmlFor="startDurationTime" className="control-label client-label">Start Date</label>
                            <div className="input-group-custom">
                                <span className="input-group-addon-custom input-group-addon">
                                    <i className="fa fa-calendar"></i>
                                </span>
                                <DateTime
                                    name="startDurationTime"
                                    className="form-control-custom"
                                    autoComplete="off"
                                    value={durationData.startDate}
                                    onChange={(e) => { handleDurationChange(e._d, 'startDate') }}
                                    dateFormat="YYYY-MM-DD"
                                    timeFormat={false}
                                    closeOnSelect={true}
                                />
                            </div>
                        </Col>
                        <Col size={6}>
                            <label htmlFor="duration" className="client-label">Duration</label>
                            <select
                                id="duration"
                                name="duration"
                                className="form-control"
                                value={durationData.duration}
                                onChange={(e) => { handleDurationChange(e.target.value, e.target.name) }}
                            >
                                {durationMonth.map((item, i) => {
                                    return (
                                        <option value={item.value} key={i}>{item.label}</option>
                                    )
                                })}
                            </select>
                        </Col>
                    </Row>
                </div>
            )}
        </div>
    )
}

export default Duration;