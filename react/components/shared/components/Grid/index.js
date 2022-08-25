import React, { forwardRef } from 'react';
import './styles.css';

export const Row = forwardRef(({className = '', ...props}, ref) => (
  <div className={`zf row ${className}`} ref={ref} {...props}/>
));

export const Col = forwardRef(({mode = 'md', size = '12', className = '', order='', ...props}, ref) => (
  <div className={`zf col-${mode}-${size} ${className}`} style={{order:order}} ref={ref} {...props} />
));
