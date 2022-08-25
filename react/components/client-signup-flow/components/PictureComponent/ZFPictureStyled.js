import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR, INPUT_COLORS_BORDER} from "../../../../shared/UI/Theme/_color";
import {DEVICE} from "../../../../shared/helper/devices";

export const ZFPictureStyled = styled.div`
  cursor: pointer;
  border-radius: 6px;
  border: 1px dashed ${INPUT_COLORS_BORDER};
  margin: 1rem;
  text-align: center;
  width: 230px;
  height: 300px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  &:hover{
    box-shadow: 0 0 15px 0 rgba(0, 0, 0, 0.05);
  }
  &.active{
    border-color: transparent;
  }
  label{
    width: 100%;
    &.default{
      padding: 2rem;
      cursor: pointer;
      img{
        height: 160px;
        width: 100%;
        object-fit: contain;
      }
    }
  }
  
  .description{
    padding: 0 2rem 2rem ;
  }
  
  .image {
    width: 100%;
    height: 100%;
    position: relative;
    span{
      position: absolute;
      top: 20px;
      display: flex;
      right: -6px;
      transform: translate(-50%, -50%);
      color: #fff;
      background-color: rgba(0, 0, 0, 0.1);
      padding: 0.5rem;
      border-radius: 50%;
      svg{
        fill: white;
      }
    }
    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      object-position: top center;
      border-radius: 6px;
    }
    
  }
  
  h2{
    font-size: 12px;
    font-weight: 500;
    font-stretch: normal;
    font-style: normal;
    line-height: normal;
    letter-spacing: normal;
    text-align: center;
    color: #000;
    margin: 0;
  }
  
  h3{
    margin: 0;
    font-family: Poppins, sans-serif;
    font-size: 12px;
    font-weight: normal;
    font-stretch: normal;
    font-style: normal;
    line-height: normal;
    letter-spacing: normal;
    text-align: center;
    color: #b5b5be;
  }
  span {
    cursor: pointer;
    font-family: Poppins,serif;
    font-size: 10px;
    font-weight: 600;
    font-stretch: normal;
    font-style: normal;
    line-height: normal;
    letter-spacing: normal;
    text-align: center;
    text-transform: capitalize;
    color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR} ;
  }
  input{
    display: none;
  }
  
  ${DEVICE.mobile}{
    width: 100%;
    height: 400px;
  }
  
`;
