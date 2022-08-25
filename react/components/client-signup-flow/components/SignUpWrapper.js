import React from 'react';
import { connect } from 'react-redux';
import * as signup from '../store/signup/action';
import ZFLangSwitch from "../../../shared/UI/LangSwitch";
import ZFPoweredBy from "../../../shared/UI/PoweredBy";
import ZFProgressIndicator from "../../../shared/UI/ProgressIndicator";
import Title from "./Title";
import {GLOBAL_FONT_FAMILY} from "../../../shared/UI/Theme/_global";

const SignUpWrapper = ({stepAttr,logo, stepsCount, locale, ChangeLangAction, ChangeStepAction, children, currentStep}) => {

    return (
        <div className={"signUpWrapper"} style={{fontFamily:GLOBAL_FONT_FAMILY}}>
            {
                currentStep !== 0 && (
                    <ZFProgressIndicator
                        currentStep={currentStep}
                        changeStep={ChangeStepAction}
                        bars={stepsCount}
                    />
                )
            }
            <div className="zf-content">
                {
                    logo && (<div className={"bgImg"} style={{ backgroundImage: `url(${logo})` }}/>)
                }
                {stepAttr && <Title bigSubtitle={stepAttr.bigSubTitle} title={stepAttr.title} subTitle={stepAttr.subTitle}/>}
                {children}
            </div>
            <div className="zf-footer">
                <ZFLangSwitch choosenLang={locale} changeLang={ChangeLangAction}/>
                <ZFPoweredBy/>
            </div>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        locale: state.signup.locale,
        currentStep: state.signup.step
    }
}

export default connect(mapStateToProps, { ...signup })(SignUpWrapper);
