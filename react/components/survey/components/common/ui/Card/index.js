import './styles.scss';
import React, {forwardRef} from 'react';
import {
  Card as BaseCard,
  Body as BaseBody,
  Header as BaseHeader,
  Footer as BaseFooter,
  Title as BaseTitle
} from "../../../../../shared/components/Card";

export const Card = forwardRef((props, ref) =>
  <BaseCard
    ref={ref}
    {...props}
  />
);

export const Header = forwardRef((props, ref) =>
  <BaseHeader
    ref={ref}
    {...props}
  />
);

export const Body = forwardRef((props, ref) =>
  <BaseBody
    ref={ref}
    {...props}
  />
);

export const Footer = forwardRef((props, ref) =>
  <BaseFooter
    ref={ref}
    {...props}
  />
);

export const Title = forwardRef((props, ref) =>
  <BaseTitle
    ref={ref}
    {...props}
  />
);

export default Card;