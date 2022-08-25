import React, { useEffect, useState } from 'react';
import './styles.scss'
import ModalComponent from "../ClientDetails/Modules/ModalComponent";
import SectionInfoComponent from '../ClientDetails/Modules/SectionInfoComponent';
import { CircularProgress, Button } from '@material-ui/core';
import Alert from '@material-ui/lab/Alert';
import { Col, Row } from "../../../shared/components/Grid";
import { GOAL_TYPE, LOCALES } from '../../const';
import { MACRO_SPLITS, MEALS_PER_DAY, CLIENT_FOOD_PREFERENCES, PAL_SLIDER, ALTERNATIVES } from '../../constants/Meal';
import { GET_RECIPE_INGREDIENTS } from "../../../../api/clients";
import { computeKcals } from '../../helpers';
import MacroTabs from "./MacroTabs";
import produce from 'immer';
import _ from 'lodash';
import axios from 'axios';

export default function ModalMealPlan(props) {
    const {
        show,
        onClose,
        generateMealPlan,
        selectedClient,
        mealPlanModalError,
        mealPlanModalLoading
    } = props;

    const updateCalculator = (value, name) => {
        setCalculator({ ...calculator, [name]: value });
    };

    const updateMealPlanConfig = (value, name) => {
        setMealPlanConfig({ ...mealPlanConfig, [name]: value });
    };

    const updateMacros = (value, name) => {
        setMealPlanConfig(produce(draft => {
            draft.macros[name] = value;
        }));
    }

    const handleChangeTabs = tabId => {
        updateMealPlanConfig(tabId, 'type');
    }

    const updateOptionsList = async (inputValue) => {
        setIngredientLoading(true);

        let q = inputValue;
        const requestData = {
            q,
            locale: mealPlanConfig.locale
        };
        await axios.get(GET_RECIPE_INGREDIENTS(), { params: requestData })
            .then(({ data }) => {
                let tempData = {};
                Object.values(data).map(item => {
                    tempData = Object.assign(tempData, { [item.id]: item.name })
                });
                setExcludeIngredientsList(tempData);
                setIngredientLoading(false);
            });
    }

    const [tempKcals, setTempKcals] = useState(0);
    const [success, setSuccess] = useState(false)
    const [kcals, setKcals] = useState(null);
    const [bmr, setBmr] = useState(null);
    const [tdee, setTdee] = useState(null);
    const [calculator, setCalculator] = useState({
        gender: null,
        weight: null,
        height: null,
        age: null,
        pal: null,
        goalType: null,
        gramsPerWeek: 500,
    });
    const [excludeIngredientsList, setExcludeIngredientsList] = useState([]);
    const [ingredientsLoading, setIngredientLoading] = useState(false);

    const initialMealConfig = {
        name: null,
        numberOfMeals: 3,
        alternatives: 4,
        type: 1,
        prioritize: true,
        excludeIngredients: [],
        locale: null,
        avoid: [],
        desiredKcals: 0,
        macroSplit: 1,
        macros: { carbohydrate: 0, protein: 0, fat: 0 },
    }

    const [mealPlanConfig, setMealPlanConfig] = useState(initialMealConfig);

    const submit = () => {
        generateMealPlan(mealPlanConfig);
    };

    useEffect(() => {
        if (selectedClient) {
            let title = `${selectedClient.name} - ${mealPlanConfig.desiredKcals} kcal - ${moment().format("DD.MM.YYYY")}`
            updateMealPlanConfig(title, "name")
        }
    }, [mealPlanConfig.desiredKcals, show])

    useEffect(() => {
        if (show && selectedClient) {
            setCalculator({
                ...calculator,
                gender: selectedClient.info.gender,
                weight: selectedClient.info.startWeight,
                height: selectedClient.info.height,
                age: selectedClient.info.age,
                pal: selectedClient.info.pal,
                goalType: selectedClient.info.goalType
            });

            setMealPlanConfig({
                ...mealPlanConfig,
                numberOfMeals: selectedClient.info.numberOfMeals ? selectedClient.info.numberOfMeals : mealPlanConfig.numberOfMeals,
                locale: selectedClient.info.locale,
                avoid: selectedClient.info.clientFoodPreferences,
                desiredKcals: selectedClient.previous_kcals
            });
        }
    }, [show, selectedClient]);

    useEffect(() => {
        const calculate = () => {
            const calculatorHelper = computeKcals(
                calculator.gender,
                calculator.weight,
                calculator.height,
                calculator.age,
                calculator.pal,
                calculator.goalType,
                calculator.gramsPerWeek,
                selectedClient.measuringSystem
            );

            setTdee(calculatorHelper.tdee);
            setKcals(calculatorHelper.kcals);
            setBmr(calculatorHelper.bmr);
        };

        if (show) {
            calculate();
        }
    }, [calculator])

    useEffect(() => {
        const kcals = mealPlanConfig.macros.carbohydrate * 4 + mealPlanConfig.macros.protein * 4 + mealPlanConfig.macros.fat * 9;
        updateMealPlanConfig(kcals, 'desiredKcals');
    }, [mealPlanConfig.macros]);

    useEffect(() => { success && setTimeout(() => setSuccess(false), 1500) }, [success])

    useEffect(() => {
        if (tempKcals !== mealPlanConfig.desiredKcals) {
            updateMealPlanConfig(tempKcals, "desiredKcals");
            setTempKcals(mealPlanConfig.desiredKcals);
        }
    }, [mealPlanConfig.type]);

    const calMacroPercentage = (val) => {
        const numb = (val / mealPlanConfig.desiredKcals * 100).toFixed(1)
        return numb !== "NaN" ? numb : 0
    }

    const missingMetrics = (calculator.gender === null ||
        calculator.weight === null ||
        calculator.height === null ||
        calculator.age === null ||
        calculator.goalType === null);

    return (
        <ModalComponent open={show} onClose={onClose} title={"Create Meal Plan"} className={"meal-modal"}>
            <div className='calculator-container'>
                {missingMetrics && (
                    <Row className={"errorWall"}>
                        <Col size={12}>
                            <h3>In order to compute kcals, please update the following metrics:</h3>
                            <ul>
                                {Object.keys(calculator).map((field, index) => {
                                    if (calculator[field] === null) {
                                        return <li key={index}>{field === 'pal' ? 'Activity Level: ' + _.startCase(field) : _.startCase(field)}</li>;
                                    }
                                })}
                            </ul>
                        </Col>
                    </Row>
                )}
                <div className={`calwrapper ${(missingMetrics ? "errorBlur" : "")}`}>
                    <Row>
                        <Col size={4}>
                            <SectionInfoComponent
                                title={'Goal Type'}
                                value={calculator.goalType}
                                type={'select'}
                                name={'goalType'}
                                optionsList={GOAL_TYPE}
                                valueChange={updateCalculator}
                            />
                        </Col>
                        <Col size={1}></Col>
                        <Col size={6}>
                            <SectionInfoComponent
                                title={'PAL'}
                                value={calculator.pal}
                                type={'slider'}
                                name={'pal'}
                                valueChange={updateCalculator}
                                marks={PAL_SLIDER}
                                step={0.05}
                                min={1.4}
                                max={2.4}
                            />
                        </Col>
                        <Col size={1}></Col>
                    </Row>
                    <Row className={"numbs"}>
                        <Col size={6}>
                            <SectionInfoComponent
                                title={GOAL_TYPE[calculator.goalType] === 'Lose Weight' ? 'Weekly Loss' : 'Weekly Gain'}
                                value={calculator.gramsPerWeek}
                                type={'number'}
                                name={'gramsPerWeek'}
                                valueChange={updateCalculator}
                            />
                        </Col>
                        <Col size={2}>
                            <SectionInfoComponent
                                title={'BMR'}
                                value={bmr}
                                type={'static'}
                            />
                        </Col>
                        <Col size={2}>
                            <SectionInfoComponent
                                title={'TDEE'}
                                value={tdee}
                                type={'static'}
                            />
                        </Col>
                        <Col size={2}>
                            <SectionInfoComponent
                                title={'KCALS'}
                                value={kcals}
                                type={'static'}
                            />
                        </Col>
                    </Row>
                    <Row>
                        <Col size={8}></Col>
                        <Col size={4}>
                            <button className={"zenfitBtn"} onClick={() => { updateMealPlanConfig(kcals, 'desiredKcals'); setSuccess(true) }}>
                                {!success ? "Apply" : "Applied!"}
                            </button>
                        </Col>
                    </Row>
                </div>
            </div>
            <div className='meal-plan-config-container'>
                <Row>
                    <Col size={4}>
                        <SectionInfoComponent
                            title={'Meals Per Day'}
                            value={mealPlanConfig.numberOfMeals}
                            type={'btnGroup'}
                            optionsList={MEALS_PER_DAY}
                            name={'numberOfMeals'}
                            valueChange={updateMealPlanConfig}
                        />
                    </Col>
                </Row>
                <Row>
                    <Col size={7}>
                        <SectionInfoComponent
                            title={'Alternatives'}
                            value={mealPlanConfig.alternatives}
                            type={'btnGroup'}
                            optionsList={ALTERNATIVES}
                            name={'alternatives'}
                            valueChange={updateMealPlanConfig}
                        />
                    </Col>
                </Row>
                <Row>
                    <Col size={7}>
                        <SectionInfoComponent
                            title={'Prioritize favorites and own recipes'}
                            value={mealPlanConfig.prioritize}
                            type={'toggle'}
                            name={'prioritize'}
                            valueChange={updateMealPlanConfig}
                        />
                    </Col>
                </Row>
                <Row className={"allergiesRow"}>
                    <Col size={6}>
                        <SectionInfoComponent
                            title={'Allergies / Preferences'}
                            value={mealPlanConfig.avoid}
                            type={'multiSelect'}
                            optionsList={CLIENT_FOOD_PREFERENCES}
                            name={'avoid'}
                            valueChange={updateMealPlanConfig}
                        />
                    </Col>
                    {/*<Col size={6}>
                        <SectionInfoComponent
                            title={'Exclude / Ingredients'}
                            value={mealPlanConfig.excludeIngredients}
                            type={'multiSelect'}
                            optionsList={excludeIngredientsList}
                            allowSelectAll={true}
                            selectLoading = {ingredientsLoading}
                            name={'excludeIngredients'}
                            valueChange={updateMealPlanConfig}
                            inputValueChange={updateOptionsList}
                        />
                    </Col>*/}
                </Row>
                <Row className={"langRow"}>
                    <Col size={6}>
                        <SectionInfoComponent
                            title={'Language'}
                            value={mealPlanConfig.locale}
                            type={'select'}
                            optionsList={LOCALES}
                            name={'locale'}
                            valueChange={updateMealPlanConfig}
                        />
                    </Col>
                </Row>
                <Row>
                    <Col size={12}>
                        <MacroTabs handleChangeKcal={handleChangeTabs}>
                            <Row>
                                <Col size={6}>
                                    <SectionInfoComponent
                                        title={'Macro Split'}
                                        valueType={"in %"}
                                        value={mealPlanConfig.macroSplit}
                                        type={'select'}
                                        optionsList={MACRO_SPLITS}
                                        name={'macroSplit'}
                                        valueChange={updateMealPlanConfig}
                                    />
                                </Col>
                                <Col size={6}>
                                    <SectionInfoComponent
                                        title={'Kcals'}
                                        value={mealPlanConfig.desiredKcals}
                                        type={'number'}
                                        name={'desiredKcals'}
                                        valueChange={updateMealPlanConfig}
                                    />
                                </Col>
                            </Row>
                            <Row>
                                <Col size={3}>
                                    <SectionInfoComponent
                                        title={'Carbs'}
                                        valueType={`gr, ${calMacroPercentage(mealPlanConfig.macros.carbohydrate * 4)} %`}
                                        value={mealPlanConfig.macros.carbohydrate}
                                        type={'number'}
                                        name={'carbohydrate'}
                                        valueChange={updateMacros}
                                    />
                                </Col>
                                <Col size={3}>
                                    <SectionInfoComponent
                                        title={'Protein'}
                                        value={mealPlanConfig.macros.protein}
                                        valueType={`gr, ${calMacroPercentage(mealPlanConfig.macros.protein * 4)} %`}
                                        type={'number'}
                                        name={'protein'}
                                        valueChange={updateMacros}
                                    />
                                </Col>
                                <Col size={3}>
                                    <SectionInfoComponent
                                        title={'Fat'}
                                        value={mealPlanConfig.macros.fat}
                                        valueType={`gr, ${calMacroPercentage(mealPlanConfig.macros.fat * 9)} %`}
                                        type={'number'}
                                        name={'fat'}
                                        valueChange={updateMacros}
                                    />
                                </Col>
                                <Col size={1}>
                                    <div className={"macroKcals"}>
                                        <SectionInfoComponent
                                            title={"KCALS"}
                                            type={"static"}
                                            value={mealPlanConfig.desiredKcals}
                                        />
                                    </div>
                                </Col>
                            </Row>
                        </MacroTabs>
                    </Col>
                </Row>
            </div>
            {mealPlanModalError && (
                <Alert severity="error">{mealPlanModalError}</Alert>
            )}
            {mealPlanModalLoading ? (
                <CircularProgress size={30} />
            ) : (
                <button className={"zenfitBtn"} color="primary" onClick={() => submit()}>
                    Generate
                </button>
            )}
        </ModalComponent>
    );
}
