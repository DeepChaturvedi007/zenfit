import React, { Fragment, useEffect, useState } from 'react';
import Card from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import SectionLoading from '../../../../spinner/SectionLoading';
import { LineChart } from '../../../../shared/components/Chart';
import { connect } from 'react-redux';
import * as clients from '../../../store/clients/actions';
import * as progress from '../../../store/progress/actions';
import moment from 'moment';
import ToolTip from './Tooltip';
import {FILTER_DEFAULT_PERIODS} from "../../../../../shared/helper/timeHelper";
import ZFToggle from "../../../../../shared/UI/Toggle";
import {PERIOD_GRAPH_TOGGLE} from "../../../const";
const LABELS = {
  EXERCISE: 'Exercise',
  KCALS:'Kcals'
};

const normalize = (item, i, source, start = 0, latest = 0) => {
  const {
    id,
    weight,
    date,
    tooltip,
    kcal
  } = item
  let day = moment(moment(date)).format('MMM DD');

  return {
    id,
    day,
    tooltip,
    date,
    weight,
    kcal
  }
};

const ClientProgression = ({ clientGoalWeight, measuringSystem,clientKcals, handleTooltip, isTooltipShow, loading, clientProgress, updateCheckInInfo, fetchGraphKcalsAction, handleExpand, graphExpand}) => {
  const [items, setItems] = useState([]);
  const [showKcal, setShowKcal] = useState(true);
  const [shownPeriod, setShownPeriod] = useState(3);
  const [goalWeight, setGoalWeight] = useState([]);
  const [tooltipData, setTooltipData] = useState([]);
  const [top, setTop] = useState(0);
  const [left, setLeft] = useState(0);
  const [minKcal, setMinKcal] = useState(0);
  const [maxKcal, setMaxKcal] = useState(0);
  const [minWeight, setMinWeight] = useState(0);
  const [maxWeight, setMaxWeight] = useState(0);
  const [chartWidth, setChartWidth] = useState(0);
  const [arrowLeft, setArrowLeft] = useState(0);
  const [arrowTop, setArrowTop] = useState(0);
  const bodyRef = React.useRef(null);
  const tooltipRef = React.useRef(null);

  useEffect(() => {
    if (clientProgress && clientProgress.metrics && clientKcals) {
      /*set Chart width*/
      bodyRef.current && setChartWidth(bodyRef.current.clientWidth);
      let dateList = [];
      let goal_weight = [];

      /*Get weight vals and dates*/
      let sliced = Object.values(clientProgress.checkIns);

      /*Add client kcal to slicedArray*/
      Object.entries(clientKcals).map(clientKcal => {
        /*Add kcal if kcaldate is same as weightDate*/
        sliced.map(slice =>
            moment(slice[0].date).isSame(clientKcal[0])
            && Object.assign(slice[0],{...slice[0],kcal:clientKcal[1]})
        );
      });

      Object.entries(clientKcals).map(clientKcal => {
        /*Add kcal if kcaldate is not same as weightDate*/
        !sliced.some(item => moment(item.date).isSame(clientKcal[0])) && (
            sliced.push([{
              checkIns: null,
              date: clientKcal[0],
              images: null,
              weight: null,
              kcal:clientKcal[1]
            }])
        )
      });

      /*Add missing kcal attributes with null value and then sort by date*/
      let sortedArray = sliced.map(
          slice => !slice[0].kcal
              ? [Object.assign(...slice[0],{...slice[0],kcal:null})]
              : [slice[0]]
          ).sort((a,b) => moment(a[0].date).diff(b[0].date));

      let last_weight = 0;

      /*Add diff weight, and toolTips*/
      let tempKcal = 0
      let tempWeight = 0

      sortedArray.map(function (item) {

        /*add kcal number to temp so it can be used for next item if empty tooltip */
        item[0].kcal && (
            tempKcal = item[0].kcal
        );
        /*Use temp kcal to replace null val*/
        item[0].weight && (
            tempWeight = item[0].weight
        );

        let itemList = {
          weight: item[0].weight,
          date: item[0].date,
          tooltip: [],
          kcal:item[0].kcal
              ? item[0].kcal
              : (tempKcal === 0 ? null : tempKcal)
        }

        let checkInInfo = {};
        if (item[0].checkIns !== null) {
          checkInInfo = { ...item[0].checkIns[0] };
        }

        let check_in_item = [];
        /*Add to tooltip*/
        let check_in = {
          weight: parseFloat(item[0].weight).toFixed(1),
          diffWeight: (parseFloat(item[0].weight) - parseFloat(last_weight)).toFixed(1),
          date: item[0].date,
          kcal: item[0].kcal
              ? item[0].kcal
              : (tempKcal === 0 ? null : tempKcal),
          ...checkInInfo
        };

        check_in_item.push(check_in);
        itemList.tooltip = check_in_item;

        /*Set last weight, if null*/
        last_weight = item[0].weight ? item[0].weight : tempWeight;
        dateList.push(itemList);
        goal_weight.push(clientGoalWeight);

      });

      dateList.sort((a,b) => moment(a.date).diff(b.date));


      /*Find the value of weight Y axis and remove null weights*/
      const min = Math.min.apply(null, dateList.filter(item => item.weight !== null).map(function (item) {
        return parseFloat(item.weight);
      }));

      /*Find the value of weight Y axis remove null weights*/
      const max = Math.max.apply(null, dateList.filter(item => item.weight !== null).map(function (item) {
        return parseFloat(item.weight);
      }));

      const kcalMin = Math.min.apply(null, dateList.filter(item => item.kcal !== null).map(function (item) {
        return parseFloat(item.kcal) - 200;
      }));

      const kcalMax = Math.max.apply(null, dateList.filter(item => item.kcal !== null).map(function (item) {
        return parseFloat(item.kcal) + 100;
      }));

      let minHeight = (min > clientGoalWeight ? clientGoalWeight : min) - 5;
      let maxHeight = (max < clientGoalWeight ? clientGoalWeight : max) + 5;

      if (maxHeight > 1000) {
        maxHeight = 1000;
      }

      setMinKcal(kcalMin);
      setMaxKcal(kcalMax);
      setMinWeight(minHeight);
      setMaxWeight(maxHeight);
      setItems(dateList);
      setGoalWeight(goal_weight)
    }
  }, [clientProgress,clientKcals, bodyRef])

  useEffect(() => {
    fetchGraphKcalsAction()
  },[])

  const latest = Math.max.apply(null, items.map(item => item.id));
  const start = Math.min.apply(null, items.map(item => item.id));
  const point = items.reduce((accumulator, currentValue) => {
    if (currentValue.tooltip.length === 0) {
      accumulator.push(0);
      return accumulator;
    }
    else {
      accumulator.push(5);
      return accumulator;
    }
  }, []);
  const pointHover = items.reduce((accumulator, currentValue) => {
    if (currentValue.tooltip.length === 0) {
      accumulator.push(0);
      return accumulator;
    }
    else {
      accumulator.push(6);
      return accumulator;
    }
  }, []);
  const pointBorder = items.reduce((accumulator, currentValue) => {
    if (currentValue.tooltip.length === 0) {
      accumulator.push(0);
      return accumulator;
    }
    else {
      accumulator.push(2);
      return accumulator;
    }
  }, []);
  const pointStyle = items.reduce((accumulator, currentValue) => {
    if (currentValue.tooltip.length === 0) {
      accumulator.push('line');
      return accumulator;
    }
    else {
      accumulator.push('circle');
      return accumulator;
    }
  }, []);

  const preFilter = items.map((item, ...rest) => normalize(item, ...rest, start, latest));
  const normalized = FILTER_DEFAULT_PERIODS(preFilter, shownPeriod,"date")

  const extraOptions = {
    animation: {
      duration: 100
    },
    tooltips: {
      enabled: false,
      displayColors: false,
      backgroundColor: 'rgba(255, 255, 255, 1)',
      titleFontFamily: "'Poppins'",
      titleFontSize: 16,
      titleFontStyle: 'bold',
      titleFontColor: '#181827',
      bodyFontColor: '#696974',
      bodyFontSize: 14,
      titleMarginBottom: 10,
      mode:'x',
      intersect: false,
      xPadding: 18,
      yPadding: 14,
      borderWidth: 1,
      borderColor: 'rgba(68, 68, 79, 0.1)',
      cornerRadius: 15,
      custom: function (tooltipModel) {
        const position = this._chart.canvas.getBoundingClientRect();
        if (tooltipModel.opacity === 0) {
          handleTooltip(false);
          return;
        }
        setTop(position.top + window.pageYOffset + tooltipModel.caretY - tooltipRef.current.clientHeight - 20);
        if (window.innerWidth > position.left + window.pageXOffset + tooltipModel.caretX - 150 + tooltipRef.current.clientWidth) {
          setLeft(position.left + window.pageXOffset + tooltipModel.caretX - 130);
        }
        else {
          const add_width = position.left + window.pageXOffset + tooltipModel.caretX - 150 + tooltipRef.current.clientWidth - window.innerWidth + 20;
          setLeft(position.left + window.pageXOffset + tooltipModel.caretX - 130 - add_width);
        }
        setArrowTop(position.top + window.pageYOffset + tooltipModel.caretY - 20)
        setArrowLeft(position.left + window.pageXOffset + tooltipModel.caretX)
      },
      callbacks: {
        title: function (tooltipItem) {
          const { index } = tooltipItem[0];
          const weight = normalized[index].weight;
          setTooltipData(
              normalized[index].tooltip[normalized[index].tooltip.length - 1] !== undefined
                  ? normalized[index].tooltip[normalized[index].tooltip.length - 1]
                  : []
          );
          updateCheckInInfo(
              normalized[index].tooltip[0] === undefined
                  ? {}
                  : normalized[index].tooltip[normalized[index].tooltip.length - 1]
          )

          if (normalized[index].tooltip.length !== 0) {
            handleTooltip(true);
          }
          else {
            handleTooltip(false);
          }
        }
      },
    },
    scales: {
      xAxes: [{
        id: 'axis-x-1',
        stacked: true,
        position: 'bottom',
        gridLines: {
          zeroLineWidth: 1,
          lineWidth: 1,
          drawTicks: false,
        },
        ticks: {
          fontSize: 12,
          fontColor: '#92929d',
          padding: 15,
          callback: function (value, index, values) {
            return value;
          }
        },
      }],
      yAxes: [{
        position: 'left',
        id: 'axis-y-1',
        stacked: true,
        gridLines: {
          color: '#F2F2F6',
          zeroLineWidth: 0,
          zeroLineColor: 'white',
          drawTicks: false,
          borderDashOffset: 10,
          lineWidth: 0,
        },
        ticks: {
          min: minWeight,
          stepSize: 5,
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
          max: maxWeight
        },
      },
        {
        position: 'right',
        id: 'axis-y-2',
        display: normalized.filter(item => item.kcal !== null ).length > 0,
        stacked: true,
        gridLines: {
          color: '#F2F2F6',
          zeroLineWidth: 0,
          zeroLineColor: 'white',
          drawTicks: false,
          borderDashOffset: 10,
          lineWidth: 0,
        },
        ticks: {
          display: false,
          min: minKcal,
          max: maxKcal+1000,
          stepSize: 200,
          fontSize: 12,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
    },
    bezierCurve: true,
    maintainAspectRatio: false,
    annotation: {
      events: ['click'],
      annotations: [
        {
          drawTime: "afterDatasetsDraw",
          id: "hline",
          type: "line",
          mode: "horizontal",
          scaleID: "axis-y-1",
          value: clientGoalWeight,
          borderColor: "grey",
          borderDash: [5, 5],
          borderWidth: 1,
          onClick: function (e) {
            // The annotation is is bound to the `this` variable
          }
        }
      ]
    }
  };

  const weightColor = 'rgba(0, 132, 255,1)';
  const kcalColor = 'rgba(255,151,74,0.48)';

  const data = {
    labels: normalized.map(({date}) => moment(date).format("MMM DD YY")),
    datasets: [
      {
        type: 'line',
        label: LABELS.EXERCISE,
        spanGaps: true,
        data: normalized.map(({weight}) => weight),
        lineTension: 0.5,
        backgroundColor: 'rgba(0, 132, 255, 0)',
        borderColor: weightColor,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: '#fff',
        pointBackgroundColor: weightColor,
        pointBorderWidth: pointBorder,
        pointHoverRadius: pointHover,
        pointHoverBackgroundColor: weightColor,
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 2,
        pointRadius: point,
        pointHitRadius: 15,
        pointStyle: pointStyle,
        yAxisID: 'axis-y-1',
        xAxisID: 'axis-x-1'
      },
      {
        type: 'line',
        spanGaps: true,
        steppedLine: true,
        label: LABELS.KCALS,
        data: normalized.map(({kcal}) => kcal),
        lineTension: 0.5,
        hidden: !showKcal,
        backgroundColor: 'rgba(0, 132, 255, 0)',
        borderColor: kcalColor,
        borderCapStyle: 'butt',
        borderDash: [],
        borderDashOffset: 0.0,
        borderJoinStyle: 'miter',
        pointBorderColor: '#fff',
        pointBackgroundColor: kcalColor,
        pointBorderWidth: pointBorder,
        pointHoverRadius: 0,
        pointHoverBackgroundColor: kcalColor,
        pointHoverBorderColor: 'rgba(220,220,220,1)',
        pointHoverBorderWidth: 0,
        pointRadius: 0,
        pointHitRadius: 0,
        pointStyle: pointStyle,
        yAxisID: 'axis-y-2',
        xAxisID: 'axis-x-1'
      }
    ],
  };

  return (
    <Card className='progress-exercises-graph' style={{ overflow: "visible" }}>
      <PowerHeader
        title={'Progress'}
      />
      <div className="parent-indicator">
        <div className="progress-indicators">
          <span onClick={() => setShowKcal(!showKcal)} className={"noSelect indicatorBtn"} style={{cursor:"pointer"}}>
            <div className={'indicatorColor'} style={{background: showKcal ? kcalColor : "rgba(241,241,241,0.86)"}}/>
            kcal/day
          </span>
          <span style={{marginRight:"4%"}} className={'indicatorBtn'}>
            <div className={'indicatorColor'} style={{background:weightColor}}/>
            kg/lbs
          </span>
          <ZFToggle
              value={shownPeriod}
              options={PERIOD_GRAPH_TOGGLE}
              onClick={setShownPeriod}
          />
        </div>
      </div>
      <Fragment>
        {loading ? (
          <div style={{ height: '350px', width: `${chartWidth - 70}` }}>
            <SectionLoading show={true} />
          </div>
        ) : (
          <div ref={el => { bodyRef.current = el }}>
            {normalized.length ? (
              <div onMouseLeave={() => { handleTooltip(false) }} style={{ padding: '10px' }}>
                {
                  chartWidth !== 0
                    ? <LineChart data={data} options={extraOptions} height={350} width={chartWidth - 70} />
                    : null
                }
                {isTooltipShow && (
                  <div
                    className="cleint-process-tooltip-content"
                    style={{ top: top, left: left }}
                    onMouseEnter={() => { handleTooltip(false) }}
                    onMouseLeave={() => { handleTooltip(true) }}
                    ref={tooltipRef}
                  >
                    <ToolTip
                      showKcal={showKcal}
                      tooltipData={tooltipData}
                      measuringSystem={measuringSystem}
                    />
                    <div className="cleint-process-arrow-tooltip" style={{ top: arrowTop, left: arrowLeft }}/>
                  </div>
                )}
              </div>
            ) : (
              <div className="client-checkin-content">
                <p>No data for this period.</p>
              </div>
            )}
          </div>
        )}
      </Fragment>
    </Card>
  );
}

function mapStateToProps({ clients, progress }) {
  return {
    isTooltipShow: clients.isTooltipShow,
    clientKcals: clients.selectedClientKcals,
    clientProgress: progress.clientProgress,
    loading: progress.progressLoading
  }
}

export default connect(mapStateToProps, { ...clients, ...progress })(ClientProgression);
