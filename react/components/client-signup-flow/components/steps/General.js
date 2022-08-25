import React, {useEffect, useState} from 'react';
import {connect} from 'react-redux';
import * as signup from '../../store/signup/action';
import ZFToggle from "../../../../shared/UI/Toggle";
import {useTranslation} from "react-i18next";
import {ACTIVITY_LEVEL, GENDERS, MEASURE_SYSTEM} from "../../const";
import {FieldValidators} from "../../../../shared/helper/validators";
import ZFInputField from "../../../../shared/UI/InputField";
import ZFButton from "../../../../shared/UI/Button";
import ZFSelect from "../../../../shared/UI/Select";
import {S3_BEFORE_AFTER_IMAGES} from "../../../../shared/helper/const";
import {
    GetCMFromFeetInches,
    GetFeetInchesFromCM, isKgOrPound,
    roundToNone,
    roundToTwo
} from "../../../../shared/helper/measurementHelper";

const General = ({step, saveFieldsAction, generalState}) => {
    const {t} = useTranslation('globalMessages');
    const [validator, setValidator] = useState({})
    const [general, setGeneral] = useState({
        gender: 0,
        age: '',
        photo: '',
        height: '',
        feet: '',
        inches: '',
        startWeight: '',
        activityLevel: 0,
        measuringSystem: 1
    });

    useEffect(() => {
        if(Object.keys(generalState).length > 0){
            setGeneral(Object.assign({...general}, generalState))
        }
    }, [generalState])

    const handleChange = (name, value) => {
        validator && setValidator({...validator, [name]: ""})
        setGeneral({...general, [name]: value})
    }
    /*Needs to be a function because of the lifeCycle of reacty*/
    const handleMeasureSystem = (name, val) => {
        if (val == 2) {
            setGeneral({
                ...general,
                measuringSystem: val,
                startWeight: roundToTwo(general.startWeight * 2.205),
                feet: GetFeetInchesFromCM(general.height).feet,
                inches: GetFeetInchesFromCM(general.height).inches,
                height: GetCMFromFeetInches(general.feet, general.inches),
            });
        } else {
            setGeneral({
                ...general,
                measuringSystem: val,
                startWeight: roundToTwo(general.startWeight / 2.205),
                height: GetCMFromFeetInches(general.feet, general.inches),
                feet: GetFeetInchesFromCM(general.height).feet,
                inches: GetFeetInchesFromCM(general.height).inches,
            });
        }
    }

    const handleSave = () => {
        try {
            general['startWeight'] = parseFloat(roundToTwo(general.startWeight));
            /*general['height'] = parseFloat(roundToTwo(general.height));*/
            if(general.measuringSystem == 2){
                general['height'] = GetCMFromFeetInches(general['feet'], general['inches'])
            }else{
                general['feet'] = GetFeetInchesFromCM(general.height).feet
                general['inches'] = GetFeetInchesFromCM(general.height).inches

            }

            FieldValidators(general, t, ['photo','inches'])
            saveFieldsAction(general, parseInt(step) + 1, 'general')
        } catch (e) {
            setValidator(e)
            console.log("Field issues", e)
        }
    }

    return (
        <div className={"zf-General"}>
            <ZFInputField
                type="file"
                label="Profile picture"
                onChange={(val) => handleChange('photo', val)}
                value={general.photo}
            />
            <ZFToggle
                options={GENDERS(t)}
                type={'radio'}
                value={general.gender}
                onClick={(val) => handleChange('gender', val)}
                error={validator["gender"]}
            />
            <ZFInputField
                label={t('client.survey.age')}
                name={"age"}
                type={"number"}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                value={general.age || ''}
                helperText={validator["age"]}
            />
            <ZFToggle
                options={MEASURE_SYSTEM(t)}
                type={'radio'}
                value={general.measuringSystem}
                onClick={(val) => handleMeasureSystem('measuringSystem', val)}
                error={validator["measuringSystem"]}
            />

            {
                general.measuringSystem == 2 ?
                    (
                        <div className="imperial">
                            <ZFInputField
                                label={t('client.survey.height')}
                                name={"feet"}
                                type={"number"}
                                unitLabel={"feet"}
                                min={0}
                                measureSystem={general.measuringSystem}
                                onChange={(e) => handleChange(e.target.name, e.target.value)}
                                value={general.feet || ''}
                                helperText={validator["feet"]}
                            />
                            <ZFInputField
                                label={t('client.survey.height')}
                                name={"inches"}
                                unitLabel={"in"}
                                min={0}
                                type={"number"}
                                measureSystem={general.measuringSystem}
                                onChange={(e) => handleChange(e.target.name, e.target.value)}
                                value={general.inches || ''}
                                helperText={validator["inches"]}
                            />
                        </div>
                    ) : <ZFInputField
                        label={t('client.survey.height')}
                        name={"height"}
                        unitLabel={"cm"}
                        type={"number"}
                        min={0}
                        measureSystem={general.measuringSystem}
                        onChange={(e) => handleChange(e.target.name, e.target.value)}
                        value={general.height || ''}
                        helperText={validator["height"]}
                    />
            }

            <ZFInputField
                label={t('client.survey.startWeight')}
                name={"startWeight"}
                value={general.startWeight || ''}
                min={general.measuringSystem == 2 ? 66 : 30}
                unitLabel={isKgOrPound(general.measuringSystem)}
                type={"number"}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                measureSystem={general.measuringSystem}
                helperText={validator["startWeight"]}
            />

            <ZFSelect
                value={general.activityLevel}
                options={ACTIVITY_LEVEL(t)}
                onChange={(val) => handleChange('activityLevel', val)}
                label={t('client.survey.activityLevel')}
                initialText={t('client.survey.selectActivityLevel')}
                error={validator["activityLevel"]}
            />

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
        step: state.signup.step,
        generalState: state.signup.general,
    }
}

export default connect(mapStateToProps, {...signup})(General);


