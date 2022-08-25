import React, { useEffect, useState } from 'react'
import Card, {
  Body as CardBody,
  Header as CardHeader,
  Title as CardTitle,
} from '../../../../shared/components/Card';
import CircleChart from '../../../../shared/components/Chart/CircleChart';
import { roundToDecimals } from '../../../helpers/utils';
import { connect } from 'react-redux';
import { Preloader } from '../../../../shared/components/Common'

const CommonStatsCard = ({ currentPlan, workoutsData, loading }) => {

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

  const { meta: { workoutsPerWeek: goal = 0 } = {} } = currentPlan || {};
  const progress = workoutsData.length || 0;
  const diff = roundToDecimals(progress - goal, 0);
  const progressText = diff === 0 ? '0' : (diff > 0 ? `+${diff}` : diff)
  let progressValue = (goal === 0 || !goal) ? 0 : (progress / goal) * 100;
  progressValue = Math.min(Math.max(progressValue, 0), 100);

  return (
    <Card className={'fs-default text-muted'}>
      <CardHeader>
        <CardTitle>
          Weekly workout stats
        </CardTitle>
      </CardHeader>
      <CardBody className={'j-between a-stretch'}>
        {
          !loading ?
            <div className="progress-circle-chart"
                 style={{ display: 'flex', alignItems: 'center' }}>
              <div>
                <CircleChart
                  prefixText=''
                  progressText={progressText}
                  suffixText=''
                  progress={progressValue}
                  viewBox={'0 0 250 250'}
                />
              </div>
              <div style={{
                display: 'flex',
                flexFlow: 'column wrap',
                alignItems: 'flex-start',
                marginLeft: '10px',
              }}>
                <p className="text-dark font-bold" style={{
                  fontFamily: 'Roboto',
                  marginBottom: '0',
                  fontSize: '24px',
                }}>
                  {progress === 1 ? (
                    <span>1 workout</span>
                  ) : (
                    <span>{progress} workouts</span>
                  )}
                </p>
                {goal > 0 &&
                  <p style={{
                    fontFamily: 'Roboto'
                  }}>of <span>{goal}</span> workouts goal</p>
                }
              </div>
            </div> :
            <Preloader />
        }

      </CardBody>
    </Card>
  );
};

const mapStateToProps = state => {
  const currentPlan = state.stats.currentPlan || null;
  const workoutsData = state.stats.workouts || [];
  const loading = state.stats.workoutsLoading;
  return {
    currentPlan,
    workoutsData,
    loading
  }
};

export default connect(mapStateToProps)(CommonStatsCard);
