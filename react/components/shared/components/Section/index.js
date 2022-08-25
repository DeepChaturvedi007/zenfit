import React, { forwardRef } from 'react';
import './styles.css';

export const Section = forwardRef(({className = '', ...props}, ref) => (
  <section className={`container-fluid ${className ? className : ''}`} ref={ref} {...props} />
));

export const Header = forwardRef(({className = '', ...props}, ref) => (
  <header className={`container-header ${className ? className : ''}`} ref={ref} {...props} />
));

export const Body = forwardRef(({className = '', ...props}, ref) => (
  <article className={`container-body ${className ? className : ''}`} ref={ref} {...props} />
));

export const Footer = forwardRef(({className = '', ...props}, ref) => (
  <footer className={`container-footer ${className ? className : ''}`} ref={ref} {...props} />
));

export const Title = forwardRef(({className = '', ...props}, ref) => (
  <h2 className={`container-title ${className ? className : ''}`} ref={ref} {...props} />
));

export default Section;