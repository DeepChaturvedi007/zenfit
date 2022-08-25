import React from 'react';
import { Bar } from 'react-chartjs-2';
import 'chartjs-plugin-annotation';

const defaultOptions = {
  plugins: {
    crosshair: false,
    annotation: {
      events: ['click', 'mouseenter', 'mouseleave', 'mouseover'],
      dblClickSpeed: 350,
    }
  },
  legend: {
    display: false
  },
};

const EarningsHistoryGraph = ({data, options = {}, height, width}) => {
  return (
    <Bar data={data}
               options={{...defaultOptions, ...options}}
               height={height}
               width={width}
    />
  );
}

export default EarningsHistoryGraph;
