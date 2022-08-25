/*jshint esversion: 6 */
import './index.scss';

import React from 'react';
import ReactDOM from 'react-dom';
import DaysList from './components/days-list';
import OptionsList from './components/options-list';

const WDListElement = document.getElementById('workout-days-list');
const planId = WDListElement.getAttribute('data-plan-id');
const itemUrl = WDListElement.getAttribute('data-item-url');
const isTemplate = WDListElement.getAttribute('data-is-template');

ReactDOM.render(
    <DaysList planId={planId} itemUrl={itemUrl} isTemplate={isTemplate}/>,
    WDListElement
);

const WDOptionsListElement = document.getElementById('workout-days-options-list');
const planName = WDOptionsListElement.getAttribute('data-plan-name');
const planComment = WDOptionsListElement.getAttribute('data-plan-comment');

ReactDOM.render(
    <OptionsList planId={planId} planName={planName} planComment={planComment} isTemplate={isTemplate}/>,
    WDOptionsListElement
);
