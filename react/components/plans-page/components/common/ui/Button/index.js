import './styles.scss';
import React from 'react';
import { default as BaseButton } from '../../../../../shared/components/Button';

export const Button = ({className, ...props}) =>
  <BaseButton className={`plans-btn ${className}`} {...props} />;