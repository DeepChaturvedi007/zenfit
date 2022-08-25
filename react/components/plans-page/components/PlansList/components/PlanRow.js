import React from "react";
import Order from "./Order";
import Actions from "./Actions";
import Status from "./Status";
import {STATUS_DONE} from "../index";

const PlanRow = ({item, onDelete, onComposeEmail}) => {
  const handleStatusClick = () => {
    if(item.status === STATUS_DONE) {
      onComposeEmail(item);
    }
  };

  return (
    <tr>
      <td><a href={`/client/info/${item.clientId}`}>{item.clientName}</a></td>
      <td>{item.purchaseTime}</td>
      <td>{item.amount}</td>
      <td>
        <Order items={item.plans} />
      </td>
      <td className={'text-right'}>
        <Status status={item.status} ready={item.ready} onClick={handleStatusClick} />
      </td>
      <td className={'text-center'}>
        <Actions
          item={item}
          onDelete={onDelete}
          onComposeEmail={onComposeEmail}
        />
      </td>
    </tr>
  );
};

export default PlanRow;