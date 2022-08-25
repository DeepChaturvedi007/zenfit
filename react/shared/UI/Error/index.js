import React from 'react';
import {ZFErrorStyled} from "./ZFErrorStyled";

const ZFError = ({msg, status}) => {
    return (
        <ZFErrorStyled>
            <span>
                { msg ? `status: ${status ? status : 'Error'} | ${msg}` : 'An error occurred, please contact support'}
            </span>
        </ZFErrorStyled>
    )
}

export default ZFError;
