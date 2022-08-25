import React, { Fragment, useEffect, useRef, useState} from 'react';
const SectionMoreComponent =React.memo((props) =>{
    const {
        deleteAction,
        visitLink,
        itemName,
        actionItemId,
    } = props

    const [open,setOpen] = useState(false);
    const wrapperReference = useRef("");


    useEffect(() => {
        if (open) {
            document.addEventListener('mousedown', handleClickOutside);
        }
        return ( ) =>{
            document.removeEventListener('mousedown', handleClickOutside);
        }
    }, [open]);

    const handleClickOutside =(event)=> {
        if (wrapperReference && !wrapperReference.current.contains(event.target) && open) {
            toggleDropdown();
        }
    }

    const toggleDropdown = () => {
        setOpen(!open)
    };
    return(
        <Fragment>
            <div className={`client-item-more ${(open) ? 'open' : ''}`} ref={wrapperReference}>
                <ul className={`client-item-more-list ${(open) ? 'open' : ''}`}>
                    {
                        visitLink &&(
                            <li className={`client-item-more-list-action`}>
                                <a href={visitLink}>
                                    <span>Go to {itemName}</span>
                                </a>
                            </li>
                        )
                    }
                    {
                        deleteAction &&(
                            <li className={`client-item-more-list-action`}
                                onClick={() => deleteAction(actionItemId)}>
                                <span>Delete</span>
                            </li>
                        )
                    }
                </ul>
                <span className="client-item-more-btn" onClick={()=> toggleDropdown()} >More</span>
            </div>
        </Fragment>
    )
})

export default SectionMoreComponent
