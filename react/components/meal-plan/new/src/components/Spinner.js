import React, { PureComponent } from 'react';
import styled, { css, keyframes } from 'styled-components';
import { clamp } from "../utils/helpers";

// see http://stackoverflow.com/a/18473154/3124288 for calculating arc path
const R = 45;
const SPINNER_TRACK = `M 50,50 m 0,-${R} a ${R},${R} 0 1 1 0,${R * 2} a ${R},${R} 0 1 1 0,-${R * 2}`;

// unitless total length of SVG path, to which stroke-dash* properties are relative.
// https://developer.mozilla.org/en-US/docs/Web/SVG/Attribute/pathLength
// this value is the result of `<path d={SPINNER_TRACK} />.getTotalLength()` and works in all browsers:
const PATH_LENGTH = 280;

const MIN_SIZE = 10;
const STROKE_WIDTH = 4;
const MIN_STROKE_WIDTH = 16;

const spinnerAnimation = keyframes`
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
`;

const SpinnerContainer = styled.div`
  display: inline-flex;
  align-items: center;
  justify-content: center;
  overflow: visible;
  vertical-align: middle;

  svg {
    display: block;
  }

  path {
    fill-opacity: 0;
  }
`;

const SpinnerAnimation = styled.div`
  ${props => props.animate && css`
    animation: ${spinnerAnimation} 500ms linear infinite;
  `}
`;

const SpinnerTrack = styled.path`
  stroke: rgba(24,32,38, .2);
`;

const SpinnerHead = styled.path`
  transform-origin: center;
  transition: stroke-dashoffset 200ms cubic-bezier(0.4, 1, 0.75, 0.9);
  stroke: rgba(24,32,38, .8);
  stroke-linecap: round;
`;
const SIZE_SMALL = 16;
const SIZE_STANDARD = 48;
const SIZE_LARGE = 96;
export class Spinner extends PureComponent {


  static defaultProps = {
    size: SIZE_SMALL,
    value: null,
  };

  componentDidUpdate(prevProps) {
    if (prevProps.value !== this.props.value) {
      // IE/Edge: re-render after changing value to force SVG update
      this.forceUpdate();
    }
  }

  render() {
    const { value } = this.props;

    const size = this.getSize();
    const strokeWidth = Math.min(MIN_STROKE_WIDTH, (STROKE_WIDTH * SIZE_LARGE) / size);
    const strokeOffset = PATH_LENGTH - PATH_LENGTH * (value === null ? .25 : clamp(value, 0, 1));

    return (
      <SpinnerContainer>
        <SpinnerAnimation animate={value === null}>
          <svg
            width={size}
            height={size}
            strokeWidth={strokeWidth.toFixed(2)}
            viewBox={Spinner.getViewBox(strokeWidth)}
          >
            <SpinnerTrack d={SPINNER_TRACK} />
            <SpinnerHead
              d={SPINNER_TRACK}
              pathLength={PATH_LENGTH}
              strokeDasharray={`${PATH_LENGTH} ${PATH_LENGTH}`}
              strokeDashoffset={strokeOffset}
            />
          </svg>
        </SpinnerAnimation>
      </SpinnerContainer>
    )
  }

  getSize() {
    return Math.max(MIN_SIZE, this.props.size);
  }

  /**
   * @param {number} strokeWidth
   * @returns {string}
   */
  static getViewBox(strokeWidth) {
    const radius = R + strokeWidth / 2;
    const viewBoxX = (50 - radius).toFixed(2);
    const viewBoxWidth = (radius * 2).toFixed(2);

    return `${viewBoxX} ${viewBoxX} ${viewBoxWidth} ${viewBoxWidth}`;
  }
}
