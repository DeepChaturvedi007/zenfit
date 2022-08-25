import React from "react";

const DifferenceIndicator = ({value = 0, unit = '%', style}) => {
  return (
    <small
      className={`fs-16 fw-600 ${value < 0 ? 'text-danger' : 'text-success'}`}
      style={{fontFamily: 'Poppins, sans-serif', ...style}}
    >
      &nbsp;{ value >= 0 && '+' }{value}{unit}
    </small>
  )
};

export default DifferenceIndicator;