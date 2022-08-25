import React from "react";
import styled from 'styled-components';

export const ZFSignupStyled = styled.div`
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  overflow-y: scroll;
  width: 100%;
  flex-direction: column;
  color: black;
  background-color: white;
  padding-bottom: 1rem;

  .foot-logo {
    position: absolute;
    bottom: 37px;
    left: calc(50% - 56.5px);
    display: flex;
    align-items: center;

    img:first-child {
      margin-right: 12px;
    }
  }

  .first-page,
  .second-page,
  .fourth-page {
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
  }

  .circle-arrow {
    cursor: pointer;
    width: 100px;
    height: 100px;
    margin: 50px auto 0;
    border: solid 1px #2c2c2c;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;

    .MuiSvgIcon-root {
      font-size: 25px;
      line-height: 100px;
    }
  }

  .first-page {
    text-align: center;

    h1 {
      font-weight: bold;
      font-size: 40px;
      margin: 0 0 10px;
    }

    h2 {
      margin: 0;
      font-size: 24px;
    }
  }

  .second-page {
    max-width: 480px;
    width: 480px;
    padding: 0 20px;
    margin: 10px;

    h1 {
      text-align: center;
      font-weight: bold;
      font-size: 37px;
      margin: 0;
    }

    h2 {
      text-align: center;
      margin: 10px 0 30px;
      font-size: 16px;
    }
  }

  .fourth-page {
    width: 100%;
    height: 100%;
    background-size: cover;
    display: flex;
    align-items: center;
    padding-left: 120px;

    &-content {
      width: 600px;
      height: 600px;
      border-radius: 20px;
      -webkit-backdrop-filter: blur(50px);
      backdrop-filter: blur(50px);
      box-shadow: 10px 20px 50px 0 rgba(0, 0, 0, 0.1);
      background-color: rgba(255, 255, 255, 0.5);
      padding-top: 154px;
      text-align: center;

      h1 {
        font-weight: bold;
        font-size: 34px;
        margin: 0 0 10px;
      }

      h2 {
        margin: 0;
        font-size: 24px;
      }
    }

    @media (max-width: 720px) {
      justify-content: center;
      width: 100%;
      padding: 10px;
    }
  }

  .terms {
    display: flex;
    justify-content: center;
  }
`;
