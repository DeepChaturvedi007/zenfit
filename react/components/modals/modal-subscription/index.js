/*jshint esversion: 6 */
import './styles.scss';
import React, { useCallback, useEffect, useState } from 'react';
import Modal from '@material-ui/core/Modal';

import SubscriptionStatus from './SubscriptionStatus';
import Currency from './Currency';
import AmountInput from './AmountInput';
import Month from './Month';
import Terms from './Terms';
import moment from 'moment';
export default function ModalSubscription(props) {
    const {
        show,
        client,
        locale,
        errorMessage,
        handleSubmit,
        onClose
    } = props;
    const [paymentData, setPaymentData] = useState({
        chargeUpfrontImmediately: true,
        currency: 'usd',
        signUpFee: '',
        monthlyAmount: '',
        periods: null,
        duration: null,
        startDurationTime: new Date,
        startPaymentDate: new Date,
        terms: null
    })
    const [spaceDateSetFlg, setSpaceDateSetFlg] = useState(false);
    const [durationTime, setDurationTime] = useState(false);

    const handleChange = (value, name) => {
        setPaymentData({ ...paymentData, [name]: value });
        //if trainer is charging everything upfront - set monthly payment to 0
        if (name == 'periods' && value == 0) {
            setPaymentData({ ...paymentData, [name]: value, ['monthlyAmount']: 0 });
        }
    }

    const handelSubmit = (e) => {
        e.preventDefault()
        let bodyData = {
            client: client.id,
            currency: paymentData.currency,
            signUpFee: paymentData.signUpFee,
            monthlyAmount: paymentData.monthlyAmount,
            periods: paymentData.periods,
            payment: true,
            durationTime: 0
        }
        if (spaceDateSetFlg) {
            if (paymentData.chargeUpfrontImmediately) {
                bodyData.chargeUpfrontImmediately = true;
            }
            bodyData.startPaymentDate = moment(paymentData.startPaymentDate).utc().format('MM/DD/YYYY')
        }
        if (paymentData.terms) {
            bodyData.terms = paymentData.terms
        }
        if (durationTime) {
            bodyData.duration = paymentData.duration;
            bodyData.startDurationTime = moment(paymentData.startDurationTime).utc().format('MM/DD/YYYY');
            bodyData.durationTime = 1
        }
        handleSubmit(bodyData);
    }
    useEffect(() => {
        if (client && client.payments && client.payments[0]) {
            const data = {
                chargeUpfrontImmediately: true,
                currency: client.payments[0].currency,
                signUpFee: client.payments[0].upfront_fee,
                monthlyAmount: client.payments[0].recurring_fee,
                periods: client.payments[0].months,
                duration: client.duration,
                startDurationTime: client.startDate ?
                    (moment(new Date(client.startDate.date)).isAfter(new Date()) ? new Date(client.startDate.date) : moment(new Date()).add(1, 'day')) :
                    moment(new Date()).add(1, 'day'),
                startPaymentDate: client.payments[0].trial_end ?
                    (moment(new Date(parseInt(client.payments[0].trial_end) * 1000)).isAfter(new Date()) ? new Date(parseInt(client.payments[0].trial_end) * 1000) : moment(new Date()).add(1, 'day')) :
                    moment(new Date()).add(1, 'day'),
                terms: client.payments[0].terms
            }
            setPaymentData(data);
            setSpaceDateSetFlg(client.payments[0].trial_end ? true : false);
            setDurationTime(client.startDate ? true : false);
        }
        else {
            setPaymentData(
                {
                    chargeUpfrontImmediately: true,
                    currency: 'usd',
                    signUpFee: '',
                    monthlyAmount: '',
                    periods: null,
                    duration: null,
                    startDurationTime: new Date,
                    startPaymentDate: new Date,
                    terms: null
                }
            )
            setSpaceDateSetFlg(false);
            setDurationTime(false);
        }
    }, [client])
    const modalClose = useCallback((e) => {
        if (e.keyCode === 27) {
            onClose()
        }
    }, [])
    useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])
    return (
        <Modal className="inmodal in sm2" open={show} style={{ zIndex: 2002, overflow: 'auto' }} onClose={onClose}>
            <div className="modal-dialog" style={{ outline: 'none' }}>
                <div className="modal-content modal-content-light-grey client-subscription-modal">
                    <div className="modal-header">
                        <button type="button" className="close" data-dismiss="modal" onClick={onClose}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        <div className="text-left">
                            <div className="notify"></div>
                            <div className="modal-headline">
                                <h4 className="modal-title">Subscription for {client.name}</h4>
                            </div>
                        </div>
                    </div>
                    <div className="custom-modal-content">
                        <form onSubmit={handelSubmit}>
                            <div className="modal-body">
                                {errorMessage !== '' && (
                                    <div className="alert alert-danger">
                                        {errorMessage}
                                    </div>
                                )}
                                <SubscriptionStatus clientPayment={client.payments} />
                                <Currency
                                    paymentCurrency={paymentData.currency}
                                    onChangeData={handleChange}
                                />
                                <AmountInput
                                    upfrontFee={paymentData.signUpFee ? paymentData.signUpFee : ""}
                                    monthlyAmount={paymentData.monthlyAmount}
                                    months={paymentData.periods}
                                    onChangeData={handleChange}
                                />
                                <Month
                                    months={paymentData.periods}
                                    specificDate={paymentData.chargeUpfrontImmediately}
                                    startPaymentDate={paymentData.startPaymentDate}
                                    spaceDateSetFlg={spaceDateSetFlg}
                                    setSpaceDateSetFlg={setSpaceDateSetFlg}
                                    onChangeData={handleChange}
                                />
                                <hr />
                                <input type="hidden" name="client" value="" />
                                <input type="hidden" name="payment" value="payment" />
                                <Terms
                                    clientId={client.id}
                                    locale={locale}
                                    terms={paymentData.terms}
                                    onChangeData={handleChange}
                                />
                            </div>
                            <div className="modal-footer">
                                <div className="btn-box">
                                    <button type="submit" className="btn btn-success btn-upper is-won">Confirm subscription</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Modal>
    );
}
