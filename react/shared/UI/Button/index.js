import React from "react";
import {ZFButtonStyled} from "./ZFButtonStyled";
const ZFButton = ({size, style = {}, children, onClick, color, disabled }) => {
    return (
        <ZFButtonStyled
            className={`zf-button ${color || ''} ${size || ''} ${disabled ? 'disabled' : ''} `}
            style={style}
            disabled={disabled}
            onClick={onClick}
            color={color}
        >
            {children}
        </ZFButtonStyled>
    )
}

export default ZFButton;
