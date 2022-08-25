/*jshint esversion: 6 */
import React from 'react';
import './styles.scss';

const Spinner = (props) => {
    if (!props.show) {
        return null;
    }
    return (
        <div className="loader-container">
            <div className="loader">Loading...</div>
        </div>
    );
};

export default Spinner;
