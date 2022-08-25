// eslint-disable-next-line
import React from 'react';
import styled, { css } from 'styled-components';
import { HorizontalDivider, Link } from './UI';

export const PopupMenu = styled.div`
  padding: 10px 5px;
  position: absolute;
  top: calc(100% + 4px);
  z-index: 1000;
  font-size: 15px;
  line-height: 26px;
  background: #fff;
  border-radius: 4px;
  box-shadow: 0 50px 100px -20px rgba(50,50,93,.25), 0 30px 60px -30px rgba(0,0,0,.3), 0 -18px 60px -10px rgba(0,0,0,.025);
  pointer-events: ${props => props.opened ? 'auto' : 'none'};
  transform: ${props => props.opened ? 'none' : 'rotate3d(1, 1, 0, -10deg)'};
  transform-origin: 100% 0;
  opacity: ${props => props.opened ? 1 : 0};
  will-change: transform, opacity;
  transition-property: transform, opacity;
  transition-duration: .25s;
  
  ${props => props.position === 'left' ? css`
    left: -5px;
  ` : css`
    right: -5px;
  `}

  ::before {
    // content: "";
    position: absolute;
    top: -6px;
    right: 16px;
    width: 16px;
    height: 16px;
    transform: rotate(45deg);
    border-radius: 3px 0 16px 0;
    background: #fff;
  }
`;

export const PopupOptions = styled.ul`
  white-space: nowrap;
  flex-basis: 50%;
  flex-shrink: 0;
  justify-content: space-between;
  padding: 0;
  margin: 0;
  list-style: none;

  li {
    width: 100%;
  }

  ${Link} {
    border-radius: 4px;
    display: block;
    line-height: 36px;
    padding: 0 15px;
    transition: color .1s, background-color .1s;
    font-size: 14px;
    text-decoration: none;

    :hover {
      background-color: #f6f9fc;
    }

    :active {
      background-color: #e6ebf1;
    }
  }

  ${HorizontalDivider } {
    margin: 6px 0;
    opacity: .1;
  }
`;

export const PopupOption = styled.li`
  color: ${props => props.selected ? '#32325d' : '#525f7f'};
  font-weight: ${props => props.selected ? '600' : 'normal'};
  width: 100%;
`;

export const PopupBox = styled.div`
  display: inline-block;
  position: relative;
  perspective: 2000px;
  z-index: 100;
`;