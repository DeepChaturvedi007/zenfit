import React from 'react';

import clsx from 'clsx';
import Tooltip from '@material-ui/core/Tooltip';
import InfoOutlinedIcon from '@material-ui/icons/InfoOutlined';
import moment from 'moment';
import Cookies from 'js-cookie';
import { CONTACT_TIME } from '../../const';

import withStyle from '../styles';
const LeadItem = (props) => {
    const {
        classes,
        leadsList,
        leadStatus,
        openModal,
        isAdmin,
        showLeadUtm
    } = props;

    const followUp = (date) => {
        return moment(date).format('MMMM Do YYYY, H:mm');
    }
    const openSide = (client) => {
        window.openSideContent(true, client, 1, 'payment-email')
    }

    return (
        leadsList.map((item, i) => {
            return (
                <tr key={i}>
                    <td>
                        <div className='lead-name'>
                            <span className='lead-name-text' onClick={(e) => { openModal(item) }}>
                                {item.name}
                            </span>
                            {item.inDialog && (
                                <Tooltip
                                    title={item.dialogMessage}
                                    classes={{
                                        tooltip: classes.dialogMessage
                                    }}
                                    arrow
                                    placement={"right"}
                                >
                                    <InfoOutlinedIcon style={{ marginRight: 7 }} />
                                </Tooltip>
                            )}
                            <div className='lead-tag'>
                                {item.tags.map((tag, j) => {
                                    return (
                                        <span key={j}>{tag}</span>
                                    )
                                })}
                            </div>
                            <div className='utm-tag'>
                                {(isAdmin || showLeadUtm || Cookies.get('zf-admin') == 1) && item.utm ?
                                  <div>
                                    <span>{item.utm.utm_campaign}</span>
                                    <span>{item.utm.utm_source}</span>
                                    <span>{item.utm.utm_medium}</span>
                                  </div> :
                                ''}
                            </div>
                        </div>
                    </td>
                    <td>
                        <span>{CONTACT_TIME[item.contactTime]}</span>
                    </td>
                    <td>
                        <span>{moment.utc(item.createdAt.date).format('MMM DD, YYYY')}</span>
                    </td>
                    <td className="hidden-xs hidden-sm">
                        <span>{item.phone}</span>
                    </td>
                    <td>
                        <span
                            className={clsx({ 'won': item.status === 3, 'lost': item.status === 4 })}
                        >
                            {leadStatus[item.status]}
                            {(item.client && item.client.payments.length > 0 && item.status === 5) && (
                                <span style={{marginLeft: '5px'}}>
                                  {moment.utc(item.client.payments[0].sent_at.date).format('MMM DD, YYYY')}
                                  <span className="payment-resend" onClick={() => { openSide(item.client) }}>Resend.</span>
                                </span>
                            )}
                        </span>
                    </td>
                    <td className="hidden-xs hidden-sm">
                        <span>
                            {item.followUpAt ? (
                                followUp(item.followUpAt.date)
                            ) : (
                                    "-"
                                )}
                        </span>
                    </td>
                </tr>
            )
        })
    )
}

export default withStyle(LeadItem);
