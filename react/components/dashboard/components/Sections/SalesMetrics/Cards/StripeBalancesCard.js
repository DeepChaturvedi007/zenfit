import React, {useState, useEffect} from 'react';
import _ from "lodash";
import {connect} from "react-redux";

import Card, {Header, Body, Title, Footer} from '../../../../../shared/components/Card';
import { Row, Col } from '../../../../../shared/components/Grid';
import {DifferenceIndicator} from "../../../../../shared/components/Uncommon";

import {currencyCode, transformToMoney} from "../../../../helpers";

const StripeBalancesCard = ({currency, balances = []}) => {
  const [available, setAvailable] = useState([]);
  const [pending, setPending] = useState([]);

  const fetchData = () => {
    setAvailable(balances.available);
    setPending(balances.pending);
  };

  useEffect(() => {
    fetchData()
  }, []);

  return (
    <Card id={'revenue-stream-card'} style={{maxHeight: '350px'}}>
      <Header>
        <Title>
          Stripe Payouts
        </Title>
      </Header>
      <div className="fs-normal text-muted payouts-subtitle">
        This is the money that will be paid out to you through Stripe.
        These balances are your actual earnings decucted fees.
      </div>
      <Body className={'a-start j-start'} style={{overflow: 'auto'}}>
        {
          available.map((item, i) => {
            const { amount, currency } = item;
            return (
              <Row key={i} style={{padding: '5px 0'}}>
                <Col>
                  <div className="fs-md font-bold text-dark">
                    { transformToMoney(Number(amount), currencyCode(currency)) }
                  </div>
                  <div className="fs-normal text-muted">
                    Available on your Stripe account
                  </div>
                </Col>
              </Row>
            )
          })
        }
        {
          pending.map((item, i) => {
            const { amount, currency } = item;
            return (
              <Row key={i} style={{padding: '5px 0'}}>
                <Col>
                  <div className="fs-md font-bold text-dark">
                    { transformToMoney(Number(amount), currencyCode(currency)) }
                  </div>
                  <div className="fs-normal text-muted">
                    Estimated future payouts
                  </div>
                </Col>
              </Row>
            )
          })
        }
      </Body>
    </Card>
  );
};

const mapStateToProps = state => ({
  balances: _.get(state.stats, 'payments.revenue.balances', []),
  currency: currencyCode(_.get(state.stats, 'payments.revenue.currency', 'usd')),
});

export default connect(mapStateToProps)(StripeBalancesCard);
