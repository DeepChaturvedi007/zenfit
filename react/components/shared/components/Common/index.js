import React, {forwardRef} from 'react';
import './styles.css';
import "react-loader-spinner/dist/loader/css/react-spinner-loader.css"
import Loader from 'react-loader-spinner';

export const Preloader = forwardRef(({style, type = "Oval", height = 50, width = 50, timeout = 10000, ...props}, ref) => (
  <div className={'preloader-spinner'} ref={ref} style={style}>
    <Loader type={type} color="#50b5ff" height={height} width={width} timeout={timeout} />
  </div>
));

export const CenteredText = forwardRef(({text = 'No data', ...props}, ref) => (
  <div className={'text-centered'} ref={ref} {...props}>
    <p className={'fs-default text-muted'}>{text}</p>
  </div>
));
