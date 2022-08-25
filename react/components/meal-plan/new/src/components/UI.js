// eslint-disable-next-line
import React from "react";
import styled, { keyframes, css } from 'styled-components';
import {DishPopover} from "./Dish";

const ball = keyframes`
  from {
    transform: translateY(0) scaleY(.8);
  }
  to {
    transform: translateY(-10px);
  }
`;

const loading = keyframes`
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
`;

export const rotate = keyframes`
  100% {
    transform: rotate(360deg);
  }
`;

export const path = keyframes`
  100% {
    stroke-dashoffset: 0;
  }
`;

const linkTypes = {
  default: {
    color: '#2895f1',
    colorHover: '#32325d',
  },
  danger: {
    color: '#d14',
    colorHover: '#d14',
  },
  secondary: {
    color: '#525f7f',
    colorHover: '#32325d',
  },
};

const alertTypes = {
  warning: {
    color: '#8a6d3b',
    background: '#fcf9e9',
  },
  danger: {
    color: '#d14',
    background: 'rgba(221, 17, 68, .1)',
  },
  info: {
    color: '#fff',
    background: '#f7fafc',
  },
  success: {
    color: '#3c763d',
    background: '#f6fff5',
  },
};

const iconTypes = {
  warning: {
    color: '#8a6d3b',
  },
  danger: {
    color: '#d14',
  },
  info: {
    color: '#fff',
  },
  success: {
    color: '#6ed69a',
  },
};

const buttonTypes = {
  default: {
    background: '#fff',
    backgroundActive: '#f2f6fa',
    color: '#32325d',
  },
  primary: {
    background: '#32325d',
    backgroundActive: '#43458b',
    color: '#fff',
  },
  blue: {
    background: '#0662FF',
    backgroundActive: '#4388FF',
    color: '#fff',
  },
  danger: {
    background: '#d14',
    backgroundActive: '#c71e48',
    color: '#fff',
  },
};

export const IconBox = styled.div`
  display: inline-block;
  vertical-align: middle;
  color: ${props => props.type ? iconTypes[props.type].color : '#32325d'};
  line-height: 1;

  svg {
    fill: currentColor;
    width: ${props => `${props.size}px`};
    height: ${props => `${props.size}px`};
  }
  
  ${props => props.withTooltip && css`
    position: relative;
    
    ${DishPopover} {
      transform: translateX(calc(-50% + 4px)) translateY(calc(-100% + 10px)) scale(.75);
      min-width: 160px;
      left: 50%;
    }
  
    :hover {
      ${DishPopover} {
        opacity: 1;
        transition-timing-function: cubic-bezier(.165,.84,.44,1);
        transform: translateY(calc(-100% - 5px)) translateX(calc(-50% + 4px));
      }
    }
  `}  
`;

IconBox.defaultProps = {
  size: 16,
};

export const Badge = styled.span`
  display: inline-block;
  vertical-align: 2px;
  color: #fff;
  text-transform: uppercase;
  font-size: 10px;
  line-height: 1;
  font-weight: 700;
  background: #32325d;
  transition: background .15s;
  border-radius: 10px;
  padding: 3px 6px;
  height: auto;
  top: auto;
  box-shadow: none;
`;

export const Center = styled.div`
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 32px;
`;

export const Link = styled.a`
  color: ${props => linkTypes[props.modifier].color};
  transition: color .1s;
  text-decoration: none;
  z-index: 0;

  :hover {
    color: ${props => linkTypes[props.modifier].colorHover};
  }
`;

Link.defaultProps = {
  modifier: 'default', 
};

export const FlatButton = styled.button`
  background: ${props => props.active ? '#f2f6fa' : 'transparent'};
  border: 1px solid transparent;
  border-radius: 4px;
  display: inline-flex;
  align-items: center;
  color: #32325d;
  padding: 6px 5px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: 400;
  line-height: 1;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  touch-action: manipulation;
  cursor: pointer;
  user-select: none;
  transition: all .15s ease;
  outline: none;
  min-height: 38px;

  :disabled {
    opacity: .6;
    cursor: not-allowed;
  }

  :not([disabled]):hover {
    background: #f6f9fc;
    transition-duration: .15s;
  }

  :not([disabled]):active {
    background: #f2f6fa;
  }

  svg {
    width: ${props => props.icon && !props.small ? '24px' : '16px'};
    height: ${props => props.icon && !props.small ? '24px' : '16px'};
    
    ${props => props.loading && css`
      animation: ${rotate} 1.6s linear infinite forwards;
      
      circle {
        fill: none;
        margin: 0;
        stroke: red;
        stroke-width: 2px;
        stroke-dashoffset: calc(47.124 * 2);
        stroke-dasharray: 47.124;
        animation: ${path} 1.6s linear infinite forwards;
      }
    `}
  }

  svg + span {
    margin-left: 6px;
  }
  
  ${props => !props.icon && css`
    ${IconBox} {
      margin-right: 6px;
    }
  `}
`;

