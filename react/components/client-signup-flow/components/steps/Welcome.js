import React from 'react';
import { connect } from 'react-redux';
import * as signup from '../../store/signup/action';
import Title from "../Title";
import ArrowBack from "@material-ui/icons/ArrowBack";
import {useTranslation} from "react-i18next";

const Welcome = ({ChangeStepAction, initialClient, companyLogo}) => {
    const {t} = useTranslation('globalMessages');

    return (
        <div className={"zf-GetStarted"}>
            <div className={"bgImg"} style={{ backgroundImage: `url(${companyLogo})` }}/>
            <Title title={t('client.activation.greetingNew',{name:(initialClient.name).split(" ")[0]})} subTitle={t('client.activation.welcomeMsg')}/>
            <span className={"oval"} onClick={() => ChangeStepAction(1)}>
                <ArrowBack/>
            </span>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        initialClient: state.signup.client,
        companyLogo: state.signup.companyLogo
    }
}

export default connect(mapStateToProps, { ...signup })(Welcome);


