import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import UpdaterContainer from './updater-container';

const UpdaterElement = document.getElementById('unread-message-updater');
if(UpdaterElement) {
    const count = UpdaterElement.getAttribute('data-count');
    const userId = UpdaterElement.getAttribute('data-user');

    ReactDOM.render(
        <UpdaterContainer count={count} userId={userId}/>,
        UpdaterElement
    );
}
