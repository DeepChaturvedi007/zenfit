import React, { useState } from 'react';

import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";

import moment from 'moment';

const Month = (props) => {
    const { months, specificDate, startPaymentDate, spaceDateSetFlg, setSpaceDateSetFlg, onChangeData, defaultMonths } = props;

    const monthChange = (e) => {
        onChangeData(e.target.value, e.target.name)
    }
    const spaceDateSet = (e) => {
        onChangeData(!specificDate, e.target.name)
    }
    const changeSpaceDate = (e) => {
        onChangeData(e._d, 'startPaymentDate')
    }
    var yesterday = moment();
    var valid = function (current) {
        return current.isAfter(yesterday);
    };
    return (
        <div className="row duration-time top-padding">
            <div className="col-sm-6">
                <label htmlFor="periods" className="control-label">Months</label>
                <select
                    id="periods"
                    name="periods"
                    className="form-control select-track-progress"
                    value={months}
                    onChange={monthChange}
                >
                    <option value="-1">Pick # of months</option>
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
                    <option value="13">Until client unsubscribes</option>
                    <option value="0">Charge whole payment upfront</option>
                </select>
            </div>
            <div className="col-sm-6">
                <label htmlFor="startPaymentDate" className="control-label">Subscription initial charge</label>
                {spaceDateSetFlg && (
                    <DateTime
                        timeFormat={false}
                        dateFormat="YYYY-MM-DD"
                        onChange={changeSpaceDate}
                        value={startPaymentDate}
                        isValidDate={valid}
                        closeOnSelect={true}
                    />
                )}
                <small className="date-pay">
                    <span
                        className={"when-client-pays " + (!spaceDateSetFlg ? '' : 'active')}
                        onClick={() => { setSpaceDateSetFlg(false) }}
                    >
                        When client pays
                    </span>
                    <span className="or-separator"> / </span>
                    <span
                        className={"specific-date " + (spaceDateSetFlg ? '' : 'active')}
                        onClick={() => { setSpaceDateSetFlg(true) }}
                    >
                        Select specific date.
                    </span>
                </small>
                {spaceDateSetFlg && (
                    <div className="delay-upfront-container" >
                        <label htmlFor="startPaymentDate" className="control-label">Charge upfront fee immediately</label>
                        <label className="pretty-switch">
                            <input type="checkbox" name="chargeUpfrontImmediately" onChange={spaceDateSet} checked={specificDate} />
                            <span className="slider"></span>
                        </label>
                    </div>
                )}
            </div>
        </div>
    )
}

export default Month
