import React, {Fragment} from 'react';
import Card, {
  Body as CardBody,
  Header as CardHeader,
  Title as CardTitle
} from '../../../../shared/components/Card';
import {
  Row,
  Col
} from '../../../../shared/components/Grid';

const MfpLinkCard = ({mfpLink = undefined}) => {
  return (
    <Card className={'fs-default text-muted'}>
      <CardHeader>
        <CardTitle>
          MyFitnessPal Integration
        </CardTitle>
      </CardHeader>
      <CardBody className={'j-between a-stretch'}>
        <Row>
          <Col>
            <div className={'fs-md'} style={{marginBottom: "5px"}}>
              <p className={mfpLink ? 'font-bold text-success' : 'font-bold text-danger' }>
                {
                  mfpLink ? (
                    <span style={{fontSize: "24px", lineHeight: "1"}}>
                      <i
                        className="fa fa-check-circle"
                        aria-hidden="true"
                        style={{paddingRight: '15px'}}>
                      </i>Connected
                    </span>
                  ) : (
                    <span style={{fontSize: "24px", lineHeight: "1"}}>
                      <i className="fa fa-ban"
                         aria-hidden="true"
                         style={{paddingRight: '15px'}}>
                      </i>Not Connected
                    </span>
                  )}
              </p>
            </div>
          </Col>
        </Row>
      </CardBody>
    </Card>
  );
};

export default MfpLinkCard;
