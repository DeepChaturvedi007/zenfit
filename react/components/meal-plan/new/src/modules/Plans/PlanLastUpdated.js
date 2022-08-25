import React, { Fragment } from "react";
import { PlanLastUpdatedLabel } from '../../components/Plan';

const PlanLastUpdated = React.memo(({ date }) => {
    return (
        <PlanLastUpdatedLabel>
            Last updated: {moment(date).format("DD, MMM YYYY")}
        </PlanLastUpdatedLabel>
    );
});

export default PlanLastUpdated;