import React, {Fragment} from "react";

import CheckIcon from '@material-ui/icons/Check';

const ItemStatus = (props) => {
    const {statuses, reminders} = props;
    const taksList = [...statuses, ...reminders];
    const count = taksList.filter(status => {
      return !status.resolved;
    }).length;

    return (
        <Fragment>
            {count === 0 ? (
              <CheckIcon className="client-item-status-success" />
            ) : (
              <div className="client-item-status">
                {count} <span className="hidden-xs hidden-sm">tasks</span> 
              </div>
            )}
        </Fragment>
    )
};

export default ItemStatus;
