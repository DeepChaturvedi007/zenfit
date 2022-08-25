import './styles.scss';
import React, {forwardRef} from 'react';
import {
  Section as BaseSection,
  Title as BaseTitle
} from "../../../../../shared/components/Section";
export { Header, Footer, Body } from "../../../../../shared/components/Section";

export const Title = forwardRef(({ className = '', ...props}, ref) => {
  const classes = ['text-center', 'j-center', className].join(' ');
  return (
    <BaseTitle ref={ref} className={classes} {...props} />
  )
});

export const Section = forwardRef(({children, ...props}, ref) => {
  return (
    <BaseSection ref={ref} {...props}>
      <Wrapper children={children} />
    </BaseSection>
  )
});

const Wrapper = ({className, ...props}) => (
  <div className={['wrapper', className].join(' ')} {...props} />
);

export default Section;