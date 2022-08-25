import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR, CTA_BUTTON_BG_GREY, CTA_COLORS_BG, DISABLED_COLOR} from "../Theme/_color";

export const ZFButtonStyled = styled.button`
  min-width: 140px;
  border: 0;
  border-radius: 6px;
  font-family: Poppins, sans-serif;
  display: flex;
  background-color: ${ ({color}) => color || CTA_BUTTON_BG_GREY};
  align-items: center;
  justify-content: space-around;
  font-size: 12px;
  font-weight: 600;
  font-stretch: normal;
  font-style: normal;
  line-height: normal;
  letter-spacing: normal;
  text-align: center;
  color: ${ ({color}) => color ? 'white' : 'black'};
  transition: background-color .35s ease;
  padding: 7px 10px;
  cursor: pointer;

  &.primary {
    color: #ffffff;
    background-color: ${CTA_COLORS_BG};
  }

  &.bigBoi{
    color: #ffffff;
    background: ${props => props.theme.primaryColor || CTA_COLORS_BG };
    width: 100%;
    padding: 13px;
    margin: 3rem auto;
    font-size: 14px;
    font-weight: 600;
  }

  &.grey {
    color: #ffffff;
    background-color: ${CTA_BUTTON_BG_GREY};
  }

  &.full {
    width: 100%;
  }
  
  &.disabled{
    background: ${DISABLED_COLOR};
  }

`;
