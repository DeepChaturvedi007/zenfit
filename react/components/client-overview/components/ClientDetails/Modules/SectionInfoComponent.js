import React, { Fragment, useRef } from 'react';
import _ from 'lodash';

import Select from 'react-select';
import CreatableSelect from 'react-select/creatable';
import { MuiThemeProvider, Slider, Switch } from '@material-ui/core';
import { createMuiTheme, withStyles } from '@material-ui/core/styles';
import ToggleButton from '@material-ui/lab/ToggleButton';
import ToggleButtonGroup from '@material-ui/lab/ToggleButtonGroup';
import makeAnimated from 'react-select/animated';
import DateTime from 'react-datetime';
import KeyboardArrowDownIcon from '@material-ui/icons/KeyboardArrowDown';

import { prepareOptions, prepareMultiOptions, prepareMultiOptionsFoodPref } from '../../../helpers';

const animatedComponents = makeAnimated();

const multiSelectCustomStyles = {
    control: (base) => ({
        ...base,
        background: '#fafafb',
        borderRadius: 15,
        borderColor: '#f1f1f5',
    }),
    multiValue: (styles) => {
        return {
            ...styles,
            backgroundColor: "#000000",
        };
    },
    multiValueLabel: (styles) => ({
        ...styles,
        color: "#ffffff",
    }),
    multiValueRemove: (styles) => ({
        ...styles,
        color: "#ffffff",
        ':hover': {
            backgroundColor: "#636363",
            color: 'white',
        },
    }),

};
const switchTheme = createMuiTheme({
    props: {
        // Name of the component
        MuiSwitch: {
            disableRipple: true
        }
    }
});

const StyledToggleButtonGroup = withStyles((theme) => ({
    root: {
        backgroundColor: '#f6f7f7',
        height: '30px',
        opacity: 0.8
    },
    grouped: {
        height: '25px',
        width: '100%',
        margin: theme.spacing(1),
        border: 'none',
        color: '#2E2F30',
        backgroundColor: '#eeedf3',
        fontFamily: 'Poppins',
        fontSize: '10px',
        fontWeight: 'normal',
        fontStretch: 'normal',
        fontStyle: 'normal',
        whiteSpace: "nowrap",
        lineHeight: 'normal',
        letterSpacing: '0.1px',
        '&:not(:first-child)': {
            borderRadius: theme.shape.borderRadius,
        },
        '&:first-child': {
            borderRadius: theme.shape.borderRadius,
            marginLeft: '0px'
        },
        '&.Mui-selected': {
            color: '#0062ff',
            fontWeight: "bold",
            backgroundColor: '#DDEBFF'
        }
    },
}))(ToggleButtonGroup);

