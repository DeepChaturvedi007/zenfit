import React from 'react';
import createStore from './store';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import Main from './Main';
import {I18nextProvider, Resource} from "react-i18next";
import createTranslator from "../../shared/helper/translator";

const Wrapper = document.getElementById('auth');
const propsStr = Wrapper.getAttribute('data-props');
const i18next = createTranslator();

let initialProps = {};
try {
    initialProps = JSON.parse(propsStr) || {};
    initialProps['locales'] = JSON.parse(initialProps['locales']);
} catch (e) {
    console.log(e)
}

const store = createStore({ auth: initialProps });
ReactDOM.render(
    <I18nextProvider i18n={i18next}>
        <Provider store={store}>
            <Main />
        </Provider>
    </I18nextProvider>,
    Wrapper
);
