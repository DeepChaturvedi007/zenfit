// eslint-disable-next-line
import React from 'react';
import styled, { css, keyframes } from 'styled-components';
import { PopupBox } from './Popup';
import {Link} from './UI';

const activityTypes = {
  default: '#8898aa',
  rejected: '#d14',
  resolved: '#6ed69a',
  pending: '#2895f1',
};

const pulseFrames = keyframes`
  from {
    opacity: 1;
  }
  to {
    opacity: .5;
  }
`;

const rotateFrames = keyframes`
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
`;

export const Card = styled.div`
  background: white;
  margin-bottom: 30px;
  border-radius: 4px;
  box-shadow: 0 0 0 1px rgba(50,50,93,.05), 0 2px 5px 0 rgba(50,50,93,.1), 0 1px 1px 0 rgba(0,0,0,.07);
`;

export const CardHeader = styled.div`
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  border-bottom: 1px solid #e6ebf1;
  padding: 9px 16px;
  position: relative;
  z-index: 30;

  button + ${styled.button},
  button + ${PopupBox},
  ${PopupBox} + button {
    margin-left: 6px;
  }
`;
export const CardTitle = styled.h4`
  display: flex;
  font-weight: 500;
  margin: 0;
  
  ${Link} {
    font-weight: 400;
    margin-left: 6px;
  }
`;

export const CardBody = styled.div`
  padding: 16px;
  position: relative;
  z-index: 1;
`;

export const CardActivityIcon = styled.div`
  line-height: 1;
  margin-right: 4px;
  width: 16px;
  height: 16px;

  svg {
    width: 16px;
    height: 16px;
    fill: currentColor;
  }
`;

export const CardActivityText = styled.span`
  display: inline-block;
  font-size: 14px;
  line-height: 16px;
`;

export const CardActivity = styled.div`
  color: ${props => activityTypes[props.type] || activityTypes.default};
  display: flex;
  padding: 0 8px;
  align-items: flex-start;

  ${props => props.loading && css`
    ${CardActivityText} {
      animation: ${pulseFrames} 1s ease-in infinite alternate;
    }

    ${CardActivityIcon} {
      svg {
        animation: ${rotateFrames} 1.3s linear infinite;
      }
    }
  `}
`;
