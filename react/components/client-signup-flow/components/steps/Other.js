import React, {Fragment, useEffect, useState} from 'react';
import {connect} from 'react-redux';
import * as signup from '../../store/signup/action';
import {useTranslation} from "react-i18next";
import {FieldValidators} from "../../../../shared/helper/validators";
import ZFInputField from "../../../../shared/UI/InputField";
import ZFButton from "../../../../shared/UI/Button";
import {CTA_COLORS_BG} from "../../../../shared/UI/Theme/_color";
import ZFCheckbox from "../../../../shared/UI/Checkbox";
import ZFError from "../../../../shared/UI/Error";

const Other = (props) => {
    const {submitClientAction, step, primaryColor, customQuestions, otherState, submitError, clientSubmitting} = props

    const [validator, setValidator] = useState({})
    const {t} = useTranslation('globalMessages');
    const [error, setError] = useState(false)
    const [other, setOther] = useState({
        lifestyle: '',
        motivation: '',
        termsAccepted: true,
        questions: {}
    })

    useEffect(() => {
        Object.keys(otherState).length > 0 && setOther(Object.assign({...other}, otherState))
    }, [otherState])

    const termsLink = 'https://zenfitapp.com/terms-conditions/'
    const terms = t('client.checkout.termsOfService');

    const TermsAccepted = () => {
        return (
            <span className={'termsTxt'}>
                {t('client.checkout.acceptTerms')}
                <a href={termsLink} target={"_blank"} style={{color: primaryColor || CTA_COLORS_BG}}>
                {terms}
                </a>
            </span>
        )
    }

    const handleChange = (name, value) => {
        setValidator({...validator, [name]: ''})
        setOther({...other, [name]: value})
    }

    const handleSave = () => {
        try {
            FieldValidators(other, t)
            submitClientAction(other, 'other')

        } catch (e) {
            setValidator(e)
            console.log("Field issues", e)
        }
    }

    return (
        <div className={"zf-Other"}>
            <ZFInputField
                label={t('client.survey.lifestyleNew')}
                name={'lifestyle'}
                multiline
                rows={4}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                value={other.lifestyle}
                helperText={validator['lifestyle']}
            />
            <ZFInputField
                label={t('client.survey.howMotivated')}
                name={'motivation'}
                multiline
                rows={4}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                value={other.motivation}
                helperText={validator['motivation']}
            />

            {
                customQuestions.length > 0 &&
                customQuestions.map((question, index) => {
                    const questionID = (question.id).toString()
                    return (
                        <Fragment key={index}>
                            <ZFInputField
                                label={question.text}
                                name={questionID}
                                multiline
                                rows={4}
                                onChange={(e) => {
                                    setOther({
                                        ...other,
                                        questions: {
                                            ...other.questions,
                                            [e.target.name]: e.target.value
                                        }
                                    })
                                }}
                                value={other.questions[questionID]}
                                helperText={validator[questionID]}
                            />
                        </Fragment>
                    )
                })
            }
            <ZFButton
                size={"bigBoi"}
                disabled={!other.termsAccepted || clientSubmitting}
                onClick={() => handleSave()}
            >
                {t('client.survey.submit')}
            </ZFButton>
            {/*<div className="terms">
                <ZFCheckbox
                    component={TermsAccepted}
                    name={'termsAccepted'}
                    checked={other.termsAccepted}
                    onChange={(e) => {
                        handleChange(e.target.name, !other.termsAccepted);
                        error && setError(false)
                    }}
                />
                {error && <span className="error"> {t('client.checkout.acceptTermsError')} </span>}
            </div>*/}
            {submitError && <ZFError msg={submitError.message} status={submitError.status}/>}
        </div>
    )
}

function mapStateToProps(state) {
    return {
        step: state.signup.step,
        primaryColor: state.signup.primaryColor,
        customQuestions: state.signup.customQuestions,
        clientSubmitting: state.signup.clientSubmitting,
        otherState: state.signup.other,
        submitError: state.signup.clientSubmitError,
        clientSubmittingState: state.signup.clientSubmitting
    }
}

export default connect(mapStateToProps, {...signup})(Other);


