import React, { Fragment, useState, useEffect } from 'react';
import moment from 'moment';

import Collapse from '@material-ui/core/Collapse';
import Card from '../../../../shared/components/Card';
import { Row, Col } from '../../../../shared/components/Grid';
import PowerHeader from '../Modules/PowerHeader';
import SectionInfoComponent from '../Modules/SectionInfoComponent';

const ClientInfo = ({ clientId, measuringSystem, changeClientInfo, fields, selectedClient }) => {
    const [collapse, setCollapse] = useState(false);
    const [showAll, setShowAll] = useState(false);

    const handleCollapse = () => {
        setCollapse((prev) => !prev);
    };

    const handleShowAll = () => {
        setShowAll((prev) => !prev);
    };

    return (
        <Fragment>
            <Card className="client-info-card">
                <PowerHeader
                    title={'Client'}
                    handleCollapse={handleCollapse}
                    collapse={collapse}
                >
                </PowerHeader>
                <Collapse in={collapse}>
                    <div className='client-info-content'>
                        <Row style={{ paddingBottom: 0 }}>
                            {Object.values(fields).map((item, i) => {
                                if (i < 12) {
                                    let value = item.value;
                                    if(measuringSystem == 1 && (item.key === 'info.feet' || item.key === 'info.inches')) {
                                        return;
                                    }
                                    if(measuringSystem == 2 && (item.key === 'info.height')) {
                                        return;
                                    }

                                    if (item.value !== undefined)
                                        return (
                                            <Col
                                                key={item.key}
                                                size={ item.key === 'info.feet' || item.key === 'info.inches' ? 3 : 6}
                                            >
                                                {
                                                    <SectionInfoComponent
                                                        className={item.label}
                                                        title={item.label}
                                                        value={value}
                                                        type={item.type}
                                                        measuringSystem={measuringSystem}
                                                        valueType={item.valueType ? item.valueType : null}
                                                        name={item.key}
                                                        key={item.key}
                                                        optionsList={'options' in item ? item.options : null}
                                                        valueChange={changeClientInfo}
                                                        clientId={clientId}
                                                    />
                                                }
                                        </Col>
                                        )
                                }
                            })}
                        </Row>
                        <Collapse in={showAll}>
                            <Row>
                                {Object.values(fields).map((item, i) => {
                                    if (i > 11) {
                                        if (item.value !== undefined)
                                            return <Col key={item.key} size={6}>
                                                <SectionInfoComponent
                                                    title={item.label}
                                                    value={item.value}
                                                    type={item.type}
                                                    name={item.key}
                                                    key={item.key}
                                                    optionsList={'options' in item ? item.options : null}
                                                    valueChange={changeClientInfo}
                                                    clientId={clientId}
                                                />
                                            </Col>
                                    }
                                })}
                            </Row>
                        </Collapse>
                        <div className='show-all' onClick={handleShowAll}>{showAll ? "SHOW LESS" : "SHOW ALL"}</div>
                    </div>
                </Collapse>
            </Card>
        </Fragment>
    )
}

export default ClientInfo;
