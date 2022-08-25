import React from 'react'

import withStyle from './styles';
const EmptyLead = (props) => {
    const {
        classes,
        handleAddLeadModal
    } = props;
    return(
        <div className={classes.emptyContent}>
            <h2>There are no leads that fit these criteras</h2>
        </div>
    )
}

export default withStyle(EmptyLead);
