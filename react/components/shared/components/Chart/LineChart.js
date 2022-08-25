import React from 'react';
import { Line } from 'react-chartjs-2';
import 'chartjs-plugin-annotation';

const defaultOptions = {
  plugins: {
    crosshair: false,
    annotation: {
      events: ['click']
    }
  },
  legend: {
    display: false
  }
};

const EarningsHistoryGraph = ({data, options = {}, height, width}) => {
  return (
    <Line data={data}
        options={{...defaultOptions, ...options}}
        height={height}
        width={width}
    />
  );
}

export default EarningsHistoryGraph;

