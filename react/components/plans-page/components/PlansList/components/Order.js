import React, {Fragment} from "react";
import {Button} from "../../common/ui/Button";

const Order = ({items}) => {
  return (
    <Fragment>
      {
        items.map(({text, onClick, isCompleted}, i) => {
          const variant = isCompleted ? 'success' : 'warning';
          return (
            <Button
              onClick={isCompleted ? () => null : onClick}
              variant={variant}
              key={i}>
              {text}
              {isCompleted && <i className={'fa fa-check'} />}
            </Button>
          )
        })
      }
    </Fragment>
  );
};

export default Order;