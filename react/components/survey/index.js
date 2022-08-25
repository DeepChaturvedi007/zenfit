/*jshint esversion: 6 */
import './styles.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import {Provider} from "react-redux";
import {I18nextProvider} from 'react-i18next';

import Main from './Main';
import createStore from "./store";
import createTranslator from '../../shared/helper/translator';
import './resizeHelper';

const Wrapper = document.getElementById('survey-page-controller');
const propsStr = Wrapper.getAttribute('data-props');
let initialProps = {};
try {
  initialProps = JSON.parse(propsStr) || {};
  window.locale = initialProps.language || navigator.language || 'en';
} catch (e) {
  console.debug('Props is empty')
}
const store = createStore();
const i18next = createTranslator();
ReactDOM.render(
    <I18nextProvider i18n={i18next}>
      <Provider store={store}>
        <Main initial={initialProps} />
      </Provider>
    </I18nextProvider>,
    Wrapper
);
