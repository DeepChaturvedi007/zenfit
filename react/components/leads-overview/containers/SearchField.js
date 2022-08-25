
import React from 'react';

import Paper from '@material-ui/core/Paper';
import InputBase from '@material-ui/core/InputBase';
import SearchIcon from '@material-ui/icons/Search';

import withStyle from './styles';
const SearchField = (props) => {
    const {
        classes,
        searchLead
    } = props;

    return(
        <Paper className={classes.searchContent} elevation={0} >
            <InputBase
                className={classes.input}
                placeholder='Search name...'
                onChange={(e) => {searchLead(e.target.value)}}
            />
            <div className={classes.searchIcon}>
                <SearchIcon />
            </div>
        </Paper>
    )
}

export default withStyle(SearchField);