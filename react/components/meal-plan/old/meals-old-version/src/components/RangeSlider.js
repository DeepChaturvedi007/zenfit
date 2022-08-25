import React, { PureComponent, createRef } from "react";
import { findDOMNode } from "react-dom";
import styled, { css } from "styled-components";
import PropTypes from "prop-types";

function handleNoop(e) {
  e.stopPropagation();
  e.preventDefault();
}

function maxmin(pos, min, max) {
  if (pos < min) { return min; }
  if (pos > max) { return max; }
  return pos;
}

const RangeSliderFill = styled.div`
  background: #2795f1;
  border-radius: 3px;
  display: block;
  position: absolute;
  height: 100%;
  top: 0;
`;

const RangeSliderHandle = styled.div`
  background-color: #fff;
  border: 1px solid #e6ebf1;
  border-radius: 3px;
  cursor: pointer;
  display: inline-block;
  position: absolute;
  box-shadow: 0 1px 3px rgba(0,0,0,.08);
  width: 42px;
  height: 18px;
  top: -6px;
  user-select: none;
  text-align: center;
  
  :active {
    box-shadow: 0 0 3px rgba(0,0,0,0.2);
    cursor: grabbing;
    cursor: -moz-grabbing;
    cursor: -webkit-grabbing;
  }
`;

const RangeSliderLabel = styled.span`
  color: #32325d;
  display: inline-block;
  padding: 2px 4px;
  vertical-align: top;
  line-height: 1;
  font-size: 12px;
  font-weight: 500;
`;

const RangeSliderContainer = styled.div`
  background: #e6ebf1;
  border-radius: 3px;
  display: block;
  min-width: 140px;
  height: 6px;
  margin: 8px 0;
  position: relative;
  
  ${props => props.disabled && css`
    ${RangeSliderFill} {
      background: transparent;
    }

    ${RangeSliderFill} {
      background-color: #f4f4f4;
      box-shadow: none;
      border-color: #E2E2E2;
    }
  `}
`;

class RangeSlider extends PureComponent {
  constructor(props) {
    super(props);

    this.slider = createRef();
    this.handle = createRef();

    this.state = {
      limit: 0,
      grab: 0,
      value: props.value,
    };
  }

  static getDerivedStateFromProps(nextProps, prevState) {
    if (nextProps.value !== prevState.value) {
      return { value: nextProps.value };
    }
    return null;
  }

  componentDidMount() {
    this.setup();
    window.addEventListener('resize', this.setup);
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.setup);
  }

  render() {
    const { labelRenderer } = this.props;
    const position = this.getPositionFromValue(this.state.value);
    const coords = this.coordinates(position);
    const fillStyle = {width: `${coords.fill}px`};
    const handleStyle = {left: `${coords.handle}px`};
    const isDisabled = this.props.max === 0 || this.props.isDisabled;
    const label = labelRenderer ? labelRenderer(this.state.value) : null;

    return (
      <RangeSliderContainer ref={this.slider} disabled={isDisabled}>
        <RangeSliderFill style={fillStyle}/>
        <RangeSliderHandle
          ref={this.handle}
          onMouseDown={isDisabled ? handleNoop : this.onKnobMouseDown}
          onTouchStart={isDisabled ? handleNoop : this.onKnobMouseDown}
          style={handleStyle}>
          {label && (
            <RangeSliderLabel>{label}</RangeSliderLabel>
          )}
        </RangeSliderHandle>
      </RangeSliderContainer>
    );
  }

  setup = () => {
    const sliderPos = findDOMNode(this.slider.current).offsetWidth;
    const handlePos = findDOMNode(this.handle.current).offsetWidth;

    this.setState({
      limit: sliderPos - handlePos,
      grab: handlePos / 2,
    });
  };

  onKnobMouseDown = () => {
    document.addEventListener('mousemove', this.onDragStart);
    document.addEventListener('touchmove', this.onDragStart);
    document.addEventListener('mouseup', this.onDragEnd);
    document.addEventListener('touchend', this.onDragEnd);
  };

  onDragStart = (e) => {
    const { onChange } = this.props;
    const value = this.position(e);

    this.setState({ value }, () => {
      if (onChange) {
        onChange(value);
      }
    });
  };

  onDragEnd = () => {
    document.removeEventListener('mousemove', this.onDragStart);
    document.removeEventListener('touchmove', this.onDragStart);
    document.removeEventListener('mouseup', this.onDragEnd);
    document.removeEventListener('touchend', this.onDragEnd);
  };

  getPositionFromValue(value) {
    const { limit } = this.state;
    const { min, max } = this.props;
    const divisor = max - min;
    const percentage = divisor !== 0 ? (value - min) / divisor : 0.5;

    return Math.round(percentage * limit);
  }

  getValueFromPosition(pos) {
    const { limit } = this.state;
    const { min, max, step } = this.props;
    const percentage = (maxmin(pos, 0, limit) / (limit || 1));

    return step * Math.round(percentage * (max - min) / step) + min;
  }

  position(e) {
    const node = findDOMNode(this.slider.current);
    const coordinate = e.touches ? e.touches[0].clientX : e.clientX;
    const direction = node.getBoundingClientRect()['left'];
    const pos = coordinate - direction - this.state.grab;

    return this.getValueFromPosition(pos);
  }

  coordinates(pos) {
    const value = this.getValueFromPosition(pos);
    const handlePos = this.getPositionFromValue(value);
    const fillPos = handlePos + this.state.grab;

    return {
      fill: fillPos,
      handle: handlePos,
    };
  }
}

RangeSlider.defaultProps = {
  min: 0,
  max: 100,
  step: 1,
  value: 0,
  isDisabled: false,
  onChange: (val) => {},
  labelRenderer: (val) => val,
};

RangeSlider.propTypes = {
  min: PropTypes.number,
  max: PropTypes.number,
  step: PropTypes.number,
  value: PropTypes.number,
  onChange: PropTypes.func.isRequired,
  className: PropTypes.string,
  isDisabled: PropTypes.bool,
  labelRenderer: PropTypes.func,
};

export default RangeSlider;
