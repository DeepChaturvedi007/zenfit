import React from 'react';

const Button = React.memo(props => (
  <button className={`btn btn-success btn-upper new-message ${props.disabled ? 'disabled' : ''}`}
          onClick={props.handleClick}
          type="button">
    {props.btnTitle}
  </button>
));

export default Button;

