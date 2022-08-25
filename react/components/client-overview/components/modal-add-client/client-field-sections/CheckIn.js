import React from 'react'

import { Col, Row } from "../../../../shared/components/Grid";
import { checkIn, week } from '../help-const';

const CheckIn = (props) => {
    const {
        value,
        trackProgressDay,
        handleHelpView,
        handleCheckBox,
        handleTrackProgressDay,
        defaultCheckInDay
    } = props;

    React.useEffect(() => {
        if (defaultCheckInDay) {
            handleTrackProgressDay(defaultCheckInDay);
        }
    }, [])

    return (
        <div className="checkbox">
            <label>
                <input
                    type="checkbox"
                    checked={value}
                    id="trackProgress"
                    name="trackProgress"
                    onChange={(e) => { handleCheckBox(!value, e.target.name) }}
                />
                Setup client's weekly check-in day.
            </label>
            <a className="read-more" onClick={() => { handleHelpView(checkIn.title, checkIn.content) }}>What is that?</a>
            {value && (
                <div className="description-box">
                    <small>Pick which day on the week, that the client should check in.</small>
                    <Row className='client-expand-grid'>
                        <Col size={6}>
                            <label htmlFor="dayTrackProgress" className="client-label">Check-in day</label>
                            <select
                                id="dayTrackProgress"
                                name="dayTrackProgress"
                                className="form-control"
                                value={trackProgressDay}
                                onChange={(e) => { handleTrackProgressDay(e.target.value) }}
                            >
                                {week.map((item, i) => {
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

export default CheckIn;