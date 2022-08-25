import React, {useState, useEffect} from 'react';
import {connect} from 'react-redux';
import axios from 'axios';
import * as auths from '../../store/auth/action';
import ArrowRightAltIcon from '@material-ui/icons/ArrowRightAlt';
import ZFButton from "../../../../shared/UI/Button";
import ZFInputField from '../../../../shared/UI/InputField';
import {FieldValidators} from "../../../../shared/helper/validators";
import {useTranslation} from "react-i18next";
import ZFCheckbox from "../../../../shared/UI/Checkbox";
import ZFError from "../../../../shared/UI/Error";
import {ZFSignupStyled} from "./ZFSignupStyled";
import ZFPoweredBy from "../../../../shared/UI/PoweredBy";
import {CTA_COLORS_BG} from "../../../../shared/UI/Theme/_color";
import ZFProgressIndicator from "../../../../shared/UI/ProgressIndicator";
import ZFSelect from "../../../../shared/UI/Select";
import {NUMBER_OF_CLIENTS} from "./const";
import Onboarding from "./components/Onboarding";
import {createGlobalStyle} from "styled-components";
import {CircularProgress} from "@material-ui/core";

const Auth = (props) => {
    const {submitErrorState, createUserAction, localesState, userCreatedState, userSubmittingState} = props;
    const {t} = useTranslation('globalMessages');
    const [active, setActive] = useState(1);
    const [countryCode, setCountryCode] = useState('');
    const [validator, setValidator] = useState({})
    const [data, setData] = useState({
        name: "",
        email: "",
        password: "",
        passwordConfirm: "",
        businessName: "",
        locale: "",
        phone: "",
        mainAccount: "",
        numberClients: 0,
        termsAccepted: false
    });
    /*
    * TODO: HUBSPOT INTEGRATION - MISSING
    * */
    useEffect(() => {
        axios.get('https://geolocation-db.com/json/')
            .then((res) => {
                const countryCode = _.lowerCase(res.data.country_code);
                Object.keys(localesState).map((key) => {
                    if (_.lowerCase(key).includes(countryCode)) {
                        setData({...data, locale: key});
                        setCountryCode(countryCode);
                    }
                });

            })
    }, [])

    useEffect(() => {
        userCreatedState && setActive(active + 1)
    },[userCreatedState, userSubmittingState])

    const handleChange = (name, value) => {
        validator && setValidator({...validator, [name]: ""})
        setData({...data, [name]: value})
    }

    const handleSteps = async () => {
        try {
            switch (active) {
                case 1:
                    FieldValidators({
                        name: data.name,
                        email: data.email,
                        password: data.password,
                        phone: data.phone,
                        passwordConfirm: data.passwordConfirm
                    }, t)
                    setActive(active + 1)
                    break;
                case 2:
                    FieldValidators({
                        /*businessName: data.businessName,*/
                        mainAccount: data.mainAccount,
                        language: data.locale,
                        numberClients: data.numberClients,
                    }, t)
                    await createUserAction(data)
                    break;
            }
        } catch (e) {
            console.log(e, 'Field issues')
            setValidator(e)
        }
    }
    const TermsAccepted = () => {
        const termsLink = 'https://zenfitapp.com/terms-conditions/'
        const privacyLink = 'https://zenfitapp.com/privacy-policy-cookies/'
        return (
            <span className={'termsTxt'} style={{fontWeight:'500'}}>
                {`I have read and accept `}
                <a href={termsLink} target={"_blank"} style={{color: CTA_COLORS_BG}}>
                    the terms of service
                </a>
                {` and `}
                <a href={privacyLink} target={"_blank"} style={{color: CTA_COLORS_BG}}>
                    the privacy policy
                </a>
            </span>
        )
    }

    const content = () => {
        switch (active) {
            case 1:
                return (
                    <div className="second-page">
                        <h1>Create coach account</h1>
                        <h2>
                            Enter info to get your
                            <span style={{fontWeight:'600',paddingLeft:'5px'}}>
                                14 day free trial
                            </span>
                        </h2>
                        <ZFInputField
                            label="Full name"
                            name="name"
                            helperText={validator["name"]}
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                            value={data.name}
                        />
                        <ZFInputField
                            label="E-mail"
                            name="email"
                            value={data.email}
                            helperText={validator["email"]}
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                        />
                        <ZFInputField
                            label="Business Phone"
                            name="phone"
                            type="phone"
                            value={data.phone}
                            onChange={(val) => handleChange('phone', val)}
                            initialValue={countryCode}
                            helperText={validator["phone"]}
                        />
                        <ZFInputField
                            label="Password"
                            name="password"
                            type={'password'}
                            helperText={validator["password"]}
                            value={data.password}
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                        />
                        <ZFInputField
                            label={"Repeat password"}
                            name="passwordConfirm"
                            helperText={validator["passwordConfirm"]}
                            type={'password'}
                            value={data.passwordConfirm}
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                        />
                        <div className="terms" style={{paddingTop:'10px'}}>
                            <ZFCheckbox
                                component={TermsAccepted}
                                name={'termsAccepted'}
                                checked={data.termsAccepted}
                                onChange={() => handleChange('termsAccepted', !data.termsAccepted)}
                            />
                        </div>

                        <ZFButton
                            size={"bigBoi"}
                            onClick={() => handleSteps()}
                            disabled={!data.termsAccepted}
                        >
                            Next
                        </ZFButton>
                    </div>
                )
            case 2:
                return (
                    <div className="second-page">
                        <h1>Business info</h1>
                        <h2>Enter your business info to get started</h2>
                        <ZFInputField
                            label="Name of Business"
                            name="businessName"
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                            value={data.businessName}
                            helperText={validator["businessName"]}
                        />
                        <ZFSelect
                            label="Language"
                            initialText={"please select a language"}
                            name="language"
                            options={localesState}
                            onChange={(val) => handleChange('locale', val)}
                            value={data.locale}
                            error={validator["locale"]}
                        />
                        <ZFInputField
                            label="Enter Social Media Account"
                            name="mainAccount"
                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                            value={data.mainAccount}
                            helperText={validator["mainAccount"]}
                        />
                        <ZFSelect
                            label={"Number of clients"}
                            name={"numberClients"}
                            placeholder={""}
                            options={NUMBER_OF_CLIENTS}
                            onChange={(val) => handleChange('numberClients', val)}
                            value={data.numberClients}
                            error={validator["numberClients"]}
                        />
                        <ZFButton
                            size={"bigBoi"}
                            style={{position:'relative'}}
                            onClick={() => handleSteps()}
                        >
                            Create Account
                            {
                                userSubmittingState && (
                                    <div style={{position:'absolute',top:'12px',right:'25%'}}>
                                        <CircularProgress size={20} style={{color:'white'}} />
                                    </div>
                                )
                            }

                        </ZFButton>

                        {
                            submitErrorState && <ZFError msg={submitErrorState.error} status={"error"}/>
                        }
                    </div>
                )
            case 3:
                return (
                    <Onboarding/>
                )
            default:
                return (
                    <div className="first-page">
                        <h1>We help coaches build<br/> successful coaching businesses.</h1>
                        <h2>Create your free account today</h2>
                        <div className="circle-arrow" onClick={() => setActive(2)}>
                            <ArrowRightAltIcon/>
                        </div>

                    </div>
                )
        }
    }

    const RemoveIntercom = createGlobalStyle`
        body{
          background: #fcfcfc!important;
          .content{
            background: #fcfcfc!important;
          }
          .intercom-lightweight-app{
            display: none;
          }
        }
    `;

    return (
        <ZFSignupStyled>
            <RemoveIntercom/>
            {
                active !== 3 && (
                    <ZFProgressIndicator
                        currentStep={active}
                        changeStep={(el) => setActive(el)}
                        bars={[1,2]}
                    />
                )
            }
            {content(active)}

            {
                active !== 3 && (
                    <div className="zf-footer">
                        <ZFPoweredBy/>
                    </div>
                )
            }

        </ZFSignupStyled>
    )


}

function mapStateToProps(state) {
    return {
        userCreatedState: state.auth.userCreated,
        submitErrorState: state.auth.error,
        userSubmittingState: state.auth.userSubmitting,
        localesState: state.auth.locales
    }
}

export default connect(mapStateToProps, {...auths})(Auth);
