import React from "react";
import styled from 'styled-components';
import {ERROR_COLOR} from "../Theme/_color";
import {GLOBAL_FONT_FAMILY} from "../Theme/_global";

export const ZFErrorStyled = styled.div`
  display: flex;
  justify-content: center;
  span{
    font-size: 1rem;
    color: ${ERROR_COLOR};
    margin-left: 14px;
    margin-right: 14px;
    margin-top: 3px;
    text-align: center;
    font-family: ${GLOBAL_FONT_FAMILY}, "Helvetica", "Arial", sans-serif;
    font-weight: 500;
    line-height: 1.66;
    letter-spacing: 0.03333em;
  }
`;
