import React, {Fragment, useState} from "react";
import {ZFLogo} from "../Svgs";
import {ZFPoweredByStyled} from "./ZFPoweredByStyled";

const ZFPoweredBy = () => {
    return(
        <ZFPoweredByStyled>
            <span>Powered by</span>
            <ZFLogo height={20} width={60}/>
        </ZFPoweredByStyled>
    )
}
export default ZFPoweredBy
