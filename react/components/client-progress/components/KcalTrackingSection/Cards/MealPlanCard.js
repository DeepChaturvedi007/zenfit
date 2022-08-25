import React, { Fragment, useEffect, useState } from 'react'
import Card, {Body, Header as CardHeader, Title as CardTitle} from '../../../../shared/components/Card';
import {getHumanizedDifference} from '../../../helpers/utils';
import moment from 'moment'

const MealPlanCard = ({info}) => {
  const [lastChanged, setLastChanged] = useState(getHumanizedDifference());
  const {
    updatedAt,
    name,
    createdAt,
    meta
  } = info;

  useEffect(() => {
    if(!updatedAt) return;
    const updated = new Date(updatedAt.replace(/\s/, 'T'));
    const humanizedLastChanged = getHumanizedDifference(updated);

    setLastChanged(humanizedLastChanged)
  }, [updatedAt]);

  const from  = moment(createdAt);
  const to = moment();
  const duration = to.diff(from, 'weeks');

  return (
    <Card className={'fs-default text-muted'}>
      <CardHeader>
        <CardTitle>
          Subscribing to
        </CardTitle>
      </CardHeader>
      <Body className={'a-start'}>
        {
          name ? (
            <Fragment>
              <p className="text-success font-bold" style={{fontSize: "24px", lineHeight: "1"}}>{name}</p>
              <div style={{display: "flex", flexFlow: "row wrap"}}>
                {
                  (meta && createdAt) &&
                    (
                      <div style={{width: "100%"}}>
                        <progress className="kpi-progress" value={duration} max={meta.duration}></progress>
                      </div>
                    )
                }
                {
                  (meta && createdAt) &&
                  (
                    <div
                      style={{fontFamily: "Roboto", color: "#696974", fontSize: "14px",  marginTop: "10px", marginRight: "5px"}}
                      className="kpi-label"
                    >
                        Week {duration} of {meta.duration} on this plan
                    </div>
                  )
                }
                <div style={{backgroundColor: "rgba(61, 213, 152, 0.1)", borderRadius: "5px", padding: "5px", marginTop: "5px"}}>
                  <span style={{color: "#3dd598", fontFamily: "Roboto"}}>Updated {lastChanged}</span>
                </div>
              </div>
            </Fragment>
          ) : (
            <p className="fs-md text-dark font-bold">N/A</p>
          )
        }
      </Body>
    </Card>
  );
};

export default MealPlanCard;
