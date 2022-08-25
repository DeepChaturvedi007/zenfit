import React, {Fragment} from "react";

const IsThereDataComponent =(props) =>{
    const {length, name,loading} = props
    return(
        <Fragment>
            {
                length || loading ? (
                    props.children
                ):(
                    <p style={{fontWeight:"bold",textAlign:"left"}}>{`No ${name ? name : "data"} to show`}</p>
                )
            }
        </Fragment>
    )
}

export default IsThereDataComponent;
