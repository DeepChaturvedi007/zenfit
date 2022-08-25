import React from 'react';

const cardStyles = {
  cursor: 'pointer'
};

export const CardTitle = props => <div className="ibox-title" {...props} />;

export const CardContent = props => <div className="ibox-content" {...props} />;

const Card = props => <div className="ibox cursor-pointer" style={cardStyles} {...props} />;

export default Card;