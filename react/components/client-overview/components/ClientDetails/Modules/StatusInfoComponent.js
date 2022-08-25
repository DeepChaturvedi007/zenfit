import React, { createRef, Fragment, useEffect, useRef, useState } from 'react';
import { prepareOptions } from "../../../helpers";


const StatusInfoComponent = (props) => {

    const {
        clientId,
        optionsList,
        actionItemId,
        currentStatus,
        changeAction,
        param
    } = props
    return (
        <Fragment>
            <div className="client-status-list">
                <select
                    type="text"
                    value={currentStatus ? currentStatus : ''}
                    className={"item-status-badge " +
                        (
                            currentStatus === "active" ? "success" :
                                currentStatus === "inactive" ? "paused" :
                                    "pending"
                        )
                    }
                    onChange={(e) => { changeAction(param, e.target.value, actionItemId, clientId) }}
                >
                    {
                        optionsList.map((value, i) => {
                            return <option key={i} value={value}>{value}</option>
                        })
                    }
                </select>
            </div>
        </Fragment>
    )
}

export default StatusInfoComponent
