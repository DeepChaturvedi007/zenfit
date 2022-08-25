import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR, DISABLED_COLOR, INPUT_COLORS_BORDER} from "../Theme/_color";
import {DEVICE} from "../../helper/devices";

export const ZFLangSwitchStyled = styled.div`
  position: absolute;
  bottom: 20px;
  right: 20px;
  font-size: 14px;
  -webkit-touch-callout: none; /* iOS Safari */
  -webkit-user-select: none; /* Safari */
  -moz-user-select: none; /* Old versions of Firefox */
  -ms-user-select: none; /* Internet Explorer/Edge */
  user-select: none; /* Non-prefixed version, currently
  supported by Chrome, Edge, Opera and Firefox */
  .langBtn {
    cursor: pointer;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    position: relative;
    z-index: 1;
    padding: 1.5rem 1rem;
    min-width: 180px;
    border-radius: 6px;
    border: solid 1px ${INPUT_COLORS_BORDER};
    background-color: #fff;
    color: black;
    &.active{
      border: 1px solid ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
      color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
      & svg{
        color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
      }
    }
  }
  .langList{
    position: absolute;
    bottom: 53px;
    width: 100%;
    padding: 1rem 1rem;
    background: white;
    border: 1px solid ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
    border-bottom: 0;
    border-radius: 6px 6px 0 0;
    .langItem{
      border-radius: 2px;
      cursor: pointer;
      padding: 0.25rem 15px;
      &.active{
        font-weight: 500;
        color: black;
        font-family: Poppins, serif;
      }
      &:hover{
        background-color: #f7f7f7;
        color: black;
      }
    }
  }
  .flag {
    padding-right: 10px;
    font-size: 17px;
  }
  
  svg{
    font-size: 18px;
    position: absolute;
    right: 15px;
    top: 18px;
    color:${DISABLED_COLOR};
    &:hover{
      color: white;
    }
  }
  
  
  ${DEVICE.mobile}{
    z-index: 1000;
    position: absolute;
    bottom: 20px;
    .langBtn{
      min-width: 66px;
      svg {
        right: 8px;
        top: 20px;
      }
      &.active{
        min-width: 180px;
      }
    }
    .flagName{
      display: none;
      &.active{
        display: flex;
      }
    }
  }
  
  
  
`;
