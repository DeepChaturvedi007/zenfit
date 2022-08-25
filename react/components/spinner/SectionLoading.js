import React from 'react';
import { CircularProgress } from '@material-ui/core';

import './styles.scss';

const SectionLoading = (props) => {
    if (!props.show) {
        return null;
    }
    return (
        <div style={{ width: '100%', height: '100%', position: 'relative' }}>
            <div className="section-loading">
                <CircularProgress size={30} />
            </div>
        </div>
    );
};

export default SectionLoading;
