import React from "react";
import styled from 'styled-components';
import {GLOBAL_FONT_FAMILY} from "../../../../../shared/UI/Theme/_global";
import {CTA_COLORS_BG, INPUT_COLORS_BORDER} from "../../../../../shared/UI/Theme/_color";
import {DEVICE, IS_MOBILE} from "../../../../../shared/helper/devices";

export const OnboardingStyled = styled.div`
  width: 100%;
  height: 100%;
  background-size: cover;
  background-position: top;
  position: relative;
  display: flex;
  justify-content: center;
  align-content: center;
  align-items: center;
  
  .inner{
    font-family: ${GLOBAL_FONT_FAMILY};
    max-width: 1200px;
    max-height: 700px;
    min-height: 700px;
    width: 80%;
    height: 80%;
    border-radius: 20px;
    backdrop-filter: blur(50px);
    padding: 55px 10rem;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    box-shadow: 10px 20px 50px 0 rgba(0, 0, 0, 0.2);
    h2{
      font-size: 17px;
      margin-top: 0;
      color: ${CTA_COLORS_BG};
      font-weight: 600;
      font-stretch: normal;
      font-style: normal;
      line-height: normal;
      letter-spacing: 3.19px;
    }
    h1{
      font-size: 83px;
      font-weight: bold;
      font-stretch: normal;
      font-style: normal;
      line-height: 0.9;
      letter-spacing: normal;
      span{
        color: ${CTA_COLORS_BG};
      }
    }
    .welcome, .settingUp{
      width: 50%;
    }
    .initialStep{
      margin-bottom: 10px;
    }
    .Gears{
      display: flex;
      width: 50%;
      align-self: center;
      justify-content: flex-end;
      & > div{
        margin: 0!important;
      }
    }
    
    .steps{
      margin: 2rem 0;
      .step{
        display: flex;
        flex-direction: row;
        align-content: flex-start;
        justify-content: flex-start;
        & > div {
          margin: 0 1rem!important;
        }
      }
    }
    
    .InitalStep, .steps > div{
      font-family: ${GLOBAL_FONT_FAMILY};
      font-size: 18px;
      margin: 2px 0;
      font-weight: normal;
      font-stretch: normal;
      font-style: normal;
      line-height: normal;
      letter-spacing: normal;
      color: #555;
    }
    
    .chooseVideo{
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-content: center;
      text-align: center;
      span{
        margin-bottom: 2rem;
      }
      .bigBoi{
        margin-bottom: 10px;
      }
    }
    
    .btnSection{
      margin: 20px 0 8px;
      max-width: 100%;
      display: flex;
      justify-content: center;
      button{
        min-width: 50rem;
        width: 50%;
        padding: 15px;
      }
    }
    .endStep{
      width: 100%;
      display: flex;
      justify-content: center;
      flex-direction: column;
      align-self: center;
      text-align: center;
      .nextSteps{
        display: flex;
        flex-direction: column;
        max-width: 50%;
        margin: 1rem auto;
        justify-content: flex-start;
        .nextStep{
          display: flex;
          justify-content: flex-start;
          margin: 15px 0;
          align-items: center;
          position: relative;
          font-size: 14px;
          .num{
            z-index: 10;
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 100px;
            border: 1px solid ${INPUT_COLORS_BORDER};
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(255, 255, 255, 1)
          }
          .line{
            z-index: 1;
            position: absolute;
            left: 14px;
            bottom: -30px;
            border-left: 1px solid ${INPUT_COLORS_BORDER};
            height: 45px;
          }
          .active{
            background: ${CTA_COLORS_BG};
            color: white;
            border-color: ${CTA_COLORS_BG};
          }
          .semiActive{
            border-color: ${CTA_COLORS_BG};
            color: ${CTA_COLORS_BG};
          }
        }
      }
    }
  }
  
  ${DEVICE.mobile}{
    background-size: cover;
     .inner{
       width: 100%;
       height: 100%;
       border-radius: 0;
       max-height: 100%;
       max-width: 100%;
       min-height: 100%;
       box-shadow: none;
       padding: 10px;
       backdrop-filter: none;
       background: white;
       flex-direction: column;
       flex-wrap: nowrap;
       .welcome,.Gears{
         width: 100%;
       }
       h1{
         margin-top: 0;
         font-size: 72px;
       }
       .steps > div{
         font-size: 15px;
       }
       .Gears{
         justify-content: center;
         align-self: center;
         div{
           width: 10rem!important;
           height: 10rem!important;
         }
       }
       
       .chooseVideo, .endStep{
         height: 100%;
         .zf-button{
           min-width: 35rem;
         }
       }
       
       .endStep{
         .nextSteps{
           max-width: 100%;
           margin: 0;
         } 
       }
     }
  }
  
`;
