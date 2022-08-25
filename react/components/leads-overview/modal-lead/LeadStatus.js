import React from 'react';
import clsx from 'clsx';

import Tags from './Tags';

import { STATUS_LIST } from './const';
import { Fragment } from 'react';
const LeadStatus = (props) => {
    const {
        classes,
        leadInfo,
        isAssistant,
        tagsList,
        changeLeadInfo
    } = props;

    const status = leadInfo.leadStatus;

    return(
        <Fragment>
            <div className="form-group">
                <label className="control-label">Status</label>
                <div className={classes.statusContent + ""}>
                    {(status === 5)||(status === 3) ? (
                        <Fragment>
                            {STATUS_LIST.map((item, i) => {
                                return(
                                    <div
                                        className={clsx(classes.statusItem, classes.disabled, {'left': i === 0, 'right': (i === 3 && status !== 5), 'active': status === item.key })} key={i}

                                    >
                                        {item.value}
                                    </div>
                                )
                            })}
                            <div className={clsx(classes.statusItem, 'right', 'active')}>
                                {status === 3 ? (
                                  <Fragment>Won</Fragment>
                                ) : (
                                  <Fragment>Offer Sent</Fragment>
                                )}
                            </div>
                        </Fragment>
                    ) : (
                        STATUS_LIST.map((item, i) => {
                            return(
                                <div
                                    className={clsx(classes.statusItem, {'left': i === 0, 'right': (i === 3 && status !== 5), 'active': status === item.key })} key={i}
                                    onClick={() => {changeLeadInfo(item.key, 'leadStatus')}}
                                >
                                    {item.value}
                                </div>
                            )
                        })
                    )}
                </div>
            </div>
            {!isAssistant && (
                <Tags
                    tags={tagsList}
                    handleChange={changeLeadInfo}
                    leadInfo={leadInfo}
                />
            )}
        </Fragment>
    )
}

export default LeadStatus
