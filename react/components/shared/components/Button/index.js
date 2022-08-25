import React, { useState } from 'react';
import './styles.css';

const getElement = (el) => {
  switch (el) {
    case 'a':
    case 'button':
      return el;
    default:
      return 'button'
  }
};

const getClassByVariant = (variant) =>
  `btn-${variant}`;

const getWidthClass = (width) => {
  switch (width) {
    case 10:
    case 20:
    case 30:
    case 40:
    case 50:
    case 60:
    case 70:
    case 80:
    case 90:
    case 100:
      return `w-${width}`;
    case 'auto':
    default:
      return 'w-auto';
  }
};

const Button = ({ className = '', variant = 'primary', el = 'button', inverse, width, ...props }) => {
  const classes = className
    .split(' ');
  classes.push('btn')
  classes.push(getClassByVariant(variant))
  classes.push(getWidthClass(width))
  if(inverse) {
    classes.push('inverse');
  }
  let Element = getElement(el);
  return (
    <Element
      className={classes.join(' ')}
      {...props}
    />
  )
};

export default Button;