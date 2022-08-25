import React, {Fragment} from "react";
import {ZFSelectStyled} from "./ZFSelectStyled";
import Select from 'react-select'
import {prepareOptions} from "../../helper/validators";
import PropTypes from "prop-types";

const ZFSelect = (props) => {

    const {
        label,
        options,
        name,
        onChange,
        value,
        disabled,
        initialText,
        placeholder,
        error
    } = props
    /*
    * Options = {
    *   value: label
    * }
    *
    * */
    const [open, setOpen] = React.useState(false);

    return (
        <ZFSelectStyled className={`${open ? 'focused' : ''} ${error ? 'error' : ''}  ${disabled ? 'disabled' : ''}`}>
            {
                options ?
                    (
                        <Fragment>
                            <Select
                                disabled={disabled}
                                name={name}
                                onFocus={() => setOpen(true)}
                                onBlur={() => setOpen(false)}
                                onChange={e => onChange(e.value)}
                                classNamePrefix={'zfSelect'}
                                options={prepareOptions(options)}
                                value={prepareOptions(options).find(option => option.value == value)}
                                menuPlacement={'auto'}
                                isSearchable={false}
                                placeholder={placeholder || initialText}
                            />
                            {
                                <span className={`zfSelect__label ${value ? 'active' : ''}`}>
                                    {label}
                                </span>
                            }
                            {error && <span className={"errorTxt"}>{error}</span>}
                        </Fragment>
                    ) : (
                        <span>no options provided</span>
                    )
            }

        </ZFSelectStyled>
    )
}

ZFSelect.propTypes = {
    options: PropTypes.object.isRequired,
    name: PropTypes.string || PropTypes.number,
    active: PropTypes.bool,
    onChange: PropTypes.func,
    value: PropTypes.oneOfType([PropTypes.string, PropTypes.number]),
    disabled: PropTypes.bool,
    initialText: PropTypes.string,
    error: PropTypes.string
}


export default ZFSelect;
