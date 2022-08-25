import React from 'react';
import Card, {CardContent, CardTitle} from "../common/Card";
import numeral from "numeral";

const NewTrainersCard = ({total, value, onClick}) => {
  const percents = Math.round(Number(value) * 100 / Number(total));
  const title = 'New Trainers (< 2 weeks)';
  return (
    <Card onClick={onClick}>
      <CardTitle>
          {title}
      </CardTitle>
      <CardContent>
        <h1 className="no-margins">{numeral(value).format('0,0')}</h1>
        <div className="stat-percent font-bold text-success">{percents}% <i className="fa fa-bolt" /></div>
        <small>Total users</small>
      </CardContent>
    </Card>
  )
};

export default NewTrainersCard;
