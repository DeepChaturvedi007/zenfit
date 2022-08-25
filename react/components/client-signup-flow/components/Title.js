import React from 'react';
import {IS_MOBILE} from "../../../shared/helper/devices";

const Title = ({title = '', subTitle = '', bigSubtitle}) => {
    return (
        <div className={"zf-title"}>
            <h1>{title}</h1>
            <h2 style={{fontSize:bigSubtitle && ( IS_MOBILE ? '18px' : '24px'), maxWidth: bigSubtitle && '100%'}}>{subTitle}</h2>
        </div>
    )
}

export default Title;


