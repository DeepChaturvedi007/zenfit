import {Button} from "../../common/ui/Button";
import React from "react";
import {
  STATUS_COMPLETED,
  STATUS_DONE,
  STATUS_PENDING
} from "../index";
import {Preloader} from "../../../../shared/components/Common";

const Status = ({status, ready = true, onClick}) => {
  if(!ready) {
    return (
      <Preloader type={'ThreeDots'} height={null} timeout={0}/>
    )
  }
  let variant;
  switch (status) {
    case STATUS_DONE: {
      variant = 'primary';
      break;
    }
    case STATUS_COMPLETED: {
      variant = 'success';
      break;
    }
    case STATUS_PENDING: {
      variant = 'warning';
      break;
    }
    default: {
      variant = 'primary';
    }
  }
  return (
    <Button variant={variant} onClick={onClick}>{status}</Button>
  )
};

export default Status;