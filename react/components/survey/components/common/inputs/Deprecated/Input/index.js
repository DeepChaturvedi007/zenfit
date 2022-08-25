import './styles.scss';
import React, { useState, useEffect } from 'react';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css'


const Index = (props) => {
  const id = Math.random();
  const {
    label,
    name,
    value,
    type = 'text',
    placeholder = '',
    onChange = () => null,
    required
  } = props;
  const [val, setVal] = useState(value);
  const [error, setError] = useState(null);

  const validate = () => {
    if(!val && required) {
      setError(`"${name}" is required`);
      return;
    }
    switch (type) {
      case 'email': {
        const pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        if(!pattern.test(String(val).toLowerCase())) {
          setError('Please input a valid email address');
        }
        break;
      }
      default: {
        setError(null);
      }
    }
  };

  const handleChange = (value) => {
    setVal(value);
  };

  const handleBlur = () => {
    validate();
    if(!error) onChange(name, val);
  };

  const handleFocus = () => {
    setError(null);
  };

  useEffect(() => {
    if(value === val) return;
    setVal(value)
  }, [value]);

  if(type === 'tel') {
    const locale = window.locale || 'en';
    const [lang, country] = locale.split('_');
    return (
      <div className={`zf-input phone form-group ${!!error ? 'error' : ''}`}>
        <label htmlFor={id} className={'fw-normal'}>{label}</label>
        <div onClick={() => handleFocus()}>
          <PhoneInput
            id={id}
            name={name}
            value={val || ''}
            placeholder={placeholder}
            type={type}
            onChange={(phone) => handleChange(phone)}
            onBlur={() => handleBlur()}
            onFocus={() => handleFocus()}
            country={(country || 'us').toLowerCase()}
            localization={lang}
            buttonClass={'country-select'}
            inputClass={'phone-input'}
            enableSearch
            disableSearchIcon
          />
        </div>
        {!!error && <span className={'text-danger'}>{error}</span>}
      </div>
    );
  }
  return (
    <div className={`zf-input form-group ${!!error ? 'error' : ''}`}>
      <label htmlFor={id} className={'fw-normal'}>{label}</label>
      <input
        id={id}
        className={'form-control'}
        name={name}
        value={val || ''}
        placeholder={placeholder}
        type={type}
        onChange={(e) => handleChange(e.target.value)}
        onBlur={() => handleBlur()}
        onFocus={() => handleFocus()}
      />
      {!!error && <span className={'text-danger'}>{error}</span>}
    </div>
  )
};

export default Index;