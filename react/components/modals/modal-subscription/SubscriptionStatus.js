import React from 'react';

const SubscriptionStatus = (props) => {
    const { clientPayment } = props;

    return (
        <React.Fragment>
            {(clientPayment && clientPayment.length !== 0 && clientPayment[0].active) && (
                <div className="current-payment-plan">
                    <div className="image pull-left">
                        <img src="/bundles/app/images/client-money.png" />
                    </div>
                    <div className="pull-left">
                        <span className="until">
                            {clientPayment[0].canceled_at ? (
                                'Subscription ended ' + clientPayment[0].canceled_at
                            ) : (
                                'Current Payment Plan until ' + clientPayment[0].until
                            )}
                        </span>
                        <p>
                            <span className="recurring-fee-js">
                                {clientPayment[0].recurring_fee + ' ' + clientPayment[0].currency}
                            </span>
                            <span className="months-js">
                                {clientPayment[0].months === 13 ? (
                                    'until client unsubscribes'
                                ) : (
                                    ' for ' + clientPayment[0].months + ' months '
                                )}
                            </span>
                             with a
                            <span className="upfront-fee-js">
                                {clientPayment[0].upfront_fee + ' ' + clientPayment[0].currency+' '}
                            </span>
                             upfront fee
                        </p>
                    </div>
                    <div className="clearfix"></div>
                </div>
            )}
            <div className="info">
                {(clientPayment && clientPayment.length !== 0 && clientPayment[0].active) ? (
                    'You have an existing subscription for this client. Change the current monthly subscription below.'
                ) : (
                    'Setup automatic monthly subscription for your client.'
                )}
            </div>
        </React.Fragment>
    )
}

export default SubscriptionStatus