export const Button = styled(FlatButton)`
  background: ${props => buttonTypes[props.modifier].background};
  border-color: #e6ebf1;
  color: ${props => buttonTypes[props.modifier].color};

  :disabled {
    background-color: rgba(206, 217, 224, .5);
    border-color: transparent;
    color: rgba(92,112,128,.5);
    cursor: not-allowed;
    opacity: 1;
  }
  
  :not([disabled]) {
    box-shadow: 0 1px 3px rgba(0,0,0,.08);
  }
  
  :not([disabled]):focus,
  :not([disabled]):hover {
    background: ${props => buttonTypes[props.modifier].backgroundActive};
    color: ${props => buttonTypes[props.modifier].colorActive || buttonTypes[props.modifier].color };
  }
  
  ${props => props.loading && css`
    background: rgba(206, 217, 224, .15) !important;
    border-color: transparent !important;
    box-shadow: none !important;
    color: transparent !important;
    cursor: wait;
    position: relative;
    pointer-events: none;

    ::after {
      animation: ${loading} .5s infinite linear;
      border: 2px solid #8898aa;
      border-radius: 50%;
      border-right-color: transparent;
      border-top-color: transparent;
      content: "";
      display: block;
      height: 14px;
      width: 14px;
      left: 50%;
      margin-left: -7px;
      margin-top: -7px;
      position: absolute;
      top: 50%;
      z-index: 1;
    }
  `}
`;

Button.defaultProps = {
  modifier: 'default',
};

export const IconButton = styled.button`
  background: transparent;
  border: 0;
  color: currentColor;
  cursor: pointer;
  display: inline-block;
  text-align: center;
  font-family: 'Material Icons';
  font-weight: normal;
  font-style: normal;
  font-size: 14px;
  line-height: 1;
  letter-spacing: normal;
  text-transform: none;
  white-space: nowrap;
  word-wrap: normal;
  direction: ltr;
  width: 24px;
  height: 24px;
  padding: 0;
`;

export const VerticalDivider = styled.div`
  background: currentColor;
  width: 1px;
  height: 14px;
  display: inline-block;
`;

export const HorizontalDivider = styled.div`
  background: currentColor;
  width: 100%;
  height: 1px;
  display: block;
`;

export const Label = styled.span`
  display: block;
  color: #8898aa;
  font-weight: 700;
`;

export const Alert = styled.div.attrs(props => alertTypes[props.type])`
  background-color: ${props => props.background};
  border: 0;
  border-radius: 4px;
  color: ${props => props.color};
  padding: 12px;
  font-size: 12px;
  
  ${props => props.multiline && css`
    line-height: 16px;
    margin-bottom: 8px;
  `}
`;

Alert.defaultProps = {
  type: 'success',
};

export const TextAlert = styled.h5.attrs(props => alertTypes[props.type])`
  color: ${props => props.color};
  margin-top: 0;
`;

TextAlert.defaultProps = {
  type: 'success',
};

export const LoadingDot = styled.div`
  transform: translate(-50%, -50%);
  content: '';
  display: block;
  width: ${props => `${props.size}px`};
  height: ${props => `${props.size}px`};
  border-radius: 50%;
  background: #32325d;
  z-index: 2;
  margin: ${props => `${props.size}px`} 4px 0;
  animation: ${ball} .45s cubic-bezier(0, 0, 0.15, 1) alternate infinite;

  :nth-child(1) {
    animation-delay: .15s;
  }
  
  :nth-child(3) {
    animation-delay: .3s;
  }
`;

LoadingDot.defaultProps = {
  size: 4,
};

export const LoadingBox = styled.div`
  position: relative;
  display: flex;
`;

export const Flex = styled.div`
  display: flex;
`;

export const FlexFill = styled.div`
  flex: 1;
`;



