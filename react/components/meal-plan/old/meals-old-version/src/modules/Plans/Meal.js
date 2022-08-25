import React, { useCallback }  from "react";
import { Droppable } from "react-beautiful-dnd";
import { ReactComponent as AddCircleIcon } from "remixicon/icons/System/add-circle-line.svg";
import Dish from "./Dish";
import Popup from "../../Popup";
import MealPercentWeight from "./MealPercentWeight";
import PlanActivityContainer from "../../containers/PlanActivity";
import ModalContainer from "../../containers/Modal";
import ActivityTypes from "../../constants/ActivityTypes";
import ModalTypes from "../../constants/ModalTypes";
import MacroSplitTypes from "../../constants/MacroSplitTypes";
import MasterMealPlanTypes from "../../constants/MasterMealPlanTypes";
import { Button, Link } from "../../components/UI";
import {
  BoardActions,
  BoardColumn,
  BoardHeader,
  BoardHeaderTop,
  BoardHeaderMeta,
  BoardTitle,
  BoardItems
} from "../../components/Board";

const macroSplitOptions = Object.keys(MacroSplitTypes).slice(1).map(value => ({
  value: parseInt(value, 10),
  label: MacroSplitTypes[value],
}));

const Meal = React.memo(({ meal, planId, planType, onRemoveDish, isLoading, viewMealPlan }) => {
  const activity = PlanActivityContainer.useContainer();
  const { show: modalShow } = ModalContainer.useContainer();

  const selectMacroSplit = (event, item) => {
    event.preventDefault();

    if (window.confirm('Are you sure you wish to change macro split for this meal?')) {
      activity.dispatch(ActivityTypes.MEAL_MACRO_SPLIT, { meal, value: item.value });
    }
  };

  const onPlanView = useCallback(() => {
    window.location = viewMealPlan(planId);
  }, [planId]);

  const renderMacroSplitTrigger = () => {
    let index = macroSplitOptions
      .findIndex(item => item.value === meal.macroSplit);

    if (index < 0) {
      index = 0;
    }

    return (
      <Link href="#" style={{zIndex: 20}}>{macroSplitOptions[index].label}</Link>
    );
  };

  return (
    <BoardColumn disabled={isLoading}>
      <BoardHeader>
        <BoardHeaderTop>
          <BoardTitle>{meal.name}</BoardTitle>
          <MealPercentWeight
            planId={planId}
            meal={meal}
          />
        </BoardHeaderTop>
        <BoardHeaderMeta>
          <span>{meal.ideal_totals.kcal} kcals</span>
          {planType === MasterMealPlanTypes.TYPE_FIXED_SPLIT &&
            <Popup
              value={meal.macroSplit}
              options={macroSplitOptions}
              onSelect={selectMacroSplit}
              renderTrigger={renderMacroSplitTrigger}
            />
          }
        </BoardHeaderMeta>
      </BoardHeader>
      <Droppable droppableId={meal.id}>
        {(provided, snapshot) => (
          <BoardItems
            loading={isLoading}
            ref={provided.innerRef}
            isDraggingOver={snapshot.isDraggingOver}
            {...provided.droppableProps}
          >
            {meal.meals.map((dish, index) => {
              return (
                <Dish
                  dish={dish}
                  index={index}
                  mealId={meal.id}
                  planId={planId}
                  onPlanView={onPlanView}
                  key={`dish_${dish.id}`}
                  onShowRecipes={modalShow.bind(null, ModalTypes.RECIPES, { meal, dish })}
                  onRemove={onRemoveDish.bind(null, meal.id, index, true)}
                />
              );
            })}
            {provided.placeholder}
          </BoardItems>
        )}
      </Droppable>
      <BoardActions>
        <Button type="button" onClick={modalShow.bind(null, ModalTypes.RECIPES, { meal })}>
          <AddCircleIcon/>
          <span>Add alternative</span>
        </Button>
      </BoardActions>
    </BoardColumn>
  );
});

export default Meal;
