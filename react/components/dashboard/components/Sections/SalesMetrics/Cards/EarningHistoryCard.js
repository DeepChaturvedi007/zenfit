import React from 'react';
import {connect} from "react-redux";
import _ from 'lodash'
import moment from "moment";
import Card, {
  Header,
  Body,
  Title
} from '../../../../../shared/components/Card';
import { BarChart } from "../../../../../shared/components/Chart";
import numeral from "numeral";
import {currencyCode} from "../../../../helpers";

const generateGraphData = (sourceList = []) => {
  let normalized = [];

  const sorted = _.sortBy(sourceList, 'date');
  const start = sorted[0] || {};
  const curr = moment(start.date);
  while (normalized.length < 12 && curr.unix() <= moment().endOf('month').unix()) {
    const month = curr.format('MMM');
    const graphItem = {
      month,
      total: 0
    };
    sourceList.forEach(item => {
      const iterableMonth = moment(item.date).format('MMM');
      if (iterableMonth === month) {
        graphItem.total = item.total;
      }
    });
    normalized.push(graphItem);
    curr.add(1, 'month');
  }
  return normalized
};

const EarningsHistoryCard = ({currency, chartData = []}) => {

  const normalized = generateGraphData(chartData);
  const data = {
    labels: normalized.map(({month}) => month),
    datasets: [
      {
        label: 'Earned',
        data: normalized.map(({total}) => total),
        barPercentage: 0.3,
        minBarLength: 2,
        barThickness: 7,
        maxBarThickness: 7,
        backgroundColor: normalized.map(() => '#0062ff'),
      },
    ],
  };

  const options = {
    maintainAspectRatio: false,
    scales: {
      yAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          zeroLineWidth: 0,
          drawTicks: false,
          borderDashOffset: 10,
          lineWidth: 0,
        },
        ticks: {
          // min: 0,
          fontSize: 10.1,
          fontColor: '#92929d',
          padding: 16,
          callback: function (value, index, values) {
            if(!value) return 0;
            if(numeral(value).format('0.0a').includes('.0')) {
              return `${currency} ${numeral(value).format('0a')}`
            } else {
              return `${currency} ${numeral(value).format('0.0a')}`
            }
          }
        },
      }],
      xAxes: [{
        id: 0,
        stacked: true,
        gridLines: {
          color: "#f8f8f8",
          zeroLineWidth: 0,
          lineWidth: 1,
          drawTicks: false,
          offsetGridLines: false,
          drawBorder: false
        },
        ticks: {
          fontSize: 10.1,
          fontColor: '#92929d',
          padding: 16,
        },
      }],
    },
  };

  return (
    <Card style={{maxHeight: '350px'}}>
      <Header>
        <Title>
          Revenue history
        </Title>
      </Header>
      <Body style={{paddingTop: '15px', paddingBottom: '15px'}}>
        <BarChart data={data} options={options} />
      </Body>
    </Card>
  );
};

const mapStateToProps = state => ({
  chartData: Object.values(_.get(state.stats, 'payments.revenue.chart', {})),
  currency: currencyCode(_.get(state.stats, 'payments.revenue.currency', 'usd')),
});

export default connect(mapStateToProps)(EarningsHistoryCard);
