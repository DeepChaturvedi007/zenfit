import React, {useState,useEffect} from 'react';
import { TextField} from '@material-ui/core';
import {connect} from "react-redux";
import * as clientAdministrationActions from "../../../actions/client-actions"
import CloseSharpIcon from '@material-ui/icons/CloseSharp';
import InputBase from "@material-ui/core/InputBase";
import SearchIcon from "@material-ui/icons/Search";
import Paper from "@material-ui/core/Paper";
import {WAIT} from "../../../const"



const SearchTable  = ({searchClients}) =>{
    const [query,setQuery] =useState("");

    useEffect(() => {
        const delayDebounceFn = setTimeout(() => {
            searchClients(query);
        }, WAIT)
        return () => clearTimeout(delayDebounceFn)
    },[query]);

    const handleClear=()=>{
        setQuery('')
    }

    return (
        <div className={"searchContainer"}>
            <Paper>
                <InputBase
                    value={query}
                    placeholder='Search by name or email'
                    onChange={(e) => {setQuery(e.target.value)}}
                />
                <div className={"searchIcon"}>
                    <SearchIcon />
                </div>
            </Paper>
            {query ? <CloseSharpIcon onClick={handleClear}/> : null }
        </div>
    )
}
function mapStateToProps(state) {
    return {
    }
}

export default connect(mapStateToProps, {...clientAdministrationActions})(SearchTable);


