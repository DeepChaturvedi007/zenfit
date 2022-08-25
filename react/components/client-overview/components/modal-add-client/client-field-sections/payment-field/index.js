/*jshint esversion: 6 */
import React from 'react';

import Currency from '../../../../../modals/modal-subscription/Currency';
import AmountInput from '../../../../../modals/modal-subscription/AmountInput';
import Month from '../../../../../modals/modal-subscription/Month';
import Terms from '../../../../../modals/modal-subscription/Terms';
export default function PaymentField(props) {
    const {
        paymentData,
        handleChange,
        spaceDateSetFlg,
        setSpaceDateSetFlg,
        locale,
        clientId,
        settings
    } = props;

    return (
        <div>
            <Currency
                paymentCurrency={paymentData.currency}
                onChangeData={handleChange}
                defaultCurrency={settings.defaultCurrency}
            />
            <AmountInput
                upfrontFee={paymentData.signUpFee}
                monthlyAmount={paymentData.monthlyAmount}
                onChangeData={handleChange}
                defaultRecurring={settings.defaultRecurring}
                defaultUpfront={settings.defaultUpfront}
            />
            <Month
                months={paymentData.periods}
                specificDate={paymentData.chargeUpfrontImmediately}
                startPaymentDate={paymentData.startPaymentDate}
                spaceDateSetFlg={spaceDateSetFlg}
                setSpaceDateSetFlg={setSpaceDateSetFlg}
                onChangeData={handleChange}
                defaultMonths={settings.defaultMonths}
            />
            <hr />
            <input type="hidden" name="client" value="" />
            <input type="hidden" name="payment" value="payment" />
            <Terms
                clientId={clientId}
                locale={locale}
                terms={paymentData.terms}
                onChangeData={handleChange}
            />
        </div>
    );
}
