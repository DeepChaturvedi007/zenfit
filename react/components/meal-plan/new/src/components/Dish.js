// eslint-disable-next-line
import React from 'react';
import styled from 'styled-components';
import media from '../utils/media';
import { IconButton, VerticalDivider } from './UI';

export const DishCaption = styled.div`
  flex: 1;
  display: block;
  position: relative;
  padding: 0 8px;

  h5 {
    margin: 0 0 2px;
  }

  span {
    color: #8898aa;
    font-size: 12px;
  }

  ${media.desktop`
    display: none;
  `}
`;

export const DishImageBox = styled.div`
  flex: 0 0 auto;
  position: relative;

  ${media.desktop`
    flex: initial;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
  `}
`;

export const DishImageOverlay = styled.div`
    width: 100%;
    height: 100%;
    background: linear-gradient(180deg, rgba(2,0,36,0) 60%, rgba(0,0,0,0.36) 100%);
    z-index: 1;
    position: absolute;
`

export const DishImage = styled.img`
  display: block;
  object-fit: cover;
  border-radius: 4px;
  width: 117px;
  height: 75px;
  min-height: 75px;
  position: relative;

  ${media.desktop`
    border-radius: 2px;
  `}
`;

export const DishActions = styled.div`
  color: #32325d;
  display: flex;
  align-items: center;
  justify-content: center;
  background: white;
  border-top: 1px solid #e6ebf1;
  transition: opacity .3s ease;
  z-index: 5;
  width: 100%;
  flex: 1 1 100%;
  margin-top: 8px;
  padding-top: 6px;
  
  div${VerticalDivider} {
    color: #e6ebf1;
  }

  button${IconButton} {
    color: #32325d;
    margin: 0 4px;
  }

  ${media.desktop`
    border: 1px solid #e6ebf1;
    border-radius: 4px;
    position: absolute;
    bottom: -5px;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    width: auto;
    flex: 0 0 auto;
    margin-top: 0;
    padding-top: 0;
    z-index: 50;
  `}
`;

export const DishPopover = styled.div`
  backface-visibility: hidden;
  position: absolute;
  left: -32px;
  right: -32px;
  top: 0;
  background-color: #32325d;
  border-radius: 4px;
  box-shadow: 0 13px 27px -5px rgba(50,50,93,.25), 0 8px 16px -8px rgba(0,0,0,.3), 0 -6px 16px -6px rgba(0,0,0,.025);
  transition-property: transform,opacity,-webkit-transform;
  transition-duration: .15s;
  transition-timing-function: ease-in-out;
  pointer-events: none;
  opacity: 0;
  transform: translateY(calc(-100% + 10px)) scale(.75);
  transform-origin: 20px calc(100% + 12px);
  color: #fff;
  padding: 8px;
  font-size: 12px;
  font-weight: 500;
  line-height: 1.5em;
  z-index: 100;
  display: ${props => props.hidden ? 'none' : 'block'};

  &::before {
    content: "";
    position: absolute;
    bottom: -2px;
    left: calc(50% - 8px);
    width: 8px;
    height: 8px;
    transform: rotate(45deg);
    border-radius: 0 0 2px 0;
    background-color: inherit;
    box-shadow: 3px 3px 5px rgba(82,95,127,.04);
  }
`;

export const Dish = styled.div`
  backface-visibility: hidden;
  display: flex;
  position: relative;
  width: 100%;
  margin-bottom: 8px;
  border-radius: 4px;
  cursor: pointer;
  padding: 8px;
  flex-wrap: wrap;
  outline: none;

  &::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background-color: #fff;
    box-shadow: 0 3px 6px -2px rgba(50,50,93,.05);
    border-radius: 4px;
    border: 1px solid #e6ebf1;
    transform-origin: center center;
    transition: all .2s;
    will-change: transform;
  }

  ${media.desktop`
    align-items: center;
    justify-content: center;
    width: 127px;
    height: 85px;
    max-width: 100%;
    padding: 0;
    margin-bottom: 16px;
    flex-wrap: initial;

    &::before {
      box-shadow: 0 3px 12px -2px rgba(50,50,93,.15);
    }

    &::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      bottom: 0;
      right: 0;
      background-color: #fff;
      border-radius: 4px;
      opacity: 0;
      transition: all .2s;
      will-change: transform, opacity;
      z-index: 5;
    }

    :hover {
      &::before {
        transform: scale(1.05);
      }

      &::after {
        opacity: .75;
        transform: scale(1.05);
      }

      ${DishActions} {
        opacity: 1;
      }

      ${DishPopover} {
        opacity: 1;
        transition-timing-function: cubic-bezier(.165,.84,.44,1);
        transform: translateY(calc(-100% - 5px));
      }
    }
  `}
`;

export const DishOverlay = styled.div`
  display: flex;
  position: absolute;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  background-color: rgba(222, 40, 30, .75);
  border-radius: 4px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: #fff;
  z-index: 200;

  span {
    display: block;
    font-size: 12px;
    font-weight: 600;
    padding: 0 4px;
    text-align: center;
  }

  svg {
    display: inline-block;
    width: 16px;
    height: 16px;
    fill: currentColor;
    margin-bottom: 4px;
  }
`;

export const DishTitle = styled.span`
  display: flex;
  position: absolute;
  bottom: 0;
  z-index:2;
  font-family: Roboto;
  font-size: 10.2px;
  font-weight: bold;
  font-stretch: normal;
  font-style: normal;
  line-height: 1.17;
  letter-spacing: 0.07px;
  color: #ffffff;
  padding: 0px 1px 3.2px 8px;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -moz-line-clamp: 2;
  -webkit-box-orient: vertical;
  -moz-box-orient: vertical;
  overflow: hidden;
`;
