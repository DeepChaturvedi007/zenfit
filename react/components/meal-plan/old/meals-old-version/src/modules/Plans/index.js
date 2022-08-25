import React, { Suspense, useState } from "react";
import orderBy from "lodash/orderBy";
import MealPlansContainer from "../../containers/MealPlans";
import PlanActivityContainer from "../../containers/PlanActivity";
import PlansLoading from "./PlansLoading";

const Plan = React.lazy(() => import('./Plan'));

const Plans = React.memo(() => {
  const container = MealPlansContainer.useContainer();
  const plans = orderBy(container.data.plans, ['id'], ['desc'])
  const [openedPlan, setOpenedPlan] = useState([0]);

  const handleOpenMeal = (index) => {
    const open_list = openedPlan;
    if(openedPlan.length === 1) {
      setOpenedPlan([...openedPlan, index])
    }
    else {
      open_list[0] = open_list[1];
      open_list[1] = index;
      setOpenedPlan([...open_list])
    }
  }
  if (container.loading) {
    return <PlansLoading/>;
  }

  return (
    <Suspense fallback={<PlansLoading/>}>
      {plans.map((plan, i) =>
        <PlanActivityContainer.Provider key={`plan_${plan.id}`}>
          <Plan
            open={openedPlan.includes(i)}
            plan={plan}
            onView={container.viewMealPlan}
            onViewMeals={() => handleOpenMeal(i)}
          />
        </PlanActivityContainer.Provider>
      )}
    </Suspense>
  );
});

export default Plans;
