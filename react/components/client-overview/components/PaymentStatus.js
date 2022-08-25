import React from "react";
import moment from "moment";
import ReactTooltip from "react-tooltip";
import MonetizationOnIcon from '@material-ui/icons/MonetizationOn';
const PaymentStatus = (props) => {
    const {payments} = props;
    let paymentContent = [];
    if (payments) {
        payments.forEach(payment => {
            if (payment.active && !payment.pending) {
                if (payment.last_payment_failed) {
                    paymentContent.push(
                        <span
                            key={payment.id + '_last_payment_failed'}
                            className="dollar-sign-failed hidden-xs"
                            data-tip="Last payment failed."
                            data-for="default-tooltip"
                        >
                            <MonetizationOnIcon />
                        </span>
                    );
                } else {
                    const months = `${payment.months} months`;
                    const duration = (payment.months === 13) ? 'until client unsubscribes' : `for ${months}`;
                    const sentAtDate = moment.utc(payment.sent_at.date);
                    const ends = (payment.months === 13 || payment.months === 0)
                        ? ''
                        : 'Ends ' + sentAtDate.add(payment.months, 'months').format("ll") + '.';
                    const currency = payment.currency;
                    const recurring_fee = parseFloat(payment.recurring_fee).toFixed(2) + "/mo";
                    const upfront_fee = (payment.upfront_fee !== '0' && payment.upfront_fee) ? `, with a ${currency}${payment.upfront_fee} upfront fee` : '';
                    const content = `Active subscription: ${currency}${recurring_fee} ${duration}${upfront_fee}. ${ends}`;

                    paymentContent.push(
                        <span
                            key={payment.id + '_active_sub'}
                            data-tip={content}
                            data-for="default-tooltip"
                            className='dollar-sign-success'
                        >
                            <MonetizationOnIcon />
                        </span>
                    );
                }
            }
            if (payment.pending) {
                const months = `${payment.months} months`;
                const duration = (payment.months === 13) ? 'until client unsubscribes' : `for ${months}`;
                const currency = payment.currency;
                const recurring_fee = parseFloat(payment.recurring_fee).toFixed(2) + "/mo";
                const upfront_fee = (payment.upfront_fee !== '0' && payment.upfront_fee) ? `, with a ${currency}${payment.upfront_fee} upfront fee` : '';
                const content = `Pending subscription: ${currency} ${recurring_fee} ${duration}${upfront_fee}`;

                paymentContent.push(
                    <span
                        key={payment.id + '_pending'}
                        className="dollar-sign-pending"
                        data-tip={content}
                        data-for="default-tooltip"
                    >
                        <MonetizationOnIcon />
                    </span>
                );
            }
            if (payment.paused_until && !payment.canceled && !payment.pending) {
                const content = `Paused subscription until ${moment.utc(payment.paused_until.date).format('ll')}`;
                paymentContent.push(
                    <span
                        key={payment.id + '_paused_until'}
                        className="dollar-sign-paused"
                        data-tip={content}
                        data-for="default-tooltip"
                    >
                        <MonetizationOnIcon />
                    </span>
                )
            }
            if (payment.canceled && !payment.pending) {
                const months = `${payment.months} months`;
                const duration = (payment.months === 13) ? 'until client unsubscribes' : `for ${months}`;
                const sentAtDate = moment.utc(payment.sent_at.date);
                const ends = (payment.months === 13 || payment.months === 0)
                    ? ''
                    : 'Ends ' + sentAtDate.add(payment.months, 'months').format("ll") + '.';
                const currency = payment.currency;
                const recurring_fee = parseFloat(payment.recurring_fee).toFixed(2) + "/mo";
                const upfront_fee = (payment.upfront_fee !== '0' && payment.upfront_fee) ? `, with a ${currency}${payment.upfront_fee} upfront fee` : '';
                const content = `Subscription ended: ${currency}${recurring_fee} ${duration}${upfront_fee}.`;

                paymentContent.push(
                    <span
                        key={payment.id + '_canceled'}
                        className="dollar-sign-canceled"
                        data-tip={content}
                        data-for="default-tooltip"
                    >
                        <MonetizationOnIcon />
                    </span>
                );
            }
        });
    }

    return (
        <span className="client-item-payment">{paymentContent}</span>
    );
};

export default PaymentStatus;
