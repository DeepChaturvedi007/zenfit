import React, { forwardRef } from 'react';
import './styles.css';

export const Card = forwardRef(({className = '', ...props}, ref) => (
  <section className={`flex-card ${className ? className : ''}`} ref={ref} {...props} />
));

export const Header = forwardRef(({className = '', ...props}, ref) => (
  <header className={`card-header ${className ? className : ''}`} ref={ref} {...props} />
));

export const Body = forwardRef(({className = '', ...props}, ref) => (
  <article className={`card-body ${className ? className : ''}`} ref={ref} {...props} />
));

export const Footer = forwardRef(({className = '', ...props}, ref) => (
  <footer className={`card-footer ${className ? className : ''}`} ref={ref} {...props} />
));

export const Title = forwardRef(({className = '', ...props}, ref) => (
  <h5 className={`card-title ${className ? className : ''}`} ref={ref} {...props} />
));

export default Card;