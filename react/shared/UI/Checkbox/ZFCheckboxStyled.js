import React from "react";
import styled from 'styled-components';
import {ACTIVE_COLOR, CTA_COLORS_BG} from "../Theme/_color";

export const ZFCheckboxStyled = styled.div`
  display: flex;
  align-items: flex-start;

  & > label {
    margin-left: 12px;
    display: flex;
    align-items: flex-start;
    flex-direction: column;
  }

  .title {
    font-weight: 500;
    font-size: 14px;
  }
  .subtitle {
    font-weight: 400;
    font-size: 13px;
  }

  .MuiCheckbox-root {
    padding: 2px;
  }

  .MuiSvgIcon-root {
    font-size: 17px;
    border-radius: 4px;
    color: ${({theme}) => theme.primaryColor || ACTIVE_COLOR}
  }
`;
