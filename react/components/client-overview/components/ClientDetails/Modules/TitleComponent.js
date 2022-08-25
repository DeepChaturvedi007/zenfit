import React, { useState, useEffect, Fragment, useRef } from 'react';
import EditIcon from "@material-ui/icons/Edit";

const TitleComponent = (props) => {
    const {
        name,
        submit,
        id,
        clientId,
        successMsg
    } = props

    const [open, setOpen] = React.useState(false);
    const wrapperRef = useRef("");
    const [stateName, setStateName] = React.useState(name);

    useEffect(() => {
        setStateName(name);
    }, [name]);

    useEffect(() => {
        if (open) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [open]);

    useEffect(() => {
        if (name !== stateName && !open) {
            try {
                submit("name", stateName, id, clientId);
                toastr.success(successMsg);
            } catch (event) {
                toastr.error("There was an error");
            }

        }
    }, [stateName, open])

    const handleClickOutside = (event) => {
        if (wrapperRef && !wrapperRef.current.contains(event.target) && open) {
            setOpen(!open)
        }
    }

    return (
        <Fragment>
            <div className="titleComponent">
                <input
                    min={1}
                    type="text"
                    className={open ? "changing" : ""}
                    value={stateName}
                    ref={wrapperRef}
                    onChange={(event) => setStateName(event.target.value)}
                    onClick={() => setOpen(true)} />
                {!open && <EditIcon onClick={() => setOpen(!open)} />}
            </div>
        </Fragment>
    )
}

export default TitleComponent;
