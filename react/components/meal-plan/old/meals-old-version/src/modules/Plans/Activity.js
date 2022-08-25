import React, { Suspense, memo } from "react";
import PlanActivity from "../../containers/PlanActivity";
import ActivityTypes from "../../constants/ActivityTypes";

const PdfDownloader = React.lazy(() => import("./PdfDownloader"));
const MacroSplitSwitcher = React.lazy(() => import("./MacroSplitSwitcher"));

const Activity = memo(() => {
  const { current, flush } = PlanActivity.useContainer();

  switch (current.type) {
    case ActivityTypes.PDF_DOWNLOAD:
      return (
        <Suspense fallback={null}>
          <PdfDownloader {...current.props} onFlush={flush} />
        </Suspense>
      );
    case ActivityTypes.MEAL_MACRO_SPLIT:
      return (
        <Suspense fallback={null}>
          <MacroSplitSwitcher {...current.props} onFlush={flush} />
        </Suspense>
      );
    default:
      return null;
  }
});

export default Activity;
