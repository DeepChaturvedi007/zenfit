import React from "react";
import { createContainer } from "../utils/unstated";

function usePlanActivity() {
  const [current, setCurrent] = React.useState(null);

  /**
   * @param {string} type
   * @param {?Object} props
   */
  const dispatch = (type, props = {}) => {
    setCurrent({ type, props });
  };

  const flush = () => {
    setCurrent(null);
  };

  return { current, dispatch, flush };
}

const PlanActivity = createContainer(usePlanActivity);

export default PlanActivity;


