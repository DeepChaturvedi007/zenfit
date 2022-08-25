import React from "react";
import styled from 'styled-components';
import {
    CTA_COLORS_BG,
    INPUT_COLORS_BORDER,
    INPUT_COLORS_HOVER,
    INPUT_IMG_PERSON_ICON,
    INPUT_IMG_PERSON_ICON_BG
} from "../Theme/_color";

export const ZFInputStyled = styled.div`
  width: 100%;

  label.Mui-focused {
    color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG}!important;
  }
  .Mui-focused fieldset{
    border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG}!important;
  }
  
  .zf-upload {
    background-color: #FFFFFF;
    width: 100%;
    min-height: 120px;
    border: ${INPUT_COLORS_BORDER} 1px dashed;
    margin-bottom: 15px;
    border-radius: 6px;

    &--image {
      height: 60px;
      width: 60px;
    }

    &--remove {
      margin-left: 40px;
      color: #92929D;
      display: flex;
      font-size: 14px;
      font-weight: 500;
      align-items: center;
      flex-direction: row;
      svg{
        font-size: 18px;
        margin-right: 10px;
      }
    }

    &:hover {
      border: ${INPUT_COLORS_HOVER} solid 1px;
    }

    &--preview {
      padding: 30px;
      display: flex;
      align-items: center;
    }

    &--content {
      display: flex;
      font-size: 12px;
      color: #b5b5be;
      font-weight: normal;
      cursor: pointer;
      padding: 30px;

      &--icon {
        margin-right: 30px;
        background-color: ${INPUT_IMG_PERSON_ICON_BG};
        color: ${INPUT_IMG_PERSON_ICON};
        border-radius: 50%;
      }

      &--title {
        font-size: 12px;
        color: black;
        font-weight: 500;
      }

      span {
        font-size: 10px;
        color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
      }

      input {
        display: none;
      }
    }
  }

  .MuiInputBase-multiline {
    padding: 0 0 12.5px!important;
  }

  .file-drop>.file-drop-target.file-drop-dragging-over-frame {
    border: none;
    background-color: rgba(73, 70, 70, 0.65);
    box-shadow: none;
    z-index: 50;
    color: white;
  }

  .zf-Phone{
    width: 100%;
    position: relative;
    margin-bottom: 15px;
    .react-tel-input{
      background: white;
      border: 1px solid #ebebeb;
      width: 100%;
      position: relative;
      display: flex;
      flex-direction: row-reverse;
      justify-content: space-between;
      outline: none;
      box-shadow: none;
      border-radius: 6px;

      &.focused{
        border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG}!important;
      }

      &:hover{
        border-color: ${INPUT_COLORS_HOVER};
      }
      &:active{
        border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
      }
      &:focus{
        border-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
        outline: none;
      }
      .special-label{
        display: flex;
        flex-direction: row;
        justify-content: space-between;
      }

      .flag-dropdown{
        width: 15%;
        position: relative;
        background: #FFFFFF;
        border-top: 1px solid transparent;
        border-left: 1px solid transparent;
        border-bottom: 1px solid transparent;
        border-radius: 6px 0 0 6px;
        .selected-flag{
          width: 100%;
          padding: 18.5px 10px;
        }
        &.open{
          z-index: 150;
          border-radius: 6px 0 0 6px;
          border: 1px solid ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
          border-right: 0;
          -webkit-touch-callout: none; /* iOS Safari */
          -webkit-user-select: none; /* Safari */
          -khtml-user-select: none; /* Konqueror HTML */
          -moz-user-select: none; /* Old versions of Firefox */
          -ms-user-select: none; /* Internet Explorer/Edge */
          user-select: none; /* Non-prefixed version, currently
  supported by Chrome, Edge, Opera and Firefox */
          .country-list{
            margin-top: 3px;
            box-shadow: none!important;
            border: 1px solid ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
            padding: 1rem;
            width: 766%;
            li{
              font-size: 12px;
            }
            &::-webkit-scrollbar {
              background: transparent;
              width: 2px;
            }
            &::-webkit-scrollbar-thumb {
              background: #868686;
              border-radius: 25px;
            }
          }
        }
      }
      .zfInfput{
        background: white;
        width: 85%;
        font-size: 12px;
        color: black;
        border-color: transparent;
        border-left: 0;
        line-height: 15px;
        padding: 20.5px 14px 16px 8%;
        border-width: 1px;
        border-radius: 0 6px 6px 0;
        box-shadow: none!important;
        outline: none;
        &.open{
          border-radius: 0 6px 6px 0;
          border: 1px solid ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
          border-left: 0;
          &:hover{
            border-radius: 0 6px 6px 0;
            border: 1px solid ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
            border-left: 0;
          }
          &:focus{
            border-radius: 0 6px 6px 0;
            border: 1px solid ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
            border-left: 0;
          }
        }
        &:hover{
          border-width: 1px;
          border-color: transparent;
          outline: none;
        }
        &:focus{
          border-width: 1px;
          border-color: transparent;
          outline: none;
        }
      }
      &:active{
        border-color: transparent;
      }
      &:focus{
        border-width: 1px;
        border-color: transparent;
        outline: none;
      }
      .special-label{
        display: none;
      }
    }
    .phone-name{
      position: absolute;
      z-index: 3;
      top: 8px;
      left: 20%;
      display: block;
      background: white;
      padding: 0 7px;
      white-space: nowrap;
      font-family: Poppins, sans-serif;
      font-size: 9px;
      font-weight: 500;
      font-stretch: normal;
      font-style: normal;
      line-height: normal;
      letter-spacing: normal;
      color: #b5b5be;
      &.focused{
        color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
      }
    }

    .zfseperator{
      position: absolute;
      top: 23%;
      left: 17%;
      z-index: 3;
      width: 1px;
      height: 31px;
      border-right: solid 1px #eee;
    }

    .arrow{
      display: none;
    }
    .zf-arrow{
      position: absolute;
      left: 10%;
      pointer-events: none;
      top: 20px;
      z-index: 100;
    }

    &.error{
      color: #f44336!important;
      border-color: #f44336!important;
      & input{
        color: #f44336!important;
        border-color: #f44336!important;
      }
      .flag-dropdown{
        color: #f44336!important;
        border-color: #f44336!important;
      }
      .phone-name{
        color: #f44336!important;
      }
      .country-name, .dial-code{
        color: lightgrey;
      }
    }



    .errorField{
      font-size: 11px;
      margin-left: 14px;
      margin-right: 14px;
      color: #f44336;
    }
  }
  @media (max-width: 1024px){
    .zf-Phone .react-tel-input .flag-dropdown.open .country-list{
      width: 777%;
    }
  }

  .ZFNumberField{
    position: relative;
    input[type="number"]{
      -moz-appearance: textfield;
      &::-webkit-outer-spin-button{
        -webkit-appearance: none;
        margin: 0;
      }
      &::-webkit-inner-spin-button{
        -webkit-appearance: none;
        margin: 0;
      }
    }

    .MuiFormControl-root{
      width: 100%;
    }

    .unit{
      position: absolute;
      top: 28%;
      bottom: 0;
      right: 15px;
      color: ${INPUT_COLORS_HOVER};
    }
    fieldset{
      width: 100%;
    }
  }

`;
