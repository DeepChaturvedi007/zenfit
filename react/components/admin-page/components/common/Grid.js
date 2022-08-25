import React from 'react';

export const Row = props =>
  <div className={'row'} {...props}/>;

export const Col = ({size = 12, variant = 'md', className = '', ...props}) =>
  <div className={`col-${variant}-${size} ${className}`} {...props}/>;