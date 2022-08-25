import React, {Fragment, useCallback, useEffect, useMemo} from 'react';
import styled from "styled-components";
import ModalTypes from "../../constants/ModalTypes";
import ModalContainer from "../../containers/Modal";
import MealPlansContainer from "../../containers/MealPlans";
import MealTypes from "../../constants/MealTypes";
import AddMealForm from "../Forms/AddMealForm";
import { ModalBody } from '../../components/Modal';
import { ReactComponent as AddCircleIcon } from "remixicon/icons/System/add-circle-line.svg";
import { Button as BaseButton } from "../../components/UI";

const Wrapper = styled.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    border: 1px solid rgb(242, 246, 250);
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    right: -10px;
    @media (max-width: 992px) {
      top: initial;
      right: initial;
      left: 50%;
      transform: translateX(-50%);
      bottom: -10px;
    }
`;

const Button = styled(BaseButton)`
    padding: 10px;
`;

const ModalContent = ({onSubmit, onCancel, mealTypeOptions}) => {
    return (
        <Fragment>
            <ModalBody>
                <AddMealForm
                    onSubmit={onSubmit}
                    onCancel={onCancel}
                    mealTypesOptions={mealTypeOptions}
                />
            </ModalBody>
        </Fragment>
    )
};

const AddMeal = ({planId}) => {
    const { mealsByPlan, addParent } = MealPlansContainer.useContainer();
    const { show: modalShow, hide: modalHide, loading: setModalLoading } = ModalContainer.useContainer();

    const currentMeals = mealsByPlan(planId);
    const mealTypeOptions = useMemo(() => {
        const existingTypes = currentMeals.map(meal => Number(meal.type));
        const options = MealTypes.filter(option => !existingTypes.includes(Number(option.value)));
        return [{ value: '', label: 'Select type' }, ...options];
    }, [currentMeals]);

    const onSubmit = useCallback(async (data) => {
        await addParent(planId, data, true);
        modalHide();
    }, [planId, addParent]);

    const onCancel = useCallback(() => {
        modalHide();
    }, []);

    const contentProps = {
        onSubmit,
        onCancel,
        mealTypeOptions
    };

    const handleClick = () => {
        setModalLoading(false);
        modalShow(ModalTypes.CUSTOM, { content: () => <ModalContent {...contentProps} /> , title: 'Add extra meal' });
    };

    if (currentMeals.length < 6 && mealTypeOptions.some(option => option.value !== '')) {
        return (
            <Wrapper>
                <Button onClick={handleClick}><AddCircleIcon/></Button>
            </Wrapper>
        );
    } else {
        return null
    }
};

export default AddMeal;