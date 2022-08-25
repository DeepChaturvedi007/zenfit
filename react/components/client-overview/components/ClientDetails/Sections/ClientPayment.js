import React, {Fragment, useEffect} from 'react';
import {connect} from 'react-redux';
import Tooltip from '@material-ui/core/Tooltip';
import IconButton from '@material-ui/core/IconButton';
import Popover from '@material-ui/core/Popover';
import MenuItem from '@material-ui/core/MenuItem';
import MoreHorizIcon from '@material-ui/icons/MoreHoriz';
import moment from 'moment';
import Card, {Body} from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import * as clients from "../../../store/clients/actions";
import {STRIPE_CONNECT_URL} from "../../../const";
import DateTime from "react-datetime";

const ClientPayment = (props) => {
    const {
        clientDetail,
        handleSubscriptionModal,
        handleActionModal,
        stripeConnect,
        paymentsLog,
        paymentslogLoading,
        refundClient,
        unsubscribe,
        pauseSubscription
    } = props;

    const payment = clientDetail.payments[0];
    const lastPaymentWarningSent = payment && payment.warnings && payment.warnings.last_warning ?
        `Last warning sent: ${moment(payment.warnings.last_warning.date).format('MMM DD, YYYY')}` :
        '';
    const [anchorEl, setAnchorEl] = React.useState(null);
    const [trialEnd, setTrialEnd] = React.useState(false);
    const [paymentDate, setPaymentDate] = React.useState(false);
    let content = ''
    if (clientDetail.payments.length !== 0) {
        const months = `${payment.months} months`;
        const duration = (payment.months === 13) ? 'until client unsubscribes' : `for ${months}`;
        const sentAtDate = payment.sent_at ? moment.utc(payment.sent_at.date) : null;
        const endDateSent = sentAtDate ? sentAtDate.add(payment.months, 'months').format("ll") : '';
        const ends = (payment.months === 13 || payment.months === 0)
            ? ''
            : 'End date: ' + endDateSent + '.';
        const starts = payment.trial_end ? `Start date: ${moment.unix(payment.trial_end).format('MMM DD, YYYY')}.` : '';
        const currency = payment.currency;
        const recurring_fee = parseFloat(payment.recurring_fee).toFixed(2) + "/mo";
        const upfront_fee = (payment.upfront_fee !== '0' && payment.upfront_fee) ? `, with a ${currency}${payment.upfront_fee} upfront fee` : '';
        content = `${currency}${recurring_fee} ${duration}${upfront_fee}. ${starts} ${ends}`;
    } else {
        content = `Client is not setup for automatic payments, click here to set it up.`;
    }
    const openPaymentModal = () => {
        if (clientDetail.payments.length === 0) {
            handleSubscriptionModal(true, clientDetail)
        }
    }
    const handleClick = (event) => {
        setAnchorEl(event.currentTarget);
    };

    const handleClose = () => {
        setAnchorEl(null);
    };

    const openSubscribeModal = () => {
        handleSubscriptionModal(true, clientDetail);
        setAnchorEl(null);
    }
    const open = Boolean(anchorEl);
    const id = open ? 'simple-popover' : undefined;

    useEffect(() => {
        if (trialEnd) {
            handleActionModal(
                true,
                <PaymentDateComponent
                    title={"Pause subscription"}
                    description={"The subscription will be paused until:"}
                    state={trialEnd}
                    setState={setTrialEnd}
                />,
                () => pauseSubscription(clientDetail.id, moment(trialEnd).unix(), true)
            )
        }
    }, [trialEnd]);

    useEffect(() => {
        if (paymentDate) {
            handleActionModal(
                true,
                <PaymentDateComponent
                    title={"Adjust payment date"}
                    description={"The date of the payment will be changed to..."}
                    state={paymentDate}
                    setState={setPaymentDate}
                />,
                () => pauseSubscription(clientDetail.id, moment(paymentDate).unix(), false)
            )
        }
    }, [paymentDate]);

    let today = moment.now();
    let valid = function (current) {
        return current.isAfter(today);
    };

    const PaymentDateComponent = ({title, description, state, setState}) => {
        return (
            <div className="modal-body">
                <h2>{title}</h2>
                <p>{description}</p>
                <DateTime
                    timeFormat={false}
                    closeOnSelect={true}
                    dateFormat="MMM DD YYYY"
                    onChange={(e) => setState(e)}
                    value={state}
                    isValidDate={valid}
                    style={{maxWidth: 100, fontSize: 12}}
                />
            </div>
        )
    }

    if (!stripeConnect && !clientDetail.demoClient) {
        return (
            <Card className='client-payment'>
                <PowerHeader title={'Payments'}/>
                <Fragment>
                    <Body>
                        <React.Fragment>
                            <div className="alert alert-warning">You are almost ready to start accepting payments from
                                your clients.
                            </div>
                            <button className="btn btn-success" onClick={() => {
                                window.location.href = STRIPE_CONNECT_URL
                            }} style={{marginBottom: 15, alignSelf: "start"}}>Setup Stripe
                            </button>
                        </React.Fragment>
                    </Body>
                </Fragment>
            </Card>
        )
    }

    return (
        <Card className='client-payment'>
            <PowerHeader
                title={'Payments'}
            >
                <IconButton component="span" size="small" aria-describedby={id} onClick={handleClick}>
                    <MoreHorizIcon/>
                </IconButton>
                <Popover
                    id={id}
                    open={open}
                    anchorEl={anchorEl}
                    onClose={handleClose}
                    anchorOrigin={{
                        vertical: 'bottom',
                        horizontal: 'right',
                    }}
                    transformOrigin={{
                        vertical: 'top',
                        horizontal: 'right',
                    }}
                >
                    <MenuItem onClick={openSubscribeModal} style={{fontSize: 13}}>Create new subscription</MenuItem>
                    {(payment && payment.status === 'active') && (
                        <React.Fragment>
                            <MenuItem
                                onClick={() => {
                                    setTrialEnd(moment(moment.now()).add(1, "M"));
                                    setAnchorEl(null);
                                }}
                                style={{fontSize: 13}}
                            >
                                Pause subscription
                            </MenuItem>
                            <MenuItem
                                onClick={() => {
                                    setPaymentDate(moment(moment.now()).add(1, "M"))
                                    setAnchorEl(null);
                                }}
                                style={{fontSize: 13}}
                            >
                                Adjust payment date
                            </MenuItem>
                            <MenuItem
                                onClick={() => {
                                    handleActionModal(
                                        true,
                                        `Are you sure you wish to unsubscribe ${clientDetail.name}`,
                                        () => unsubscribe(clientDetail.id)
                                    );
                                    setAnchorEl(null);
                                }}
                                style={{fontSize: 13}}
                            >
                                Unsubscribe client
                            </MenuItem>
                        </React.Fragment>
                    )}
                </Popover>
            </PowerHeader>

            <Fragment>
                <Body>
                    {payment ? (
                        <React.Fragment>
                            <div className="client-payment-list">
                                <div className="list-item">
                                    <p className="item-title">Status</p>
                                    <p className="item-value">
                                        {payment.status === 'active' && (
                                            <span className="item-status-badge success">Active</span>
                                        )}
                                        {payment.status === 'will_start' && (
                                            <span
                                                className="item-status-badge success">Starts {moment.unix(payment.trial_end).format('MMM DD, YYYY')}</span>
                                        )}
                                        {payment.status === 'paused' && (
                                            <span
                                                className="item-status-badge paused">Paused until {moment.unix(payment.paused_until).format('MMM DD, YYYY')}</span>
                                        )}
                                        {payment.status === 'pending' && (
                                            <span className="item-status-badge pending">Pending</span>
                                        )}
                                        {payment.status === 'canceled' && (
                                            <span className="item-status-badge cancel">Canceled</span>
                                        )}
                                    </p>
                                </div>
                                <div className="item-divider"/>
                                <div className="list-item">
                                    <p className="item-title">Created</p>
                                    <p className="item-value">
                                        {payment.sent_at ? moment(payment.sent_at.date).format('MMM DD, YYYY') : ''}
                                    </p>
                                </div>
                                <div className="item-divider"/>
                                <div className="list-item">
                                    <p className="item-title">Terms</p>
                                    {payment.terms ? (
                                        <Tooltip title={payment.terms} arrow placement='top'
                                                 classes={{tooltip: "payment-tooltip"}}>
                                            <p className="item-value terms">
                                                <img src="/bundles/app/protect.png" alt={"protect"}/>
                                                {payment.status === 'active' ? 'Accepted' : 'Pending'}
                                            </p>
                                        </Tooltip>
                                    ) : (
                                        <p className="item-value terms">-</p>
                                    )}
                                </div>
                                {/*
                                <div className="item-divider"></div>
                                <div className="list-item">
                                    <p className="item-title">Warnings</p>
                                    {payment.warnings && payment.warnings.last_warning ? (
                                        <p className="item-value">
                                          <Tooltip title={lastPaymentWarningSent} arrow placement='top' classes={{ tooltip: "payment-tooltip" }}>
                                              <span className="item-status-badge cancel">{payment.warnings.warning_count}</span>
                                          </Tooltip>
                                        </p>
                                    ) : (
                                        <p className="item-value">-</p>
                                    )}
                                </div>*/}
                            </div>

                            <div className="client-payment-message">
                                <div
                                    className={"message-content " + (clientDetail.payments.length === 0 ? 'payment-empty' : "")}
                                    onClick={openPaymentModal}
                                >
                                    {content}
                                </div>
                            </div>

                            <div className="client-payment-history">
                                <div className="history-item header">
                                    <div className="item-list">
                                        Date
                                    </div>
                                    <div className="item-list" style={{textAlign: 'right'}}>
                                        Amount
                                    </div>
                                </div>
                                <div className="history-content">
                                    {paymentslogLoading ? (
                                        <div className="history-empty">Loading...</div>
                                    ) : (
                                        <Fragment>
                                            {paymentsLog.map((item, i) => {
                                                let className = '';
                                                let label = '';
                                                let icon = null;
                                                let refund = null;
                                                if (item.type === 'invoice.payment_succeeded') {
                                                    className = 'success';
                                                    label = 'Paid';
                                                    refund = item.charge !== null ?
                                                        <svg
                                                            className={"refundIcon"}
                                                            onClick={() => handleActionModal(true, `Are you sure you wish to refund ${clientDetail.name}?`, () => refundClient(clientDetail.id, item.charge))}
                                                            height="12" width="12" viewBox="0 0 16 16"
                                                            xmlns="http://www.w3.org/2000/svg">
                                                            <path
                                                                d="M10.5 5a5 5 0 0 1 0 10 1 1 0 0 1 0-2 3 3 0 0 0 0-6l-6.586-.007L6.45 9.528a1 1 0 0 1-1.414 1.414L.793 6.7a.997.997 0 0 1 0-1.414l4.243-4.243A1 1 0 0 1 6.45 2.457L3.914 4.993z"
                                                                fillRule="evenodd"/>
                                                        </svg>
                                                        : null;
                                                } else if (item.type === 'charge.succeeded') {
                                                    className = 'success';
                                                    label = 'Paid';
                                                    icon = <img
                                                        src="https://x.klarnacdn.net/payment-method/assets/badges/generic/klarna.png"/>;
                                                } else if (item.type === 'invoice.payment_failed') {
                                                    className = 'fail';
                                                    label = 'Failed';
                                                } else if (item.type === 'charge.refunded') {
                                                    className = 'refunded';
                                                    label = 'Refunded';
                                                }

                                                return (
                                                    (item.type !== 'customer.subscription.created' && item.type !== 'customer.subscription.deleted') && (
                                                        <div className="history-item" key={i}>
                                                            <div className="item-list">
                                                                {item.createdAt ? moment(item.createdAt.date).format('MMM DD, YYYY') : '-'}
                                                            </div>
                                                            <div className="item-list" style={{textAlign: 'right'}}>
                                                                {refund}
                                                                {icon}
                                                                <span className={className}>{label}</span>
                                                                {item.amount ? item.amount : '-'}
                                                                <span className="currency">{" " + item.currency}</span>
                                                            </div>
                                                        </div>
                                                    )
                                                )
                                            })}
                                            {paymentsLog.length === 0 && (
                                                <div className="history-empty">No Payment history</div>
                                            )}
                                        </Fragment>
                                    )}
                                </div>
                            </div>
                        </React.Fragment>
                    ) : (
                        <React.Fragment>
                            <div className="alert alert-warning">Client is not setup for automatic payments.</div>
                            <button className="btn btn-success" onClick={openPaymentModal}
                                    style={{marginBottom: 15}}>Setup Payment
                            </button>
                        </React.Fragment>
                    )}
                </Body>
            </Fragment>
        </Card>
    );
}

function mapStateToProps(state) {
    return {
        stripeConnect: state.clients.stripeConnect,
        paymentsLog: state.clients.clientPaymentsLog,
        paymentslogLoading: state.clients.paymentslogLoading
    }
}

export default connect(mapStateToProps, {...clients})(ClientPayment);
