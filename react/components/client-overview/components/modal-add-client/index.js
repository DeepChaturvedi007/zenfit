/*jshint esversion: 6 */
import React from 'react';
import Modal from '@material-ui/core/Modal';

import moment from 'moment';
import ClientForm from './clientFrom';
import ClientField from './clientField';
import FieldHelp from './fieldHelp';
import './styles.scss'
export default function ModalAddClient(props) {
    const {
        show,
        locale,
        fromLead,
        leadId,
        stripeConnect,
        handleModal,
        handleSubmit,
        clientUpdate,
        clientIdFromLead,
        onClose,
        paymentRequired,
        settings
    } = props;

    const [clientData, setClientData] = React.useState({
        clientName: '',
        clientEmail: '',
        tags: []
    });
    const [loading, setLoading] = React.useState(false);
    const [error, setError] = React.useState('');
    const [clientId, setClientId] = React.useState('');
    const [queue, setQueue] = React.useState('');
    const [clientAddStep, setClientAddStep] = React.useState(fromLead ? 1 : 0);
    const [clientFields, setClientFields] = React.useState({
        questionnaire: true,
        durationTime: true,
        trackProgress: false,
        payment: paymentRequired
    })
    const [fieldHelpData, setFieldHelpData] = React.useState({
        title: '',
        content: ['', '']
    });
    const [durationData, setDurationData] = React.useState({
        startDate: new Date(),
        duration: 0
    });
    const [trackProgressDay, setTrackProgressDay] = React.useState(1);
    const [paymentData, setPaymentData] = React.useState({
        chargeUpfrontImmediately: true,
        currency: 'usd',
        signUpFee: '',
        monthlyAmount: '',
        periods: null,
        duration: null,
        startDurationTime: new Date,
        startPaymentDate: new Date,
        terms: null
    });
    const [spaceDateSetFlg, setSpaceDateSetFlg] = React.useState(false);
    const [addTags, setAddTags] = React.useState(true);

    const modalClose = React.useCallback((e) => {
        if (e.keyCode === 27) {
            handleModal(false)
        }
    }, [])
    const handleModalClose = () => {
        if (clientAddStep === 0) {
            handleModal(false)
        }
        else {
            if (clientAddStep !== 4) {
                setClientAddStep(4)
            }
            else {
                onClose();
            }
        }
    }
    const clientFormChange = (value, name) => {
        setClientData({ ...clientData, [name]: value });
        setError('');
    }
    const clientFieldsChange = (value, name) => {
        setClientFields({ ...clientFields, [name]: value })
    }
    const durationChange = (value, name) => {
        setDurationData({ ...durationData, [name]: value })
    }
    const paymentChange = (value, name) => {
        setPaymentData({ ...paymentData, [name]: value })
    }
    const clientFormSubmit = (e) => {
        e.preventDefault();
        if (clientAddStep === 0) {
            handleSubmit(clientData).then(res => {
                if (res.status === 'success') {
                    setClientId(res.resData.client);
                    setQueue(res.resData.queue);
                    setClientAddStep(1);
                    setError('');
                }
                else {
                    setError(res.resData);
                }
            })
        }
        else {
            e.preventDefault()
            let bodyData = {
                client: clientId,
                lead: leadId ? leadId : '',
                queue: queue,
                modal: 'addClient',
                startDurationTime: moment(durationData.startDate).utc().format('MM/DD/YYYY'),
                duration: durationData.duration,
                dayTrackProgress: trackProgressDay,
                currency: paymentData.currency,
                signUpFee: paymentData.signUpFee,
                monthlyAmount: paymentData.monthlyAmount,
                periods: paymentData.periods,
                payment: '',
                durationTime: 0
            };
            if (clientFields.questionnaire) {
                bodyData.questionnaire = 'questionnaire';
            }
            if (clientFields.durationTime) {
                bodyData.durationTime = 'on';
            }
            if (clientFields.trackProgress) {
                bodyData.trackProgress = "trackProgress";
            }
            if (clientFields.payment) {
                bodyData.payment = true;
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
            clientUpdate(bodyData).then(res => {
                if (res.status === 'success') {
                    onClose();
                    if (clientFields.payment) {
                        res.resData.payments = [res.resData.payment];
                        window.openSideContent(true, res.resData, 1, 'payment-email', true)
                    } else {
                        window.openSideContent(true, res.resData, 4, 'welcome-email', true)
                    }
                }
                else {
                    setError(res.resData);
                }
            });
        }
    }
    const fieldHelpView = (title, content) => {
        setFieldHelpData({
            title: title,
            content: content
        })
        setClientAddStep(2);
    }
    React.useEffect(() => {
        window.addEventListener('keyup', modalClose, false);
        return function cleanup() {
            window.removeEventListener('keyup', modalClose, false);
        }
    }, [])
    React.useEffect(() => {
        setClientData({
            clientName: '',
            clientEmail: '',
            tags: []
        });
        setError('');
        setClientId(clientIdFromLead ? clientIdFromLead : '');
        setQueue('');
        setClientAddStep(fromLead ? 1 : 0);
        setClientFields({
            questionnaire: true,
            durationTime: true,
            trackProgress: settings.defaultCheckInDay ? true : false,
            payment: settings.defaultRecurring ? true : paymentRequired
        });
        setFieldHelpData({
            title: '',
            content: ['', '']
        });
        setDurationData({
            startDate: new Date(),
            duration: 0
        })
        setTrackProgressDay(1);
        setPaymentData({
            chargeUpfrontImmediately: true,
            currency: settings.defaultCurrency ? settings.defaultCurrency : 'usd',
            signUpFee: settings.defaultUpfront ? settings.defaultUpfront : '',
            monthlyAmount: settings.defaultRecurring ? settings.defaultRecurring : '',
            periods: settings.defaultMonths ? settings.defaultMonths : null,
            duration: null,
            startDurationTime: new Date,
            startPaymentDate: new Date,
            terms: null
        })
        setSpaceDateSetFlg(false)
    }, [show])
    return (
        <Modal open={show} style={{ zIndex: 2002, overflow: 'auto' }} className="inmodal in sm2">
            <div className="modal-dialog" style={{ outline: 'none' }}>
                <div className="modal-content modal-content-light-grey">
                    <div className="modal-header">
                        {clientAddStep === 2 && (
                            <div className="modal-back-action">
                                <button className="btn btn-upper btn-close" type="button" onClick={() => { setClientAddStep(1) }}>
                                    <i className="material-icons">&#xE314;</i> Back
                                </button>
                            </div>
                        )}
                        <button type="button" className="close" onClick={() => handleModalClose()}>
                            <span aria-hidden="true">×</span>
                            <span className="sr-only">Close</span>
                        </button>
                        {clientAddStep === 0 && (
                            <React.Fragment>
                                <h4 className="modal-title">Add New Client</h4>
                                <p>When you add a new client, we take care of inviting them to the Zenfit Mobile App, help them fill out your questionnaire etc.</p>
                            </React.Fragment>
                        )}
                        {clientAddStep === 1 && (
                            <React.Fragment>
                                {error !== '' && (
                                    <div className="notify alert alert-danger">
                                        {error}
                                    </div>
                                )}
                                <div className="success-add-header">
                                    <div className="success-add-icon"></div>
                                    <h4 className="modal-title">Setup client</h4>
                                </div>
                            </React.Fragment>
                        )}
                        {clientAddStep === 2 && (
                            <FieldHelp
                                title={fieldHelpData.title}
                                content={fieldHelpData.content}
                            />
                        )}
                        {clientAddStep === 4 && (
                            <div className="text-left">
                                <h4 className="modal-title">Close Client</h4>
                                <p>Sure you want to close client? The data will not be saved!</p>
                            </div>
                        )}
                    </div>
                    <form className="taskForm max-width" rel="normal" onSubmit={clientFormSubmit}>
                        {clientAddStep === 0 && (
                            <ClientForm
                                handleChange={clientFormChange}
                                handleTagChange={clientFormChange}
                                clientData={clientData}
                                error={error}
                            />
                        )}
                        {clientAddStep === 1 && (
                            <ClientField
                                clientFields={clientFields}
                                durationData={durationData}
                                trackProgressDay={trackProgressDay}
                                paymentData={paymentData}
                                spaceDateSetFlg={spaceDateSetFlg}
                                clientId={clientId}
                                locale={locale}
                                leadId={leadId}
                                addTagsOpen={addTags}
                                stripeConnect={stripeConnect}
                                handleChange={clientFieldsChange}
                                handleHelpView={fieldHelpView}
                                handleDurationChange={durationChange}
                                handleTrackProgressDay={setTrackProgressDay}
                                handlePayment={paymentChange}
                                setSpaceDateSetFlg={setSpaceDateSetFlg}
                                setAddTags={setAddTags}
                                paymentRequired={paymentRequired}
                                settings={settings}
                            />
                        )}
                    </form>
                    {clientAddStep === 4 && (
                        <div className="modal-footer p-n-t">
                            <div className="text-right">
                                <button className="btn btn-default btn-upper" onClick={() => { setClientAddStep(1) }}>Cancel</button>
                                <button className="btn btn-danger btn-upper" onClick={onClose}>Close client</button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </Modal>
    );
}
