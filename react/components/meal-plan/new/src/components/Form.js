// eslint-disable-next-line
import React from 'react';
import styled, { css, keyframes } from 'styled-components';
import { Badge } from './UI';

const feedbackTypes = {
  default: {
    color: '#32325d',
  },
  warning: {
    color: '#8a6d3b',
  },
  info: {
    color: '#3a97d4',
    background: '#f7fafc',
  },
  valid: {
    color: '#3c763d',
  },
  invalid: {
    color: '#d14',
  },
};

const input = css`
  display: block;
  background-color: #fff;
  box-shadow: 0 0 0 1px #e6ebf1;
  border: none;
  outline: none;
  width: 100%;
  border-radius: 2px;
  color: #32325d;
  font-family: Camphor,Open Sans,Segoe UI,sans-serif;
  font-weight: 400;
  font-size: 14px;
  line-height: 1.5;
  height: calc(1.5em + .75rem + 2px);
  padding: .375rem .75rem;
  transition: background-color .1s ease-in,color .1s ease-in;
`;

export const Form = styled.form``;

export const FormGroup = styled.div`
  box-sizing: border-box;
  margin-bottom: 1rem;
`;

export const FormLabel = styled.label`
  -webkit-tap-highlight-color: transparent;
  font-size: 14px;
  font-weight: 500;
  letter-spacing: normal;
  text-transform: none;
  color: #424770;
  display: inline-block;
  margin-bottom: .375rem;

  ${props => props.required && css`
    ::before {
      content: '*';
      color: #d14;
      display: inline-block;
      margin-right: 4px;  
    }
  `}

  ${Badge} {
    margin-left: 6px;
  }
`;

export const Input = styled.input`
  ${input}
  min-height: 38px;
`;

export const Select = styled.select`
  ${input}
  word-wrap: normal;
`;

export const Feedback = styled.div`
  font-size: 12px;
  margin-top: .25rem;
  color: ${props => feedbackTypes[props.type || 'default'].color}
`;

export const SwitchElement = ({ className, on, off, label, single, ...props }) =>  (
  <label className={className}>
    <input type="checkbox" {...props}/>
    <span>
      {!single && (
        <strong data-title={off} data-type="off"/>
      )}
      <em/>
      <strong data-title={single ? label : on} data-type="on"/>
    </span>
  </label>
);

SwitchElement.defaultProps = {
  single: false,
};

export const Switch = styled(SwitchElement)`
  height: 24px;
  display: block;
  position: relative;
  cursor: pointer;
  
  ${props => !props.nm && css`
    margin: 24px auto;
  `}

  input {
    display: none;
  }

  span {
    align-items: center;
    display: flex;
    min-height: 24px;
    line-height: 24px;
    color: #8898aa;
    transition: color .3s ease;
  }

  em,
  em::after {
    display: block;
    border-radius: 12px;
  }

  em {
    background: #6ed69a;
    width: 42px;
    height: 24px;
    position: relative;
    margin: 0 8px;

    ::after {
      background: #fff;
      content: '';
      width: 18px;
      height: 18px;
      position: absolute;
      top: 3px;
      left: 3px;
      box-shadow: 0 1px 3px rgba(18, 22, 33, .1);
      transition: all .45s ease;
    }
  }

  strong {
    font-weight: normal;
    position: relative;
    display: block;
    top: 1px;
    white-space: nowrap;

    ::after,
    ::before {
      content: attr(data-title);
      font-size: 14px;
      font-weight: 500;
      display: block;
      -webkit-backface-visibility: hidden;
    }

    ::before {
      transition: all .3s ease .2s;
    }

    ::after {
      opacity: 0;
      visibility: hidden;
      position: absolute;
      left: 0;
      top: 0;
      color: #32325d;
      transition: all .3s ease;
      transform: translate(2px, 0);
    }
  }

  input:checked + span em::after {
    background: #fff;
    transform: translate(18px, 0);
  }

  input:not(:checked) + span strong[data-type="off"]::before,
  input:checked + span strong[data-type="on"]::before {
    opacity: 0;
    visibility: hidden;
    transition: all .3s ease;
  }

  input:not(:checked) + span strong[data-type="off"]::before {
    transform: translate(2px, 0);
  }

  input:checked + span strong[data-type="on"]::before {
    transform: translate(-2px, 0);
  }

  input:not(:checked) + span strong[data-type="off"]::after,
  input:checked + span strong[data-type="on"]::after {
    opacity: 1;
    visibility: visible;
    transform: translate(0, 0);
    transition: all .3s ease .2s;
  }

  ${props => props.single && css`
    em {
      background: #e4ecfa;
      margin-left: 0;
    }

    input:checked + span em {
      background: #6ed69a;
    }
  `}
`;

export const CheckElement = ({ className, label, ...props }) =>  (
  <label className={className}>
    <input {...props}/>
    <em>
      <svg width="12px" height="10px" viewBox="0 0 12 10">
        <polyline points="1.5 6 4.5 9 10.5 1"/>
      </svg>
    </em>
    <span>{label}</span>
  </label>
);

const waveAnimation = keyframes`
  50% {
    transform: scale(.9);
  }
`;

export const Checkbox = styled(CheckElement)`
  display: inline-block;
  -webkit-user-select: none;
  margin: auto;
  user-select: none;
  cursor: pointer;
  margin-right: 16px;

  input {
    display: none;
  }

  em,
  span {
    display: inline-block;
    vertical-align: middle;
    transform: translate3d(0,0,0);
  }

  em {
    background-color: #f6f9fc;
    position: relative;
    width: 18px;
    height: 18px;
    border-radius: 2px;
    transform: scale(1);
    vertical-align: middle;
    border: 1px solid #e6ebf1;
    transition: all .2s ease;
  }

  span {
    padding-left: 8px;
  }

  svg {
    position: absolute;
    top: 3px;
    left: 2px;
    fill: none;
    stroke: #FFFFFF;
    stroke-width: 2;
    stroke-linecap: round;
    stroke-linejoin: round;
    stroke-dasharray: 16px;
    stroke-dashoffset: 16px;
    transition: all .3s ease;
    transition-delay: .1s;
    transform: translate3d(0,0,0);
  }

  :hover {
    em {
      background-color: #e6ebf1;
    }
  }

  input:checked + em {
    background: #32325d;
    border-color: #32325d;
    animation: ${waveAnimation} .4s ease;

    svg {
      stroke-dashoffset: 0;
    }
  }
`;

Checkbox.defaultProps = {
  type: 'checkbox',
};

export const Radiobox = styled(Checkbox)`
  em {
    border-radius: 50%;
    width: 20px;
    height: 20px;
  }

  svg {
    top: 4px;
    left: 3px;
  }
`;

Radiobox.defaultProps = {
  type: 'radio',
};