import React, { Fragment, useEffect, useState } from 'react'
import Card, {Body, Header as CardHeader, Title as CardTitle} from '../../../../shared/components/Card';
import {getHumanizedDifference} from '../../../helpers/utils';
import moment from 'moment';
import { connect } from 'react-redux'

const WorkoutPlanCard = ({ currentPlan }) => {
  const [lastChanged, setLastChanged] = useState(getHumanizedDifference())

  const sortByDateAsc = function(a, b) {
    const timeA = (new Date(a.date)).getTime();
    const timeB = (new Date(b.date)).getTime();
    if (timeA < timeB) {
      return -1;
    }
    if (timeA > timeB) {
      return 1;
    }
    return 0;
  };

  const {
    name,
    created: createdAt,
    last_updated: updatedAt,
    meta: { duration = 0 } = {},
  } = currentPlan;

  useEffect(() => {
    if (!updatedAt) {
      return
    }
    const updated = new Date(updatedAt.replace(/\s/, 'T'))
    const humanizedLastChanged = getHumanizedDifference(updated)

    setLastChanged(humanizedLastChanged)
  }, [updatedAt])

  const from = moment(createdAt)
  const to = moment();
  //round up duration in order to display current week
  const diff = to.diff(from) > 0 ? to.diff(from) : 0;
  const progress = Math.ceil(moment.duration(diff).asWeeks());

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
              <p className="text-success font-bold" style={{
                fontSize: '24px',
                lineHeight: '1',
                marginBottom: '15px',
              }}>{name}</p>
              <div style={{ display: 'flex', flexFlow: 'row wrap' }}>
                {
                  !!duration &&
                  (
                    <div style={{ width: '100%' }}>
                      <progress className="kpi-progress" value={progress} max={duration}></progress>
                    </div>
                  )
                }
                {
                  !!duration &&
                  (
                    <div
                      style={{
                        fontFamily: 'Roboto',
                        color: '#696974',
                        fontSize: '14px',
                        marginTop: '10px',
                        marginRight: '5px',
                      }}
                      className="kpi-label"
                    >
                      Week {progress} of {duration} on this plan
                    </div>
                  )
                }
                <div style={{
                  backgroundColor: 'rgba(61, 213, 152, 0.1)',
                  borderRadius: '5px',
                  padding: '5px',
                  marginTop: '5px',
                }}>
                  <span style={{
                    color: '#3dd598',
                    fontFamily: 'Roboto',
                  }}>Updated {lastChanged}</span>
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

const mapStateToProps = state => {
  const currentPlan = state.stats.currentPlan || [];
  return {
    currentPlan
  }
};

export default connect(mapStateToProps)(WorkoutPlanCard);
