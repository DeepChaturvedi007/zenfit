import React from "react";
import {Radio} from "@material-ui/core";
import {CTA_COLORS_BG, ERROR_COLOR, INPUT_COLORS_BORDER, INPUT_COLORS_HOVER} from "../Theme/_color";
import styled from 'styled-components';
import {DEVICE} from "../../helper/devices";


export const ZFToggleWrapper = styled.div`
  margin-bottom: 15px;

  .toggleLabel {
    margin-top: 1rem;
    font-size: 13px;
    font-weight: 500;
    font-stretch: normal;
    font-style: normal;
    line-height: normal;
    letter-spacing: normal;
    color: #000;
  }

  .MuiIconButton-root {
    background-color: transparent !important;
  }

  &.error {
    color: ${ERROR_COLOR} !important;

    .MuiFormControlLabel-label {
      color: ${ERROR_COLOR} !important;
    }
  }

  .standardToggle {
    display: flex;
    flex-direction: row;

    .zf-toggle {
      border: 0;
      text-transform: uppercase;
      border-radius: 100px;
      font-family: Poppins, sans-serif;
      display: flex;
      margin: 0 4px;
      align-items: center;
      font-size: 12px;
      font-weight: 600;
      font-stretch: normal;
      font-style: normal;
      line-height: normal;
      letter-spacing: normal;
      text-align: center;
      transition: background-color .35s ease;
      padding: 5px 10px;
      cursor: pointer;
      background-color: ${CTA_COLORS_BG};
      color: white;

      &.Active {
        background-color: white;
        color: ${CTA_COLORS_BG};
      }
    }
  }

  .errorLabel {
    color: ${ERROR_COLOR}
    margin-left: 14px;
    margin-right: 14px;
    font-size: 0.75rem;
    text-align: left;
    font-family: "Poppins", "Arial", sans-serif;
    font-weight: 400;
    line-height: 1.66;
    letter-spacing: 0.03333em;
  }

`;


const ZFRadioStyled = (props) => {
    return (
        <Radio
            disableRipple
            checkedIcon={
                props.checktype === 'exclude'
                    ? <ExcludeCheckedIcon {...props}/>
                    : <CheckedIcon {...props} />
            }
            icon={<UnCheckedIcon {...props} />}
            {...props}
        />
    );
}
export default ZFRadioStyled

export const ZFRadioWrapper = styled.div`
  display: flex;
  width: 100%;

  ${DEVICE.mobile} {
  }

  .MuiFormGroup-row {
    width: 100%;
    justify-content: ${
            ({radiomulti, alignment}) =>
                    alignment
                            ? alignment
                            : radiomulti ? 'flex-start' : 'space-evenly'
    };
  }

  .MuiFormControlLabel-label {
    font-weight: 500;
    color: black;
    font-size: 14px;
    padding: 11px 9px 9px 8px;
  }

  label {
    border: 1px solid ${INPUT_COLORS_BORDER};
    border-width: ${({outline}) => outline ? '1px' : '0'};
    border-radius: 10px;
    margin: ${({radiomulti}) => radiomulti ? '1rem 0.5rem 0' : '1rem 0 0'};
    margin-right: ${({alignment}) => alignment === 'flex-start' ? '1rem' : ''};
    padding: 0 1rem;

    &:hover {
      color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
      border-color: ${INPUT_COLORS_HOVER};
    }

    &.active {
      border-color: ${({theme, radiomulti, zfcolor}) => radiomulti
              ? zfcolor || CTA_COLORS_BG
              : theme.primaryColor || CTA_COLORS_BG};
    }
  }

`;

const UnCheckedIcon = styled.span`
  width: ${({size}) => size !== 'small' ? '16px' : '20px'};
  height: ${({size}) => size !== 'small' ? '16px' : '20px'};
  object-fit: contain;
  border-radius: 10px;
  border: solid 2px #ddd;
  background-color: #fff;

  & .Mui-focusVisible {
    outline: 2px auto ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
    outline-offset: 2px;
  }

  input:hover ~ & {
    background-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG},
  }

,
input: disabled ~ &: {
  box-shadow: none;
  background: CTA_COLORS_BG+"55"
},
`;

const CheckedIcon = styled.span`
  position: relative;
  width: ${({size}) => size !== 'small' ? '16px' : '20px'};
  height: ${({size}) => size !== 'small' ? '16px' : '20px'};
  object-fit: contain;
  border-radius: 100%;
  border: solid 2px ${({theme}) => theme.primaryColor || CTA_COLORS_BG};
  background-color: ${({theme}) => theme.primaryColor || CTA_COLORS_BG};

  &:before {
    position: absolute;
    display: block;
    width: 6px;
    border-radius: 100%;
    height: 6px;
    background: white;
    content: '';
    left: ${({size}) => size !== 'small' ? '3px' : '5px'};
    bottom: 0;
    right: 0;
    top: ${({size}) => size !== 'small' ? '3px' : '5px'};
  }
`;

const ExcludeCheckedIcon = styled.span`
  position: relative;
  width: ${({size}) => size !== 'small' ? '16px' : '20px'};
  height: ${({size}) => size !== 'small' ? '16px' : '20px'};
  object-fit: contain;
  border-radius: 10px;
  border: solid 2px ${({zfcolor}) => zfcolor || CTA_COLORS_BG};
  background-color: ${({zfcolor}) => zfcolor || CTA_COLORS_BG};

  &:before {
    position: absolute;
    display: block;
    width: ${({size}) => size !== 'small' ? '8px' : '10px'};
    height: 2px;
    background: white;
    content: '';
    left: ${({size}) => size !== 'small' ? '2px' : '3px'};
    bottom: 0;
    right: 0;
    top: 45%;
    border-radius: 0;
  }
`;


