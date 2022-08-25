import React from 'react';

const Button = React.memo(props => (
  <button className={`btn btn-success btn-upper new-message ${props.disabled ? 'disabled' : ''}`}
          type="submit">
    {props.btnTitle}
  </button>
));

export default Button;
