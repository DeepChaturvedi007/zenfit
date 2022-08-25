import React, { useState, useEffect } from 'react';
import moment from 'moment';
import SectionInfoComponent from '../Modules/SectionInfoComponent';
import Card, { Body } from '../../../../shared/components/Card';
import PowerHeader from '../Modules/PowerHeader';
import Collapse from '@material-ui/core/Collapse';
import { DURATION } from '../../../const';
import DateTime from 'react-datetime';
import "react-datetime/css/react-datetime.css";
import { connect } from 'react-redux';

const ClientNotes = ({ clientDetail, tagsList, fields, changeClientInfo }) => {
    const [collapse, setCollapse] = useState(false);

    const handleCollapse = () => {
        setCollapse((prev) => !prev);
    };

    return (
        <Card className="notes-card">
            <PowerHeader
                title={'Notes'}
                handleCollapse={handleCollapse}
                collapse={collapse}
            />
            <Collapse in={collapse}>
                <div className='client-info-content'>
                    {Object.values(fields).map(item => {
                        let options = 'options' in item ? item.options : null;
                        let placeholder = 'placeholder' in item ? item.placeholder : null;
                        if (item.key === 'tags') {
                            options = tagsList;
                        }
                        if (item.value !== undefined)
                            return <SectionInfoComponent
                                title={item.label}
                                name={item.key}
                                key={item.key}
                                value={item.value}
                                type={item.type}
                                creatable={item.creatable}
                                optionsList={options}
                                valueChange={changeClientInfo}
                                placeholder={placeholder}
                                clientId={clientDetail.id}
                            />
                    })}
                </div>
            </Collapse>
        </Card>
    )
}

function mapStateToProps({ clients }) {
    return {
        tagsList: clients.tagsList,
    }
}

export default connect(mapStateToProps)(ClientNotes);
