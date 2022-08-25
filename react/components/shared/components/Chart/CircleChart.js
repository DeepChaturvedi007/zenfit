import React, { useEffect, useRef } from 'react';

const CircleChart = (props) => {
  const SELECTOR = `circle-chart-${Math.round(Math.random() * 10000)}`;

  const {
    prefixText,
    progressText,
    suffixText,
    progress,
    viewBox = '0 0 400 250'
  } = props;
  const svgRef = useRef(null);

  useEffect(() => {
    drawChart();
  }, [window.$zf, progressText, prefixText, suffixText]);

  const drawChart = () => {
    if(!window.$zf || !window.$zf.ProgressCircle) {
      return;
    }
    // Cleanup before drawing
    while(svgRef.current && svgRef.current.lastChild) {
      svgRef.current.removeChild(svgRef.current.lastChild);
    }
    window.$zf.ProgressCircle(
      `.${SELECTOR}`,
      progress,
      {
        progressText,
        prefixText,
        suffixText
      }
    );
  };

  return <svg ref={svgRef} viewBox={viewBox} className={`${SELECTOR}`} />;
};

export default CircleChart;
