import React from "react";

const NavBar = (props) => {
    const {clientsCount, isActiveFilter, changeFn} = props;

    return (
        <nav className="responsive-tabs client-nav">
            <a className={`responsive-tab responsive-tab-active-clients ${(isActiveFilter) ? 'active' : ''}`}
               href="#"
               onClick={() => changeFn(true)}
            >
                Active clients (<span>{clientsCount.active}</span>)
            </a>
            <a className={`responsive-tab responsive-tab-active-clients ${(isActiveFilter) ? '' : 'active'}`}
               href="#"
               onClick={() => changeFn(false)}
            >
                Inactive clients (<span>{clientsCount.inactive}</span>)
            </a>
        </nav>
    );
};

export default NavBar;
