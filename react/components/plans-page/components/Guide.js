import React from 'react';
import {Col, Row} from "../../shared/components/Grid";
import {Alert} from 'react-bootstrap';

const PREVIEW_IMG = '/bundles/app/images/plans-page/preview.png';

const Guide = ({onClose = () => null}) => {
  return (
    <Alert
      bsClass={'guide-card'}
      onDismiss={onClose}
      closeLabel={'x'}
    >
      <Row>
        <Col className={'col-sm-12 col-md-3 j-center a-center'}>
          <img src={PREVIEW_IMG} alt=""/>
        </Col>
        <Col className={'col-sm-12 col-md-9 j-between'}>
          <h4>Did you know...</h4>
          <p>
            You can earn money by selling plans automatically (even when you're asleep)
            by using a trainer website built by Zenfit
          </p>
          <a href={'mailto:hello@zenfitapp.com'} className={'learn-more'}>Learn more</a>
        </Col>
      </Row>
    </Alert>
  )
};

export default Guide;
