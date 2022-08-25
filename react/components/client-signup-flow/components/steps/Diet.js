import React, {useEffect, useState} from 'react';
import { connect } from 'react-redux';
import * as signup from '../../store/signup/action';
import ZFButton from "../../../../shared/UI/Button";
import {FieldValidators} from "../../../../shared/helper/validators";
import {useTranslation} from "react-i18next";
import ZFSelect from "../../../../shared/UI/Select";
import {DIET_PREFERENCES, FOOD_PREFERENCES, NUMBER_OF_MEALS_PR_DAY} from "../../const";
import ZFInputField from "../../../../shared/UI/InputField";
import ZFToggle from "../../../../shared/UI/Toggle";

const Diet = ({step, saveFieldsAction, dietState}) => {
    const [validator, setValidator] = useState({})
    const {t} = useTranslation('globalMessages');
    const [diet, setDiet] = useState({
        dietStyle: '',
        numberOfMeals: 0,
        clientFoodPreferences: [],
        dietPreference: 0
    })

    useEffect(() => {
        Object.keys(dietState).length > 0 &&  setDiet(Object.assign({...diet},dietState))
    },[dietState])

    const handleChange = (name,value) => {
        setValidator({...validator, [name]: ''})
        setDiet({ ...diet, [name]: value })
    }

    const handleSave = () => {
        try {
            FieldValidators(diet, t, ['clientFoodPreferences', 'dietPreference'])
            saveFieldsAction(diet, parseInt(step) + 1, 'diet')
        } catch (e){
            setValidator(e)
            console.log("Field issues",e)
        }
    }

    return (
        <div className={"zf-Diet"}>
            <ZFInputField
                label={t('client.survey.dietStylePlaceholder')}
                name={'dietStyle'}
                multiline
                rows={4}
                onChange={(e) => handleChange(e.target.name, e.target.value)}
                value={diet.dietStyle}
                helperText={validator['dietStyle']}
            />
            <ZFSelect
                value={diet.numberOfMeals}
                onChange={(val) => handleChange('numberOfMeals',val)}
                label={t('client.survey.dietStylePlaceholder')}
                initialText={t('client.survey.numberOfMeals')}
                error={validator['numberOfMeals']}
                options={NUMBER_OF_MEALS_PR_DAY}
            />
            <ZFToggle
                label={t('client.survey.dietStyleTitle')}
                options={DIET_PREFERENCES(t)}
                type={'radio'}
                alignment={'flex-start'}
                error={validator['dietPreference']}
                outline
                size={'small'}
                value={diet.dietPreference}
                onClick={(val) => handleChange('dietPreference',val)}
            />
            <ZFToggle
                label={t('client.survey.excludeIngredients')}
                options={FOOD_PREFERENCES(t)}
                type={'radioMulti'}
                outline
                color={'#de5d5d'}
                checkType={'exclude'}
                size={'small'}
                value={diet.clientFoodPreferences}
                onClick={(val) => handleChange('clientFoodPreferences',val)}
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
        dietState: state.signup.diet
    }
}

export default connect(mapStateToProps, { ...signup })(Diet);
