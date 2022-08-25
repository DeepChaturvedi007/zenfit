import React, {useState} from "react";
import PersonIcon from '@material-ui/icons/Person';
import {FileDrop} from 'react-file-drop';
import {TextField} from "@material-ui/core";
import {withStyles} from '@material-ui/core/styles';
import InputAdornment from '@material-ui/core/InputAdornment';
import CheckCircleOutlineIcon from '@material-ui/icons/CheckCircleOutline';
import PhoneInput from "react-phone-input-2";
import 'react-phone-input-2/lib/material.css'
import KeyboardArrowDownIcon from "@material-ui/icons/KeyboardArrowDown";
import {determinUnit} from "../../helper/measurementHelper";
import {INPUT_COLORS_HOVER, INPUT_COLORS_BORDER, CTA_COLORS_BG} from '../Theme/_color.scss';
import {CloseRounded} from "@material-ui/icons";
import {GLOBAL_FONT_FAMILY} from "../Theme/_global";
import {ZFInputStyled} from "./ZFInputStyled";


const CssTextField = withStyles({
    root: {
        marginBottom: '10px',
        backgroundColor: '#FFFFFF',
        width: '100%',
        '& label.Mui-focused': {
            color: CTA_COLORS_BG,
            marginTop: '9px',
            fontSize: '9px',
            transform: 'translate(15px,5px) scale(1)'
        },
        '& .MuiFormLabel-filled': {
            fontSize: '9px',
            marginTop: '9px',
            transform: 'translate(15px, 5px) scale(1)'
        },
        '& label': {
            marginTop: '2px',
            fontSize: '12px',
            fontFamily: GLOBAL_FONT_FAMILY,
            fontWeight: 'normal',
            fontStretch: 'normal',
            fontStyle: 'normal',
            letterSpacing: 'normal'
        },
        '& .MuiInput-underline:after': {
            borderBottomColor: 'green',
        },
        '& .MuiFormHelperText-root': {
            fontSize: '11px'
        },
        '& .MuiOutlinedInput-root': {
            width: '100%',
            minHeight: '48px',
            background: 'white',
            '& .MuiInputBase-input': {
                fontSize: '12px',
                lineHeight: '17px',
                fontWeight: 'normal',
                fontStretch: 'normal',
                fontStyle: 'normal',
                letterSpacing: 'normal',
                padding: '25.5px 15px 12.5px',
                zIndex: 1,
            },
            '& textarea':{
                padding: '0 15px 12.5px!important',
                marginTop: '25.5px',
                zIndex: 1
            },
            '& fieldset': {
                marginTop: '5px',
                borderColor: INPUT_COLORS_BORDER,
                background: 'white',
                borderRadius: '6px',
                borderWidth: '1px',
                '& .PrivateNotchedOutline-legendLabelled-4': {
                    display: 'none'
                },
                '& legend': {
                    display: 'none'
                }
            },
            '&:hover fieldset': {
                borderColor: INPUT_COLORS_HOVER
            },
            '&.Mui-focused fieldset': {
                borderWidth: '1px',
                borderColor: CTA_COLORS_BG
            },
        },
    },
})(TextField);

const InputField = (props) => {
    const {
        name,
        type,
        label,
        helperText,
        defaultValue,
        error,
        onChange,
        unitLabel,
        initialValue,
        value,
        multiline,
        checkIcon,
        measureSystem,
        rows,
        min,
        onKeyPress
    } = props;
    const [focused, setFocused] = useState(false)

    const content = () => {
        switch (type) {
            case 'file':
                return (
                    <div className="zf-upload">
                        {
                            value == "" || value == null ? (
                                <FileDrop onDrop={(files, event) => {
                                    onChange(files[0]);
                                    event.preventDefault();
                                }}>
                                    <label className="zf-upload--content">
                                        <PersonIcon className="zf-upload--content--icon" style={{fontSize: '60px'}}/>
                                        <div>
                                            <div className="zf-upload--content--title">{label}</div>
                                            <div className="zf-upload--content--content">Drop your JPG or PNG file here
                                            </div>
                                            <input accept="image/*" id="contained-button-file" type="file"
                                                   onChange={e => onChange(e.target.files[0])}/>
                                            <span>Upload picture</span>
                                        </div>
                                    </label>
                                </FileDrop>
                            ) : (
                                <div className="zf-upload--preview">
                                    <img src={(typeof value) == "string" ? value : URL.createObjectURL(value)}
                                         className="zf-upload--image"/>
                                    <a onClick={() => onChange("")} className="zf-upload--remove">
                                        <CloseRounded/>
                                        Remove
                                    </a>
                                </div>
                            )
                        }
                    </div>
                )
            case 'phone':
                return (
                    <div className={`zf-Phone ${helperText && 'error'} `}>
                        <PhoneInput
                            country={initialValue || 'dk'}
                            name={name}
                            placeholder={""}
                            buttonClass={`${focused ? "focused" : ""}`}
                            containerClass={`${focused ? "focused" : ""} ${helperText && 'error'}`}
                            inputClass={`MuiInputBase-input zfInfput ${helperText && 'error'}`}
                            label={label}
                            defaultErrorMessage={!(helperText == "" || !helperText)}
                            value={value}
                            onChange={onChange}
                            onFocus={() => setFocused(true)}
                            onBlur={() => setFocused(false)}
                            preferredCountries={['dk', 'se', 'no', 'fi', 'gb', 'nl', 'de', 'us']}
                        />
                        <span className={`${helperText && 'error'} phone-name ${focused ? "focused" : ""}`}>{label}</span>
                        <span className={'zfseperator'}/>
                        <span className="zf-arrow">{<KeyboardArrowDownIcon/>}</span>
                        {
                            helperText && (
                                <span className={'error errorField MuiFormHelperText-root'}>
                                {helperText}
                            </span>
                            )
                        }
                    </div>
                )
            case 'number':
                return (
                    <div className="ZFNumberField">
                        <CssTextField
                            name={name}
                            label={label}
                            error={!(helperText == "" || !helperText)}
                            variant="outlined" type={type}
                            helperText={helperText}
                            value={value}
                            type={'number'}
                            onKeyDown={(e) => ( e.keyCode === 69 || e.keyCode === 190 || e.keyCode === 189 || e.keyCode === 187  ) && e.preventDefault()}
                            multiline={multiline}
                            onChange={onChange}
                            InputProps={{
                                inputProps: {
                                    min: min
                                },
                                endAdornment: checkIcon &&
                                    <InputAdornment position="end"><CheckCircleOutlineIcon/></InputAdornment>
                            }}
                        />
                        {
                            measureSystem &&
                            <span className={"unit"}>{ unitLabel ? unitLabel : determinUnit(name, measureSystem)}</span>
                        }
                    </div>
                )

            default:
                return (
                    <CssTextField
                        name={name}
                        label={label}
                        error={!(helperText == "" || !helperText)}
                        variant="outlined"
                        type={type}
                        helperText={helperText}
                        value={value}
                        rows={rows}
                        defaultValue={defaultValue}
                        multiline={multiline}
                        onChange={onChange}
                        onKeyPress={onKeyPress}
                        autoComplete='new-password'
                        InputProps={{
                            endAdornment: checkIcon &&
                                <InputAdornment position="end"><CheckCircleOutlineIcon/></InputAdornment>
                        }}
                    />
                )
        }
    }

    return (
        <ZFInputStyled>
            {content(type)}
        </ZFInputStyled>
    )
}

export default InputField;
