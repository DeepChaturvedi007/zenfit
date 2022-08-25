import React from 'react';
import Loader from 'react-loader-spinner'

const style = {
  minHeight: '250px',
  width: '100%',
  height: '100%',
  justifyContent: 'center',
  alignItems: 'center',
  display: 'flex'
};

const Preloader = () => (
  <div style={style}>
    <Loader type="Oval"
            color="#50b5ff"
            height={50}
            width={50}
            timeout={10000} />
  </div>
);

export default Preloader;
