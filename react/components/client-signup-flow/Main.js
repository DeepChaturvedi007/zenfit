import React, {useEffect} from 'react';
import {connect} from 'react-redux';
import * as signup from './store/signup/action';
import SignUpWrapper from "./components/SignUpWrapper";
import Welcome from "./components/steps/Welcome";
import {useTranslation} from "react-i18next";
import CreateAccount from "./components/steps/CreateAccount";
import General from "./components/steps/General";
import Progress from "./components/steps/Pictures";
import GoalWeight from "./components/steps/GoalWeight";
import Workout from "./components/steps/Workout";
import Diet from "./components/steps/Diet";
import Other from "./components/steps/Other";
import Done from "./components/steps/Done";

const FULL_STEPS_TEMPLATE = (t, weight, measureType) => {
    const weightString = `${weight} ${measureType == '2' ? 'lbs' : 'kg'}`

    return {
        1: {
            title: t('client.activation.createAccountTitle'),
            subTitle: t('client.activation.createAccountSubTitle'),
            component: <CreateAccount/>
        },
        2: {
            title: t('client.activation.generalTitle'),
            subTitle: t('client.activation.generalSubTitle'),
            component: <General/>
        },
        3: {
            title: t('client.survey.photos'),
            subTitle: t('client.survey.photosDescription'),
            component: <Progress/>
        },
        4: {
            title: t('client.survey.goalInfoTitle'),
            subTitle: weightString ? t("client.survey.yourCurrentWeight", {weightString}) : 'No weight provided',
            component: <GoalWeight/>
        },
        5: {
            title: t('client.survey.workoutPreferences'),
            subTitle: t('client.survey.workoutSubtitlePreferences'),
            component: <Workout/>
        },
        6: {
            title: t('client.survey.dietStyleTitle'),
            subTitle: t('client.survey.dietStyleSubTitle'),
            component: <Diet/>
        },
        7: {
            title: t('client.survey.otherFeedbackTitle'),
            subTitle: t('client.survey.otherFeedbackSubTitle'),
            component: <Other/>
        },
        8: {
            title: t('client.downloadApp.title') + ' 🎉',
            subTitle: t('client.downloadApp.subTitle'),
            bigSubTitle: true,
            component: <Done/>
        },
    }
}
const ACTIVATION_STEPS_TEMPLATE = (t, configState) => {
    return {
        1: {
            title: configState === 'activation'
                ? t('client.activation.updateAccountTitle')
                : t('client.activation.createAccountTitle'),
            subTitle: configState === 'activation'
                ? t('client.activation.updateAccountSubTitle')
                : t('client.activation.createAccountSubTitle'),
            component: <CreateAccount/>
        },
        2: {
            title:  configState === 'activation'
                ? t('client.downloadApp.updated')
                : t('client.downloadApp.title') + ' 🎉',
            subTitle: configState === 'activation'
                ? t('client.downloadApp.updatedSubtitle')
                : t('client.downloadApp.subTitle'),
            bigSubTitle: true,
            component: <Done/>
        },
    }
}
const SURVEY_STEPS_TEMPLATE = (t, weight, measureType) => {
    const weightString = `${weight} ${measureType == '2' ? 'lbs' : 'kg'}`

    return {
        1: {
            title: t('client.activation.generalTitle'),
            subTitle: t('client.activation.generalSubTitle'),
            component: <General/>
        },
        2: {
            title: t('client.survey.photos'),
            subTitle: t('client.survey.photosDescription'),
            component: <Progress/>
        },
        3: {
            title: t('client.survey.goalInfoTitle'),
            subTitle: weightString ? t("client.survey.yourCurrentWeight", {weightString}) : 'No weight provided',
            component: <GoalWeight/>
        },
        4: {
            title: t('client.survey.workoutPreferences'),
            subTitle: t('client.survey.workoutSubtitlePreferences'),
            component: <Workout/>
        },
        5: {
            title: t('client.survey.dietStyleTitle'),
            subTitle: t('client.survey.dietStyleSubTitle'),
            component: <Diet/>
        },
        6: {
            title: t('client.survey.otherFeedbackTitle'),
            subTitle: t('client.survey.otherFeedbackSubTitle'),
            component: <Other/>
        },
        7: {
            title: t('client.downloadApp.title') + ' 🎉',
            subTitle: t('client.downloadApp.subTitle'),
            bigSubTitle: true,
            component: <Done/>
        },
    }
}

const Main = ({step, weight, measuringSystem, mapClientToFieldsAction, configState, companyLogoState, submitClientAction}) => {
    const {t} = useTranslation('globalMessages');

    useEffect(() => {
        if (configState !== 'activation') {
            window.addEventListener('beforeunload', saveData)
            window.addEventListener('beforeunload', onClose)
        }

        return () => {
            window.removeEventListener('beforeunload', saveData)
            window.removeEventListener('beforeunload', onClose)
        }
    },[])

    const onClose = (ev) => {
        ev.preventDefault();
        return ev.returnValue = 'post';
    }

    const saveData = () => {
        try {
            submitClientAction(null, 'on_leave_save')
        } catch (e) {
            console.log(e)
        }
    }

    /*Make sure client wants to close*/

    useEffect(() => mapClientToFieldsAction(), []);

    const STEPS_TEMPLATE = () => {

        switch (configState) {
            case 'activation':
                return ACTIVATION_STEPS_TEMPLATE(t, configState);

            case 'survey':
                return SURVEY_STEPS_TEMPLATE(t, weight, measuringSystem)

            default:
                return FULL_STEPS_TEMPLATE(t, weight, measuringSystem)
        }
    }

    return (
        <SignUpWrapper logo={companyLogoState} stepAttr={STEPS_TEMPLATE()[step]} stepsCount={Object.keys(STEPS_TEMPLATE())}>
            {
                /*!isClientCreatedState
                    ? <CreateAccount/> :
                    (
                        step === 0
                            ? <Welcome/>
                            : STEPS_TEMPLATE()[step] && STEPS_TEMPLATE()[step].component
                    )*/
                STEPS_TEMPLATE()[step] && STEPS_TEMPLATE()[step].component
            }
        </SignUpWrapper>
    )
}

function mapStateToProps(state) {
    return {
        step: state.signup.step,
        weight: state.signup.general.startWeight,
        measuringSystem: state.signup.general.measuringSystem,
        configState: state.signup.config,
        companyLogoState: state.signup.companyLogo
    }
}

export default connect(mapStateToProps, {...signup})(Main);
