import React from 'react';
import Card, {
  Header,
  Body,
  Title
} from '../../../../../shared/components/Card';
import { Row, Col } from '../../../../../shared/components/Grid';
import {DifferenceIndicator} from "../../../../../shared/components/Uncommon";
import { OverlayTrigger } from 'react-bootstrap';

const StatCard = ({title = '',link, thisMonth = 0, percentage = 0, lastMonth = 0, popover: Popover}) => {
  return (
    <Card style={{fontFamily: 'Roboto, sans-serif'}}>
      <a href={link ? link : "#"} style={{cursor: !link && "default"}}>
        <Header>
        <Title>
          {title}
        </Title>
      </Header>
      <Body className={'a-normal '} style={{marginBottom: '15px', marginTop: '3px',padding: "0 15px"}}>
        <Row style={{padding:0}}>
          <Col size={10} mode={'xs'}>
            <div>
              <span className="text-dark fs-24 font-bold" style={{verticalAlign: 'middle', marginRight: '5px'}}>
                { thisMonth }
              </span>
              <DifferenceIndicator value={percentage} style={{verticalAlign: 'middle'}} />
              <br/>
              <span className="fs-14 text-muted font-normal">
                Last month: {lastMonth}
              </span>
            </div>
          </Col>
          <Col size={2} mode={'xs'} className={'text-right'}>
            <i className={"fa fa-chevron-right"}
               style={{color: '#000', opacity: 0.16, marginRight: '15px'}}
            />
          </Col>
        </Row>
      </Body>
      </a>
    </Card>
  );
};

const styles = {
  icon: {
    display: 'flex',
    justifyContent: 'center',
    alignItems: 'center',
    padding: '5px',
    border: '2px solid #000',
    color: '#000',
    fontWeight: 'bold',
    width: '17px',
    height: '17px',
    borderRadius: '50%',
    opacity: 0.22,
    cursor: 'pointer',
    fontSize: '12px'
  }
};

export default StatCard;
