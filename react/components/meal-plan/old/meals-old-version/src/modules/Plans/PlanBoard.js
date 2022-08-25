import React from "react";
import { DragDropContext } from "react-beautiful-dnd";
import MealPlansContainer from "../../containers/MealPlans";
import Meal from "./Meal";
import { Board } from "../../components/Board";
import AddMeal from "./AddMeal";
import MasterMealPlanTypes from '../../constants/MasterMealPlanTypes';

const PlanBoard = React.memo(({ planId, planType }) => {
  const { mealsByPlan, onDishDragEnd, onRemoveDish, isMealSyncing, viewMealPlan } = MealPlansContainer.useContainer();
  const meals = mealsByPlan(planId);

  return (
    <Board>
      <DragDropContext
        onDragEnd={onDishDragEnd}
      >
        {meals.map(meal =>
          <Meal
            id={meal.id}
            meal={meal}
            planType={planType}
            planId={planId}
            viewMealPlan={viewMealPlan}
            onRemoveDish={onRemoveDish}
            isLoading={isMealSyncing(meal.id)}
            key={`meal_${meal.id}`}
          />
        )}
        {planType === MasterMealPlanTypes.TYPE_FIXED_SPLIT &&
          <AddMeal planId={planId} />
        }
      </DragDropContext>
    </Board>
  );
});

export default PlanBoard;
