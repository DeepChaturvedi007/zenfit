import React, {Fragment, useEffect, useState} from 'react';
import { connect } from 'react-redux';
import * as signup from '../../store/signup/action';
import ZFInputField from "../../../../shared/UI/InputField";
import {useTranslation} from "react-i18next";
import ZFButton from "../../../../shared/UI/Button";
import {FieldValidators} from "../../../../shared/helper/validators";
import {CREATE_ACCOUNT_FIELDS} from "../../const";
import ZFError from "../../../../shared/UI/Error";
import ZFCheckbox from "../../../../shared/UI/Checkbox";
import {CTA_COLORS_BG} from "../../../../shared/UI/Theme/_color";
import Title from "../Title";

const CreateAccount = ({step, initialClient, configState, localeState, saveFieldsAction, submitClientAction, accountState, submitError, primaryColor, clientSubmittingState}) => {
    const {t} = useTranslation('globalMessages');
    const [validator, setValidator] = useState({})
    const [error, setError ] = useState(false)
    const [account,setAccount] = useState({
        name: '',
        email: '',
        phone: '',
        password: '',
        passwordConfirm: '',
        termsAccepted: false,
    })

    useEffect(() => {
        Object.keys(accountState).length > 0 &&  setAccount(Object.assign({...account},accountState))
    },[accountState])

    const termsLink = 'https://zenfitapp.com/terms-conditions/'
    const terms = t('client.checkout.termsOfService');

    const TermsAccepted = () => {
        return(
            <span className={'termsTxt'}>
                {t('client.checkout.acceptTerms')}
                <a href={termsLink} target={"_blank"} style={{color: primaryColor || CTA_COLORS_BG}}>
                {terms}
                </a>
            </span>
        )
    }

    const handleChange = (name,value) => {
        validator && setValidator({...validator, [name] : ""})
        setAccount({ ...account, [name]: value })
    }

    const handleSubmit = () => {
        try {
            FieldValidators(account, t, ['password'])
            submitClientAction(account, 'account')
        } catch (e){
            setValidator(e)
            console.log("Field issues",e)
        }
    }
    return (
        <div className={"zf-CreateAccount"}>
            {
                Object.values(CREATE_ACCOUNT_FIELDS(t)).map((field,i) =>{
                    return(
                        <Fragment key={i}>
                            <ZFInputField
                                type={field.type}
                                label={field.label}
                                name={field.name}
                                initialValue={field.type === 'phone' ? _.lowerCase(localeState.split('_')[1]) : ''}
                                value={account[field.name]}
                                onChange={(e) => field.type === "phone"
                                    ? handleChange(field.name, '+'+e)
                                    : handleChange(e.target.name, e.target.value)
                                }
                                helperText={validator[field.name]}
                            />
                        </Fragment>
                    )
                })
            }

            <ZFButton
                size={"bigBoi"}
                disabled={!account.termsAccepted || clientSubmittingState}
                onClick={() => handleSubmit()}
            >
                {
                    configState === 'activation'
                        ? t('client.activation.updateLogin')
                        : t('client.activation.createLoginBtn')
                }
            </ZFButton>
            <div className="terms">
                <ZFCheckbox
                    component={TermsAccepted}
                    disabled={account.termsAccepted}
                    name={'termsAccepted'}
                    checked={account.termsAccepted}
                    onChange={(e) => {
                        handleChange(e.target.name, true);
                        error && setError(false)
                    }}
                />
                {
                    error &&
                    <span className="error">
                        {t('client.checkout.acceptTermsError')}
                    </span>
                }
            </div>
            {submitError && <ZFError msg={submitError.message} status={submitError.status}/>}

        </div>
    )
}

function mapStateToProps(state) {
    return {
        step: state.signup.step,
        localeState: state.signup.locale,
        initialClient: state.signup.client,
        accountState: state.signup.account,
        configState: state.signup.config,
        primaryColor: state.signup.primaryColor,
        submitError: state.signup.clientSubmitError,
        clientSubmittingState: state.signup.clientSubmitting
    }
}

export default connect(mapStateToProps, { ...signup })(CreateAccount);


