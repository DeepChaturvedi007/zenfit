/*jshint esversion: 6 */
import './styles.scss';

import React from 'react';
import ReactDOM from 'react-dom';

import Main from './Main';

const Wrapper = document.getElementById('plans-page-controller');
const propsStr = Wrapper.getAttribute('data-props');
let initialProps = {};
try {
  initialProps = JSON.parse(propsStr) || {};
} catch (e) {
  console.debug('Props is empty')
}
ReactDOM.render(
  <Main initial={initialProps} />,
  Wrapper
);