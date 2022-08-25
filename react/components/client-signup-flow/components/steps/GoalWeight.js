import React, {useEffect, useState} from 'react';
import {connect} from 'react-redux';
import * as signup from '../../store/signup/action';
import ZFButton from "../../../../shared/UI/Button";
import {useTranslation} from "react-i18next";
import {GOAL_WEIGHT_CONVERT, GOAL_WEIGHT_GAIN, GOAL_WEIGHT_LOSE, GOAL_WEIGHT_MAINTAIN} from "../../const";
import ZFInputField from "../../../../shared/UI/InputField";
import {FieldValidators} from "../../../../shared/helper/validators";
import ZFSelect from "../../../../shared/UI/Select";
import {isKgOrPound} from "../../../../shared/helper/measurementHelper";

const GoalWeight = ({saveFieldsAction, currentWeight, goalState, measuringSystem = '1', step}) => {
    const {t} = useTranslation('globalMessages');
    const [validator, setValidator] = useState({})
    const [goal, setGoal] = useState({
        /*primaryGoal: 0,*/
        goalWeight: ''
    })
/*    const IsGain = parseInt(currentWeight) < parseInt(goal.goalWeight)
    const isEqual = parseFloat(currentWeight) === parseFloat(goal.goalWeight)
    ;*/
    const roundToTwo = (val) => val !== 0 ? parseFloat(val).toFixed(2) : '';

    useEffect(() => {
        if (Object.keys(goalState).length > 0) {
           /* let updatedObj =
                measuringSystem == 2
                    ? {...goalState, goalWeight: roundToTwo(goalState['goalWeight'] * 2.205)}
                    : goalState*/

            setGoal(Object.assign({...goal}, goalState));
        }
    }, [goalState])

    /*const goalTypeOptions = isEqual ? GOAL_WEIGHT_MAINTAIN(t, measuringSystem)
        : IsGain ?
            GOAL_WEIGHT_GAIN(t, measuringSystem)
            : GOAL_WEIGHT_LOSE(t, measuringSystem)*/

    /* Deprecated key
    useEffect(() => {
        if(isEqual){
            setGoal({...goal, goalType: 3})
        }
    },[goal.goalWeight])*/

    /* Deprecated key

    const NumWeeksString = () => {
        try {
            const KgToPound = (weight) => measuringSystem == 2 ? (weight * 2) : weight;

            const FindNumWeeks = IsGain
                ? (goal.goalWeight - currentWeight) / parseFloat(KgToPound(GOAL_WEIGHT_CONVERT[goal.goalType]))
                : (currentWeight - goal.goalWeight) / parseFloat(KgToPound(GOAL_WEIGHT_CONVERT[goal.goalType]))

            let errorString =
                isEqual
                    ? t('client.survey.goalEqualCurrent') :
                IsGain
                    ? t('client.survey.goalHigherCurrent')
                    : t('client.survey.goalLessCurrent')

            let week = (FindNumWeeks > 0 && FindNumWeeks !== Number.POSITIVE_INFINITY && FindNumWeeks !== Number.NEGATIVE_INFINITY)
                ? parseInt(FindNumWeeks)
                : errorString

            return(
                typeof(week) === 'number'
                    ? t('client.survey.goalWeek',{week})
                    :  week
            )
        }catch (e){
            console.log(e,'error')
        }
    }*/

    const handleChange = (name, value) => {
        setValidator({...validator, [name]: ''})
        setGoal({...goal, [name]: value})
    }

    const handleSave = () => {
        try {
            FieldValidators(goal, t)
            saveFieldsAction(goal, parseInt(step) + 1, 'goal')
        } catch (e) {
            setValidator(e)
            console.log("Field issues", e)
        }
    }

    return (
        <div className={"zf-Goal"}>
            <ZFInputField
                label={t('client.survey.goalWeight')}
                name={"goalWeight"}
                type={"number"}
                unitLabel={isKgOrPound(measuringSystem)}
                min={measuringSystem == 2 ? 66 : 30}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                value={goal.goalWeight || ''}
                measureSystem={measuringSystem}
                helperText={validator["goalWeight"]}
            />

            {/* Deprecated key
            <ZFSelect
                value={goal.goalType}
                options={goalTypeOptions}
                disabled={isEqual}
                onChange={(val) => handleChange('primaryGoal', parseInt(val))}
                initialText={t('client.survey.selectPrimaryGoal')}
                error={validator["primaryGoal"]}
            />*/}

            {/*<span className={'weekInfo'}>{NumWeeksString()}</span>*/}

            <ZFButton
                size={"bigBoi"}
                onClick={() => handleSave()}>
                {t('client.activation.next')}
            </ZFButton>
        </div>
    )
}

function mapStateToProps(state) {
    return {
        currentWeight: state.signup.general.startWeight,
        goalState: state.signup.goal,
        measuringSystem: state.signup.general.measuringSystem,
        step: state.signup.step
    }
}

export default connect(mapStateToProps, {...signup})(GoalWeight);


