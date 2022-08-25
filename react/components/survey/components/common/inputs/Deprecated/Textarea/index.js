import './styles.scss';
import React from 'react';
import TextareaAutosize from 'react-autosize-textarea';

const Textarea = (props) => {
  const {
    id = Math.random(),
    label,
    rows = 10,
    name,
    placeholder = '',
    value = '',
    onChange = () => null
  } = props;

  const handleChange = (e) => {
    onChange(name, e.target.value)
  };
  return (
    <div className={'zf-textarea form-group'}>
      <label htmlFor={id} className={'fw-normal'}>{label}</label>
      <TextareaAutosize
        id={id}
        className={'form-control'}
        placeholder={placeholder}
        name={name}
        rows={rows}
        value={value || ''}
        onChange={handleChange}
      />
    </div>
  )
};

export default Textarea;