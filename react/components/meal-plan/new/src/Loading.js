import React from 'react';
import { LoadingBox, LoadingDot } from './components/UI';

const Loading = React.memo(({ size }) => (
  <LoadingBox>
    <LoadingDot size={size}/>
    <LoadingDot size={size}/>
    <LoadingDot size={size}/>
  </LoadingBox>
));

export default Loading;
