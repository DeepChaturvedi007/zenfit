import React from 'react';
import { CircularProgress } from '@material-ui/core';

export default function Loader(props) {

    const {color} = props
    return (
        <div style={{display:"flex", justifyContent:"center",padding:"21px"}}>
            <CircularProgress color={color}/>
        </div>
    );
}
