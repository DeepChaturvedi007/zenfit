import React from "react";
import './styles.scss';

const ZFBreadcrum = (props) => {
  const path = props.url.split('/');

  return (
    <span className={`zf-crum ${props.className}`}>
      {path.map((item, i) => {
        return i + 1 == path.length ? item : item + "    >    "
      })}
    </span>
  );
}

export default ZFBreadcrum;