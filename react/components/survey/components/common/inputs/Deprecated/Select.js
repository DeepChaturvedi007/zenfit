import React, { useState, useEffect } from 'react';

const Select = (props) => {
  const id = Math.random();
  const {
    value,
    label,
    name,
    options,
    placeholder = '',
    required,
    onChange = () => null
  } = props;
  const [val, setVal] = useState(value);
  const [error, setError] = useState(null);

  const handleBlur = () => {
    if(!val && required) setError(`"${name}" is required`);
    if(!error) onChange(name, val);
  };

  const handleSelect = (val) => {
    setError(null);
    setVal(val);
  };

  useEffect(() => {
    if(value === val) return;
    setVal(value)
  }, [value]);

  return (
    <div className={`form-group ${!!error ? 'error' : ''}`}>
      { !!label && <label htmlFor={id} className={'fw-normal'}>{label}</label> }
      <select
        id={`${id}`}
        name={name}
        placeholder={placeholder}
        value={val || ''}
        className={'form-control'}
        onBlur={() => handleBlur()}
        onChange={(e) => handleSelect(e.target.value)}
        style={!val ? placeholderStyles : {}}
      >
        <option value="" disabled={required}>{placeholder}</option>
        {
          options.map((option, i) => {
            return <option key={i} value={option.value}>{option.name}</option>
          })
        }
      </select>
      {!!error && <span className={'fs-sm text-danger'}>{error}</span>}
    </div>
  )
};

const placeholderStyles = {
  fontStyle: 'italic',
  color: '#BBC4CF',
  fontWeight: 300
};

export default Select;