const SectionInfoComponent = (props) => {
    const {
        title,
        value,
        className,
        valueType,
        measuringSystem,
        type,
        creatable,
        valueChange,
        inputValueChange,
        selectLoading,
        name,
        optionsList,
        clientId,
        marks,
        step,
        min,
        max,
        fileType,
        allowSelectAll
    } = props;

    const [readMore, setReadMore] = React.useState(false);
    const [isOverFlowing, setIsOverFlowing] = React.useState(false);
    const [selectInputValue, setSelectInputValue] = React.useState("");

    const checkOverFlow = () => {
        const el = document.getElementById(`form-control-textarea-content-${clientId}-${name}`);
        if (el !== null) {
            let curOverFlow = el.style.overflow;
            if (!curOverFlow || curOverFlow === "visible")
                el.style.overflow = "hidden";

            (el.clientHeight < el.scrollHeight) ? setIsOverFlowing(true) : setIsOverFlowing(false);
            el.style.overflow = curOverFlow;
        }
    }

    React.useEffect(() => {
        if (type === 'textarea') {
            checkOverFlow();
        }
    }, [value]);

    const changeTextAreaValue = (e) => {
        valueChange(e.target.value, name);
    }

    const changeTagValue = (value) => {
        let tagValues;
        if (value && value.find(element => element.value === "*")) {
            tagValues = prepareMultiOptionsFoodPref(optionsList) ? prepareMultiOptionsFoodPref(optionsList).map((item) => item.value) : [];
        } else {
            tagValues = value ? value.map((item) => item.value) : [];
        }
        valueChange(tagValues, name);
    }

    const loadMore = () => {
        setReadMore((prev) => !prev);
    }

    const handleToggleButton = (event, newValue) => {
        valueChange(_.toNumber(newValue), name);
    }

    const debounceChangeSelectInputValue = _.debounce(changeSelectInputValue, 500);
    function changeSelectInputValue(inputValue) {
        if (inputValue) {
            inputValueChange(inputValue);
        }
    }

    const prepareOptionsList = (optionsList) => {
        if (allowSelectAll && allowSelectAll !== undefined && !_.isEmpty(optionsList)) {
            return [{ label: "Select All", value: "*" }, ...prepareMultiOptionsFoodPref(optionsList)];
        } else {
            return prepareMultiOptionsFoodPref(optionsList);
        }
    }

    const content = (type) => {
        switch (type) {
            case "date":
                return (
                    <div className="zenfitSelect">
                        <DateTime
                            utcOffset={0}
                            name={name}
                            timeFormat={false}
                            closeOnSelect={true}
                            dateFormat="MMM DD YYYY"
                            onChange={(e) => { valueChange(e.format('Y-MM-DD'), name) }}
                            value={moment(value).format("MMM DD YYYY")}
                            style={{ maxWidth: 100, fontSize: 12 }}
                        />
                    </div>
                );
            case "static":
                return (
                    <span>{value}</span>
                );
            case "dateVal":
                return (
                    <span style={{ fontSize: "1.5rem", fontWeight: "bold" }}>{value ? moment(value).format("MMM DD YYYY") : '-'}</span>
                );
            case "textarea":
                return (
                    <div className='form-control-textarea'>
                        <textarea
                            rows={2}
                            className={'form-control-textarea-content'}
                            disabled={false}
                            onChange={changeTextAreaValue}
                            defaultValue={value}
                        />
                    </div>
                )
            case "input":
                return (
                    <input
                        name={name}
                        type="text"
                        defaultValue={value ? value : ''}
                        className="form-control-input"
                        onChange={(e) => {
                            valueChange(e.target.value, e.target.name);
                        }}
                    />
                );
            case "file":
                return (
                    <div className={"zenfitFile"}>
                        <label htmlFor="zenfitFile" className={"zenfitBtn"}>
                            Upload file
                        </label>
                        <span className={"zenfitFileName"}>
                         {value && value.name}
                        </span>

                        <input
                            id={"zenfitFile"}
                            name={name}
                            accept={fileType}
                            type="file"
                            defaultValue={value ? value : ''}
                            className="form-control-input"
                            onChange={(e) => {
                                valueChange(e.target.files[0], e.target.name);
                            }}
                        />
                    </div>

                );
            case "number":
                return (
                    <input
                        name={name}
                        type="number"
                        value={value ? value : ''}
                        className={"form-control-input "+(className)}
                        onChange={(e) => { valueChange(e.target.value, e.target.name) }}
                    />
                );
            case "phone":
                return (
                    <input
                        name={name}
                        type="number"
                        value={value ? value : ''}
                        className={"form-control-input "+(className)}
                        onKeyDown={(event) => {
                            (event.key === "ArrowDown" || event.key === "ArrowUp") && event.preventDefault()
                        }}
                        onChange={(e) => { valueChange(e.target.value, e.target.name) }}
                    />
                );
            case "select":
                return (
                    <div className="zenfitSelect">
                        <select
                            name={name}
                            type="text"
                            value={value ? value : ''}
                            className="form-control-select"
                            onChange={(e) => { valueChange(e.target.value, e.target.name) }}
                        >
                            <option disabled value="">{`Select ${_.upperFirst(title)}`}</option>
                            {prepareOptions(optionsList).map((item, i) => {
                                return <option key={item.value} value={item.value}>{item.label}</option>
                            })}
                        </select>
                        <KeyboardArrowDownIcon />
                    </div>

                );
            case "multiSelect":
                return (
                    <Fragment>
                        {creatable && creatable !== undefined ? (
                            <CreatableSelect
                                components={animatedComponents}
                                isMulti
                                options={prepareMultiOptions(optionsList)}
                                value={value ? prepareMultiOptions(value) : []}
                                styles={multiSelectCustomStyles}
                                onChange={(value) => { changeTagValue(value) }}
                            />
                        ) : (
                            <Select
                                components={animatedComponents}
                                isMulti
                                isLoading={selectLoading !== undefined ? selectLoading : false}
                                options={prepareOptionsList(optionsList)}
                                value={value ? prepareMultiOptionsFoodPref(value, optionsList) : []}
                                components={{ DropdownIndicator: () => null, IndicatorSeparator: () => null }}
                                styles={multiSelectCustomStyles}
                                onChange={(value) => { changeTagValue(value) }}
                                filterOption={(options, filter, current_values) => { return options }}
                                onInputChange={(inputValue) => {
                                    if (inputValueChange && inputValueChange !== undefined) {
                                        debounceChangeSelectInputValue(inputValue)
                                    }
                                }}
                            />
                        )}
                    </Fragment>
                );
            case "slider":
                return (
                    <div style={{ padding: "0 10px" }}>
                        <Slider
                            value={value}
                            onChange={(e, value) => valueChange(value, name)}
                            aria-labelledby="discrete-slider"
                            valueLabelDisplay="auto"
                            marks={marks}
                            step={step}
                            min={min}
                            max={max}
                        />
                    </div>

                );
            case "toggle":
                return (
                    <MuiThemeProvider theme={switchTheme}>
                        <div className="zenfitToggle">
                            <Switch
                                checked={value}
                                onChange={(e) => { valueChange(e.target.checked, name) }}
                                name={name}
                                inputProps={{ 'aria-label': 'primary checkbox' }}
                            />
                        </div>
                    </MuiThemeProvider>
                );
            case "btnGroup":
                return (
                    <div className="btnGroup">
                        <StyledToggleButtonGroup
                            value={_.toString(value)}
                            exclusive
                            onChange={handleToggleButton}
                        >
                            {prepareOptions(optionsList).map((item, i) => (
                                <ToggleButton key={item.value} value={item.value} aria-label={item.value}>
                                    {optionsList[item.value]}
                                </ToggleButton>
                            ))}
                        </StyledToggleButtonGroup>
                    </div>
                )
            default:
                return "";
        }
    }

    return (
        <Fragment>
            {type === 'static' && value === null ? (
                <Fragment></Fragment>
            ) : (
                <Fragment>
                    <div className="section-info-wrapper">
                        <label className="section-info-title">
                            {_.startCase(title)}
                            {
                                valueType && (
                                    typeof valueType === 'object' && valueType !== null
                                        ? <span>({parseInt(measuringSystem) === 1 ? valueType[1] : valueType[2]})</span>
                                        : <span>({valueType})</span>
                                )
                            }
                        </label>
                        {content(type)}
                    </div>
                </Fragment>
            )}
        </Fragment>
    )
}

export default SectionInfoComponent;
