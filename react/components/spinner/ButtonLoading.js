import React from 'react';
import CircleProcess from '@material-ui/core/CircularProgress';

const ButtonLoading = (props) => {
    const {
        size
    } = props;
    return(
        <CircleProcess size={size} classes={{root: 'btn-loading-content'}}/>
    )
}

export default ButtonLoading