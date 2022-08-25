import React from 'react';
import { STRIPE_CONNECT_URL } from "../const";

const StripeConnectAlert = () => {
    return (
        <div className="client-version-alert" onClick={() => window.open(STRIPE_CONNECT_URL, '_blank')}>
            <div className="alert alert-info custome" role="alert">
                Want to collect your clients' payments automatically each month? <strong>Click here to setup your Stripe account.</strong>
            </div>
        </div>
    )
}

export default StripeConnectAlert;
