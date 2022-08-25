import React from 'react';
import {
  Card,
  Header,
  Body
} from '../../shared/components/Card';
import PlansList from "./PlansList";

const PlansSummary = () => {
  return (
    <Card>
      <Header />
      <Body style={{overflow: 'hidden'}}>
        <PlansList />
      </Body>
    </Card>
  )
};

export default PlansSummary;
