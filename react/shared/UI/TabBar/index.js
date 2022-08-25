import React from "react";
import './styles.scss';

const ZFTabBar = (props) => {
    const { tabs, activeTab, onNavigateTab, className = '' } = props;

    return (
        <nav className={`zf-tabbar ${className}`}>
            {tabs.map((item, index) => {
                return <a className={`zf-tabbar--item ${(activeTab === item.value) ? 'active' : ''}`}
                    href="#"
                    key={item.value}
                    onClick={() => onNavigateTab(item.value)}
                >
                    <span>{item.label}</span>
                </a>
            })}
        </nav>
    );
};

export default ZFTabBar;