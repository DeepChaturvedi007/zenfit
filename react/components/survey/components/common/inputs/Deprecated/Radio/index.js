import './styles.scss';
import React from 'react';

const Radio = (props) => {
  const id = Math.random();
  const {
    label,
    name,
    value,
    onChange = () => null,
    checked
  } = props;

  const handleChange = (e) => {
    onChange(name, value)
  };

  return (
    <div className={'form-group'}>
      <div className={'zf-radio'}>
        <input id={id}
               name={name}
               type="radio"
               className={'form-control'}
               onChange={handleChange}
               checked={checked}
        />
        <label htmlFor={id}>
          {!!label && label}
        </label>
      </div>
    </div>
  )
};

export default Radio;