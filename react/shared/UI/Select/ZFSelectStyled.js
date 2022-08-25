import React from "react";
import styled from 'styled-components';
import {CTA_COLORS_BG, ERROR_COLOR, INPUT_COLORS_BORDER} from "../Theme/_color";

export const ZFSelectStyled = styled.div`
  font-size: 13px;
  position: relative;
  margin-bottom: 15px;
  
  &.error{
    .zfSelect__control{
      border-color: ${ERROR_COLOR}!important;
    }
    .zf__value-container,.zfSelect__label{
      color: ${ERROR_COLOR}!important;
    }
  }

  &.disabled .zfSelect__control{
    background: #eeeeee;
    cursor: not-allowed;
    pointer-events: none;
    .zfSelect__single-value{
      color: #b3b3b3;
    }
  }
  .zfSelect__control {
    position: relative;
    line-height: 15px;
    font-size: 12px;
    padding: 23.5px 14px 11.5px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 101;
    width: 100%;
    border-radius: 6px;
    border: solid 1px ${INPUT_COLORS_BORDER};
    background-color: #fff;
    color: black;
    box-shadow: none;
    -webkit-touch-callout: none; /* iOS Safari */
    -webkit-user-select: none; /* Safari */
    -khtml-user-select: none; /* Konqueror HTML */
    -moz-user-select: none; /* Old versions of Firefox */
    -ms-user-select: none; /* Internet Explorer/Edge */
    user-select: none; /* Non-prefixed version, currently
  supported by Chrome, Edge, Opera and Firefox */
    .zfSelect__value-container{
      padding: 0;
    }
    &:hover{
      border-color: #ccc;
    }
    svg{
      fill: #b5b5be;
    }
  }

  .zfSelect__indicator-separator{
    display: none;
  }
  
  .zfSelect__single-value{
    margin: 0;
    padding-bottom: 2px;
  }
  &.focused{
    border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};;
  }

  &.focused .zfSelect__label{
    color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
  }

  &.focused .zfSelect__single-value{
    color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
  }
  
  .zfSelect__placeholder{
    display: none;
  }
  
  .zfSelect__label{
    color: rgba(0, 0, 0, 0.54);
    transition: color 200ms cubic-bezier(0.0, 0, 0.2, 1) 0ms,transform 200ms cubic-bezier(0.0, 0, 0.2, 1) 0ms;
    position: absolute;
    background: transparent;
    font-weight: normal;
    font-stretch: normal;
    font-style: normal;
    letter-spacing: normal;
    z-index: 104;
    top: 17px;
    left: 15px;
    bottom: 0;
    pointer-events: none;
    font-size: 12px;
    &.active{
      top: 10px;
      left: 15px;
      font-size: 9px;
      z-index: 102;
      transition: 2ms;
    }
  }
  
  .zfSelect__dropdown-indicator{
    padding: 0;
  }
  
  .zfSelect__menu{
    position: absolute;
    width: 100%;
    padding: 1rem 1rem;
    margin: 3px 0;
    box-shadow: none;
    z-index: 201;
    border: 1px solid #eeeeee;
    border-radius: 6px;
    background: white;
    .zfSelect__menu-list{
      background: white;
      padding: 2px;
      overflow-y: scroll;
      overflow-x: hidden;
      font-size: 13px;
      &::-webkit-scrollbar {
        background: transparent;
        width: 2px;
      }
      &::-webkit-scrollbar-thumb {
        background: #868686;
        border-radius: 25px;
      }
    }

    .zfSelect__option{
      border-radius: 6px;
      background-color: transparent ;
      color: #676a6c;
      cursor: pointer;
      font-size: 12px;
      padding: 1rem 15px;
      font-weight: normal;
      font-stretch: normal;
      font-style: normal;
      letter-spacing: normal;
      &.zfSelect__option--is-selected, .zfSelect__option--is-focused{
        background: transparent;
        font-weight: 500;
        color: black;
      }
      &:hover{
        background-color: #f7f7f7;
        color: black;
      }
    }
  }
  .zfSelect__option--is-focused{
  }
 .zfSelect__dropdown-indicator{
    position: absolute;
    top: 15px;
    right: 10px;
  }
  
  

  .zfSelect__control--menu-is-open{
    border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
    &:hover{
      border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
    }
    &:focus{
      border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
    }
    svg{
      transform: rotate(180deg);
    }
  }
  
  
  .error{
    color: ${ERROR_COLOR}!important;
    border-color: ${ERROR_COLOR}!important;
  }

  .errorTxt {
    font-size: 11px;
    color: ${ERROR_COLOR};
    margin-top: 3px;
    margin-left: 14px;
    margin-right: 14px;
  }

`;
