import React, {Fragment} from "react";
import {FormControlLabel, RadioGroup, Radio} from "@material-ui/core";
import ZFRadioStyled, {ZFRadioWrapper, ZFToggleWrapper} from "./ZFRadioStyled";
import {prepareOptions} from "../../helper/validators";

const ZFToggle = (props) => {

    const {
        options,
        value,
        alignment = '',
        inverted = false,
        onClick,
        type = '',
        size,
        outline = false,
        label = '',
        error = '',
        color,
        checkType
    } = props
    /*
     * options = {
     *   val:'label'
     * }
     */

    const handleMulti = (newEntry) => {

        let newArr =
            value.includes(newEntry)
                ? (value.filter(item => item !== newEntry))
                : (value.concat(newEntry))

        onClick(newArr)
    }

    const content = (type) => {
        switch (type) {
            case 'radio':
                return (
                    <ZFRadioWrapper size={size} outline={outline} checkType={checkType} alignment={alignment}>
                        <RadioGroup
                            row={true}
                            name="controlled-radio-buttons-group"
                            value={value}
                            onChange={(e, value) => onClick(value)}
                        >
                            {
                                (prepareOptions(options)).map((option, index) => {
                                    return (
                                        <Fragment key={index}>
                                            <FormControlLabel
                                                className={option.value === value ? 'active' : ''}
                                                value={option.value}
                                                checked={value == option.value}
                                                control={
                                                    <ZFRadioStyled
                                                        size={size}
                                                        type={type}
                                                        alignment={alignment}
                                                        zfcolor={color}
                                                    />
                                                }
                                                label={option.label}
                                                checktype={checkType}
                                            />
                                        </Fragment>
                                    )
                                })
                            }
                        </RadioGroup>
                    </ZFRadioWrapper>
                )
            case 'radioMulti':
                return (
                    <ZFRadioWrapper size={size} outline={outline} radiomulti zfcolor={color} checkType={checkType}>
                        <RadioGroup
                            row={true}
                            name="controlled-radio-buttons-group"
                        >
                            {
                                (prepareOptions(options)).map((option, index) => {
                                    return (
                                        <Fragment key={index}>
                                            <FormControlLabel
                                                className={value && value.includes(option.value) ? 'active' : ''}
                                                value={option.value}
                                                onClick={(e) => {
                                                    e.preventDefault();
                                                    handleMulti(option.value)
                                                }}
                                                checked={value && value.includes(option.value)}
                                                control={
                                                    <ZFRadioStyled
                                                        size={size}
                                                        radiomulti={1}
                                                        zfcolor={color}
                                                        checktype={checkType}
                                                    />
                                                }
                                                label={option.label}
                                            />
                                        </Fragment>
                                    )
                                })
                            }
                        </RadioGroup>
                    </ZFRadioWrapper>
                )

            default:
                return (
                    <span className={"standardToggle"}>
                        {
                            (prepareOptions(options)).map((option, index) => {
                                const isActive = (value == option.value);
                                return (
                                    <React.Fragment key={index}>
                                        <button
                                            className={`zf-toggle ${isActive ? 'Active' : ''}`}
                                            onClick={() => onClick(option.value)}
                                        >
                                            {option.label}
                                        </button>
                                    </React.Fragment>
                                )
                            })
                        }
                    </span>
                )
        }
    }

    return (
        <ZFToggleWrapper className={error ? 'error' : ''}>
            {label && <span className={'toggleLabel'}>{label}</span>}
            {
                content(type)
            }
            {error && (<span className="errorLabel">{error}</span>)}
        </ZFToggleWrapper>
    )
}
export default ZFToggle
