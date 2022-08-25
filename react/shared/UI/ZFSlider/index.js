import React, { Fragment, useState } from 'react';
import {ZFSliderStyled} from "./ZFSliderStyled";
import Slider from "@material-ui/core/Slider";
import Tooltip from "@material-ui/core/Tooltip";
import {withStyles} from "@material-ui/core/styles";

const ZFSlider = ({options, value, onChange, max, min, step}) => {
    const [state,setState] = useState(0)

    const LightTooltip = withStyles((theme) => ({
        tooltip: {
            backgroundColor: 'white',
            color: 'rgba(0, 0, 0, 0.87)',
            padding:'10px 20px',
            boxShadow: '0 0 10px 0 rgba(0, 0, 0, 0.05)',
            fontSize: 11,
            margin: 0,
            marginBottom: 10
        },
        arrow: {
            color: 'white'
        },
    }))(Tooltip);

    const CustomizedToolTip = ({value}) => {
        return(
            <span>
                {options[value].label}
            </span>
        )
    }

    function ValueLabelComponent(props) {
        const { children, open, value } = props;

        return (
            <LightTooltip
                arrow
                open={open}
                enterTouchDelay={0}
                placement="top"
                title={<CustomizedToolTip value={value}/>}>
                {children}
            </LightTooltip>
        );
    }

    return (
        <ZFSliderStyled className={"zf-ZFSlider"}>
            <Slider
                value={value}
                getAriaValueText={(val) => `loose weight ${val} `}
                aria-labelledby="discrete-slider"
                ValueLabelComponent={ValueLabelComponent}
                onChange={(event,val) => onChange(val)}
                step={step}
                marks
                min={min}
                max={max}
            />
        </ZFSliderStyled>
    )
}

export default ZFSlider;


