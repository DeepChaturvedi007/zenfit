import React from 'react'

import PaymentField from './payment-field';
import { payment, week } from '../help-const';

const Payment = (props) => {
    const {
        clientId,
        value,
        paymentData,
        spaceDateSetFlg,
        locale,
        handleHelpView,
        handleCheckBox,
        handlePayment,
        setSpaceDateSetFlg,
        paymentRequired,
        settings
    } = props;

    return (
        <div className="checkbox">
            <label>
                <input
                    type="checkbox"
                    checked={value}
                    disabled={paymentRequired}
                    id="payment"
                    name="payment"
                    onChange={(e) => { handleCheckBox(!value, e.target.name) }}
                />
                Setup payment.
            </label>
            <a className="read-more" onClick={() => { handleHelpView(payment.title, payment.content) }}>What is payment?</a>
            {value && (
                <div className="description-box">
                    <PaymentField
                        paymentData={paymentData}
                        handleChange={handlePayment}
                        spaceDateSetFlg={spaceDateSetFlg}
                        setSpaceDateSetFlg={setSpaceDateSetFlg}
                        locale={locale}
                        clientId={clientId}
                        settings={settings}
                    />
                </div>
            )}
        </div>
    )
}

export default Payment;
