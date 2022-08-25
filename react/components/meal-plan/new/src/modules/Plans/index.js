import React, { Fragment, Suspense, useState } from "react";
import orderBy from "lodash/orderBy";
import Collapse from '@material-ui/core/Collapse';
import styled from 'styled-components';

import MealPlansContainer from "../../containers/MealPlans";
import PlanActivityContainer from "../../containers/PlanActivity";
import PlansLoading from "./PlansLoading";
import Plan from './Plan';
import ManualPlan from './ManualPlan';

const ShowAll = styled.div`
  padding: 10px 0px;
  text-align: center;
  color: #0084ff;
  cursor: pointer;
  font-weight: 500;
`;

const Plans = React.memo(() => {
  const container = MealPlansContainer.useContainer();
  const plans = orderBy(container.data.plans, ['id'], ['desc']);
  const generatedPlans = plans.filter(plan => plan.contains_alternatives);
  const manualPlans = plans.filter(plan => !plan.contains_alternatives);

  const [openedPlan, setOpenedPlan] = useState([0]);
  const [showAll, setShowAll] = useState(false);

  const handleOpenMeal = (index) => {
    const open_list = openedPlan;
    if (openedPlan.length === 1) {
      setOpenedPlan([...openedPlan, index])
    }
    else {
      open_list[0] = open_list[1];
      open_list[1] = index;
      setOpenedPlan([...open_list])
    }
  }

  if (container.loading) {
    return <PlansLoading />;
  }

  const handleShowAll = () => {
    setShowAll((prev) => !prev);
  };

  return (
    <Suspense fallback={<PlansLoading />}>
      {plans.length ? (
        <Fragment>
          <Fragment>
            {generatedPlans.map((plan, i) => {
              if (i < 1) {
                return (
                  <PlanActivityContainer.Provider key={`plan_${plan.id}`}>
                    <Plan
                      open={openedPlan.includes(i)}
                      plan={plan}
                      onView={container.viewMealPlan}
                      onViewMeals={() => handleOpenMeal(i)}
                    />
                  </PlanActivityContainer.Provider>
                )
              }
            })}
          </Fragment>
          <Collapse in={showAll}>
            <Fragment>
              {generatedPlans.map((plan, i) => {
                if (i > 0) {
                  return (
                    <PlanActivityContainer.Provider key={`plan_${plan.id}`}>
                      <Plan
                        open={openedPlan.includes(i)}
                        plan={plan}
                        onView={container.viewMealPlan}
                        onViewMeals={() => handleOpenMeal(i)}
                      />
                    </PlanActivityContainer.Provider>
                  )
                }
              })}
            </Fragment>
          </Collapse>
          {generatedPlans.length > 1 && <ShowAll className='show-all' onClick={handleShowAll}>{showAll ? "SHOW LESS" : "SHOW ALL"}</ShowAll>}
          {manualPlans.length > 0 && (
            <Fragment>
              <br />
              <hr />
              <h5 style={{ textAlign: 'center' }}>Manual plans</h5>
              <br />
              <br />
              <Fragment>
                {manualPlans.map((plan, i) => {
                  return (
                    <PlanActivityContainer.Provider key={`plan_${plan.id}`}>
                      <ManualPlan
                        plan={plan}
                        onView={container.viewMealPlan}
                      />
                    </PlanActivityContainer.Provider>
                  )
                })}
              </Fragment>
            </Fragment>
          )}
        </Fragment>
      ) : (
          <p style={{ paddingLeft: "15px", fontWeight: "bold", textAlign: "left" }}>No Plans</p>
      )}
    </Suspense>
  );
});

export default Plans;
