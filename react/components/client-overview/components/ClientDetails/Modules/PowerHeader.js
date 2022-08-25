import React from 'react';
import { Header, Title } from '../../../../shared/components/Card';
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import ExpandLessIcon from '@material-ui/icons/ExpandLess';

const PowerHeader = ({ title, subtitle, handleCollapse, collapse, children,subtitleLink }) => {
    return (
        <Header onClick={(event)=> handleCollapse && (event.target.className !== "section-header-right" && handleCollapse()) }>
            <div className='section-header'>
                <div className='section-header-left'>
                    <Title>{title}</Title>
                    {subtitle && (
                        subtitleLink
                            ? <a href={subtitleLink}><div className='section-header-subtitle'>{subtitle}</div></a>
                            : <div className='section-header-subtitle'>{subtitle}</div>
                    )}
                </div>
                {children}
                {handleCollapse && (
                    !collapse ? <ExpandMoreIcon className='section-header-collapse' style={{ fontSize: 25 }} />
                    : <ExpandLessIcon className='section-header-collapse' style={{ fontSize: 25 }}/>
                )}
            </div>
        </Header>
    );
}

export default PowerHeader;
