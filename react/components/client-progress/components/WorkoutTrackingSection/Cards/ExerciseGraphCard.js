import React, { Fragment, useEffect, useState } from 'react'
import Card, {
  Header,
  Title,
  Body
} from '../../../../shared/components/Card';
import { BarChart } from '../../../../shared/components/Chart';
import { CenteredText } from "../../../../shared/components/Common";
import { connect } from 'react-redux';
import moment from 'moment';
import _ from 'lodash';
import { Preloader } from '../../../../shared/components/Common';

const COLORS = {
  EXERCISE: '#3dd598',
};

const LABELS = {
  EXERCISE: 'Exercise',
};

const normalize = (item, i, source, start = 0, latest = 0) => {
  const {
    id,
    weight,
    date,
    reps,
  } = item

  let day = moment(moment(date)).format('ddd, MMM D');

  //replace comma to dot in float number in order to create valid chart data
  const weightValue = weight ? weight : 0;
  const validWeight = weightValue.toString().indexOf(',') !== -1 ? weightValue.toString().
    replace(/,/g, '.') : weightValue;

  return {
    id,
    day,
    reps,
    date,
    weight: Number(validWeight),
  }
};

const ExerciseGraphCard = ({exerciseData, loading}) => {
  const [items, setItems] = useState([]);

  useEffect(() => {
    const sliced = Object.values(exerciseData)
    const sortedArray = _.orderBy(sliced, (item) => {
      return moment(item.date).unix()
    }, ['asc'])
    setItems(sortedArray)
  }, [exerciseData])

  const latest = Math.max.apply(null, items.map(item => item.id));
  const start = Math.min.apply(null, items.map(item => item.id));

  const normalized = items.map((item, ...rest) => normalize(item, ...rest, start, latest));
  const extraOptions = {
    tooltips: {
      enabled: true,
      displayColors: false,
      backgroundColor: 'rgba(255, 255, 255, 1)',
      titleFontFamily: "'Poppins'",
      titleFontSize: 16,
      titleFontStyle: 'bold',
      titleFontColor: '#181827',
      bodyFontColor: '#696974',
      bodyFontSize: 14,
      titleMarginBottom: 10,
      xPadding: 18,
      yPadding: 14,
      borderWidth: 1,
      borderColor: 'rgba(68, 68, 79, 0.1)',
      cornerRadius: 15,
      callbacks: {
        title: function (tooltipItem) {
          const { index } = tooltipItem[0];
          const weight = normalized[index].weight;
          return `${weight} kg/lbs`;
        },
        label: function (tooltipItem) {
          const { index } = tooltipItem;
          const date = tooltipItem.label;
          const reps = normalized[index].reps;

          //Convert to array in order to display tooltip with linebreak
          let tooltip = [`${reps} reps`];
          tooltip.push(moment(date).format('MMM D'));
          return tooltip;
        }
      },
    },
    scales: {
      yAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          color: '#F2F2F6',
          zeroLineWidth: 0,
          zeroLineColor: 'white',
          drawTicks: false,
          borderDashOffset: 10
        },
        ticks: {
          stepSize: 5,
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
      xAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          zeroLineWidth: 0,
          lineWidth: 0,
          drawTicks: false,
        },
        ticks: {
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
    },
    maintainAspectRatio: true,
    annotation: false
  };

  const data = {
    labels: normalized.map(({ day }) => day),
    datasets: [
      {
        label: LABELS.EXERCISE,
        data: normalized.map(({weight}) => weight),
        barPercentage: 0.3,
        minBarLength: 0,
        barThickness: 7,
        maxBarThickness: 7,
        backgroundColor: normalized.map(() => COLORS.EXERCISE),
      }
    ],
  };

  const exerciseName = Object.keys(_.groupBy(items, 'name'))[0] || null;
  return (
    <Card className='progress-exercises-graph' style={{overflow: "visible"}}>
      <Header style={{flexFlow: 'column wrap', alignItems: 'flex-start'}}>
        <Title style={{marginBottom: '12px'}}>Strength progress</Title>
      </Header>
        {
          !loading ?
            <Fragment>
              <p className="text-success font-bold" style={{
                fontSize: '24px',
                lineHeight: '1',
                paddingLeft: '25px'
              }}>{exerciseName}</p>
              <Body style={{alignItems: 'center', paddingLeft: '46px'}}>
                {
                  normalized.length ?
                    <BarChart data={data} options={extraOptions} height={350} width={635}/> :
                    <CenteredText text={'No data'} />
                }
              </Body>
            </Fragment>:
            <Preloader/>
        }

    </Card>
  );
};

const mapStateToProps = state => {
  const exerciseData = state.stats.exercise || [];
  const loading = state.stats.exerciseLoading || false;
  return {
    exerciseData,
    loading
  }
};

export default connect(mapStateToProps)(ExerciseGraphCard);
