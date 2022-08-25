import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR, INACTIVE_COLOR} from "../Theme/_color";
import {DEVICE} from "../../helper/devices";

export const ZFProgressStyled = styled.div`
  padding: 1.5rem 2rem;
  background: #fcfcfc;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  width: 100%;
  svg{
    cursor: pointer;
    width: 22px;
    height: 22px;
  }
  .zf-progress{
    width: 93%;
    align-items: center;
    display: flex;
    span{
      padding: 10px 1px;
      &:first-child{
        border-radius: 2px 0 0 2px;
      }
      &:last-child{
        border-radius: 0 2px 2px 0;
      }
      &.span_active{
        cursor: pointer;
        &:hover > .zf-progressItem.active{
          background-color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
          opacity: 0.5;
        }
      }
    }
    .zf-progressItem{
      background: ${INACTIVE_COLOR};
      height: 8px;
      display: flex;
      flex-direction: row;
      &.active{
        background-color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
        &:hover{
          background-color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
        }
      }
      
    }
  }
  
  .counts{
    font-size: 16px;
    color: #92929d;
  }

   ${DEVICE.mobile} {
    padding: 1rem;
    .zf-progress{
      width: 80%;
    }
   .counts{
      min-width: 28px;
   }
  }
  
`;
