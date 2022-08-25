import './styles.scss';
import React, {useState} from 'react';
import BaseSlider from 'rc-slider';

const Slider = (props) => {
  const {
    value,
    min = 1,
    max = 10,
    step = 1,
    onChange,
    className = '',
    dots
  } = props;
  const onSliderChange = (value) => {
    onChange(value)
  };

  return (
    <BaseSlider
      value={value}
      min={min}
      max={max}
      step={step}
      className={className}
      onChange={onSliderChange}
      trackStyle={{
        ...styles.trackStyle,
        backgroundColor: '#4591e6',
      }}
      railStyle={{
        ...styles.trackStyle,
        backgroundColor: '#bcc4cc',
      }}
      dotStyle={{
        ...styles.dotStyle,
      }}
      handleStyle={styles.handleStyle}
      dots={dots}
    />
  );
};

const styles = {
  trackStyle: {
    height: '8px',
    borderRadius: '4px'
  },
  handleStyle: {
    width: '22px',
    height: '22px',
    borderRadius: '50%',
    border: '1px solid rgba(122,137,153,0.4)',
    boxShadow: '0px 1px 0px rgba(122,137,153,0.3)',
    backgroundColor: '#fff',
    top: '2px'
  },
  dotStyle: {
    bottom: '-4px'
  }
};

export default Slider;