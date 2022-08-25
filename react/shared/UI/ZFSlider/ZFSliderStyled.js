import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR} from "../Theme/_color";

export const ZFSliderStyled = styled.div`
  margin: 15px 0;
  .MuiSlider-root{
    width: 99%;
    color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR};
  }
  
  .MuiSlider-rail,.MuiSlider-track{
    height: 6px;
    border-radius: 30px;
    width: 101%;
  }

  .MuiSlider-rail,.MuiSlider-mark{
    background-color: #E5E5E5;
  }

  .MuiSlider-markActive{
    background: white;
  }

  .MuiSlider-mark:last-child{
    left: 98% !important;
  }
  .MuiSlider-mark{
    margin-top: 1px;
    width: 4px;
    height: 4px;
    border-radius: 100%;
  }

  .MuiSlider-thumb.Mui-focusVisible, .MuiSlider-thumb:hover {
    box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.05);
  }
  
  .MuiSlider-thumb{
    background: #e5e5e5;
    border: 4px solid white;
    margin-top: -5px;
    width: 16px;
    height: 16px;
    box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.05);
  }
`;
