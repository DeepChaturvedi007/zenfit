import React, {Fragment, useEffect, useState} from 'react';
import { connect } from 'react-redux';
import * as signup from '../../store/signup/action';
import {FieldValidators} from "../../../../shared/helper/validators";
import {WORKOUT_PREFERENCES_FIELDS} from "../../const";
import ZFInputField from "../../../../shared/UI/InputField";
import ZFButton from "../../../../shared/UI/Button";
import {useTranslation} from "react-i18next";
import ZFSelect from "../../../../shared/UI/Select";

const Workout = ({step, saveFieldsAction, workoutState}) => {

    const {t} = useTranslation('globalMessages');
    const [validator, setValidator] = useState({})
    const [workout,setWorkout] = useState({
        injuries: '',
        experienceLevel: 0,
        experience: '',
        exercisePreferences: '',
        workoutLocation: 0,
        workoutsPerWeek: 0
    })

    useEffect(() => {
        Object.keys(workoutState).length > 0 && setWorkout(Object.assign({...workout}, workoutState));
    },[workoutState])

    const handleChange = (name,value) => {
        setValidator({...validator, [name]: ''})
        setWorkout({ ...workout, [name]: value })
    }

    const handleSave = () => {
        try {
            FieldValidators(workout, t, ['injuries','experience'])
            saveFieldsAction(workout, parseInt(step) + 1, 'workout')

        } catch (e){
            setValidator(e)
            console.log("Field issues",e)
        }
    }

    return (
        <div className={"zf-Workout"}>
            {
                Object.values(WORKOUT_PREFERENCES_FIELDS(t)).map((field,i) =>{
                    return(
                        <Fragment key={i}>
                            {
                                field.type === 'input'
                                    ? (
                                        <ZFInputField
                                            type={field.type}
                                            label={field.label}
                                            name={field.name}
                                            multiline
                                            rows={4}
                                            onChange={(e) => handleChange(e.target.name, e.target.value)}
                                            value={workout[field.name]}
                                            helperText={validator[field.name]}
                                        />
                                    ):(
                                        <ZFSelect
                                            value={workout[field.name]}
                                            options={field.options}
                                            onChange={(val) => handleChange(field.name,val)}
                                            label={field.label}
                                            initialText={field.label}
                                            error={validator[field.name]}
                                        />
                                    )
                            }
                        </Fragment>
                    )
                })
            }

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
        workoutState: state.signup.workout
    }
}

export default connect(mapStateToProps, { ...signup })(Workout);


