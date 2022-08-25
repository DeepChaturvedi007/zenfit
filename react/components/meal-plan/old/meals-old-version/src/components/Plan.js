// eslint-disable-next-line
import React from 'react';
import styled from 'styled-components';
import media from '../utils/media';
import {Flex, IconBox} from './UI';

export const PlanInfo = styled.div`
  display: flex;
  align-items: center;
  border-bottom: 1px solid #e6ebf1;
  flex-wrap: wrap;
  position: relative;
  z-index: 20;

  ${media.desktop`
    flex-wrap: nowrap;
  `}
`;

export const PlanInfoItem = styled.div`
  font-size: 13px;
  padding: 8px 16px;
  position: relative;
  text-align: left;
  flex: ${props => props.fill ? '1 1 100%' : '0 0 auto'};
  width: ${props => props.fill ? '100%' : '50%'}

  & + &::before {
    content: "";
    position: absolute;
    top: 50%;
    left: 0;
    width: 1px;
    height: 24px;
    background-color: #e6ebf1;
    transform: translateY(-50%);
  }

  ${IconBox} {
    margin-right: 4px;
  }

  ${media.desktop`
    padding: 16px;
    width: auto;
    flex: ${props => props.fill ?  '1 1 auto' : '0 0 auto'};
  `}
  
  ${Flex} {
    > * + * {
      margin-left: 6px; 
    }
  }
`;
