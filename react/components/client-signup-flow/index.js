import './styles.scss';

import React, {Fragment} from 'react';
import createStore from './store';
import ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import Main from './Main';
import {I18nextProvider} from "react-i18next";
import createTranslator from "../../shared/helper/translator";
import {initializeHttp} from "../../shared/helper/http";
import {ThemeProvider} from "styled-components";

const Wrapper = document.getElementById('client-overview');
const propsStr = Wrapper.getAttribute('data-props');

let initialProps;
try {
    initialProps = JSON.parse(propsStr) || {};
    window.locale = initialProps.language || navigator.language || 'en';
    initializeHttp(initialProps)
} catch (e) {
    console.log(e)
}

const i18next = createTranslator();
const theme = {
    primaryColor: initialProps.primaryColor
}

initialProps['client'] = JSON.parse(initialProps['client'])
initialProps['customQuestions'] = JSON.parse(initialProps['customQuestions'])
initialProps['clientImages'] = JSON.parse(initialProps['clientImages'])
initialProps['answers'] = JSON.parse(initialProps['answers'])
initialProps['userApp'] = JSON.parse(initialProps['userApp'])

const store = createStore({ signup: initialProps });
ReactDOM.render(
    <Fragment>
        <ThemeProvider theme={theme}>
            <I18nextProvider i18n={i18next}>
                <Provider store={store}>
                    <Main initialProps={initialProps} />
                </Provider>
            </I18nextProvider>
        </ThemeProvider>
    </Fragment>,
    Wrapper
);
