import React from 'react';

import Questions from './client-field-sections/question';
import Duration from './client-field-sections/Duration';
import CheckIn from './client-field-sections/CheckIn';
import Payment from './client-field-sections/Payment';

const ClientField = (props) => {
    const {
        clientFields,
        durationData,
        trackProgressDay,
        paymentData,
        spaceDateSetFlg,
        locale,
        leadId,
        addTagsOpen,
        stripeConnect,
        clientId,
        handleChange,
        handleHelpView,
        handleDurationChange,
        handleTrackProgressDay,
        handlePayment,
        setSpaceDateSetFlg,
        setAddTags,
        paymentRequired,
        settings
    } = props;

    const clientDataChange = (value, name) => {
        handleChange(value, name);
    }
    return (
        <div>
            <div className="modal-body">
                <div className="what-next">
                    <h4 className="what-next-title">What To Do Next</h4>
                    <Questions
                        value={clientFields.questionnaire}
                        handleHelpView={handleHelpView}
                        handleCheckBox={clientDataChange}
                    />
                    <Duration
                        value={clientFields.durationTime}
                        handleHelpView={handleHelpView}
                        durationData={durationData}
                        handleCheckBox={clientDataChange}
                        handleDurationChange={handleDurationChange}
                    />
                    <CheckIn
                        value={clientFields.trackProgress}
                        handleHelpView={handleHelpView}
                        trackProgressDay={trackProgressDay}
                        handleCheckBox={clientDataChange}
                        handleTrackProgressDay={handleTrackProgressDay}
                        defaultCheckInDay={settings.defaultCheckInDay}
                    />
                    {stripeConnect && (
                        <Payment
                            clientId={clientId}
                            value={clientFields.payment}
                            handleHelpView={handleHelpView}
                            paymentData={paymentData}
                            locale={locale}
                            handleCheckBox={clientDataChange}
                            handlePayment={handlePayment}
                            spaceDateSetFlg={spaceDateSetFlg}
                            setSpaceDateSetFlg={setSpaceDateSetFlg}
                            paymentRequired={paymentRequired}
                            settings={settings}
                        />
                    )}
                </div>
            </div>
            <div className="modal-footer">
                <button className="btn btn-success btn-upper btn-block">next</button>
            </div>
        </div>
    )
}

export default ClientField;
