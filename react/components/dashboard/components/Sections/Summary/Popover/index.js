import './styles.css';
import React from 'react';
import { Popover as BasePopover } from "react-bootstrap";

const Popover = ({id = Math.random(), className = '', ...props}) =>
  <BasePopover id={id} className={`zf-popover ${className}`} {...props} />;

export default Popover;