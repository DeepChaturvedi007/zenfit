import React, { memo, useEffect } from "react";
import { useLoads } from "react-loads";
import MealPlansContainer from "../../containers/MealPlans";
import { timeout } from "../../utils/helpers";
import { CardActivity, CardActivityIcon, CardActivityText } from "../../components/Card";
import * as api from "../../utils/api";

const MESSAGES = {
  pending: 'Updating macro split...',
  resolved: 'Macro split successfully updated.',
  rejected: 'Something was wrong, try again.',
};

const MacroSplitSwitcher = memo(({ meal, value, onFlush }) => {
  const { updateMacroSplit, setSync } = MealPlansContainer.useContainer();
  const fetch = () => api.updateMeal({
    macroSplit: value,
    plan: meal.planId,
    parent: meal.id,
    type: meal.type,
  });

  const { response, error, isRejected, isPending, isResolved } = useLoads(
    fetch,
    {},
    [meal.id, value]
  );

  useEffect(() => {
    setSync({mealId: meal.id});
  }, []);

  useEffect(() => {
    setSync(null);

    if (response) {
      updateMacroSplit(meal, response);
      timeout(3000).then(onFlush);
    }
  }, [isResolved]);

  useEffect(() => {
    setSync(null);
    timeout(5000).then(onFlush);
  }, [isRejected]);

  let status;
  let message;

  if (isPending) {
    status = 'pending';
  } else if (isResolved) {
    status = 'resolved';
  } else if (isRejected) {
    try {
      message = error.response.data.error;
    } catch (e) {}
    status = 'rejected';
  }

  if (!message) {
    message = MESSAGES[status];
  }

  return (
    <CardActivity type={status} loading={isPending}>
      <CardActivityIcon>
        {isPending && <div>Pending</div>}
        {isResolved && <div>Resolved</div>}
        {isRejected && <div>Rejected</div>}
      </CardActivityIcon>
      <CardActivityText>{message}</CardActivityText>
    </CardActivity>
  );
});

export default MacroSplitSwitcher;
