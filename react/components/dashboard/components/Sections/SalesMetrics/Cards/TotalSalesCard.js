import React, {useState, useEffect} from 'react';
import {connect} from "react-redux";
import _ from 'lodash';
import Card, {
  Header,
  Body,
  Title, Footer
} from '../../../../../shared/components/Card';
import { DifferenceIndicator } from '../../../../../shared/components/Uncommon';
import { CircleChart } from '../../../../../shared/components/Chart';
import {transformToMoney, roundToDecimals, currencyCode} from "../../../../helpers";
import numeral from 'numeral';

const TotalSalesCard = ({currency, charged, progress, prevCharged, difference}) => {

  const [prefixText, setPrefixText] = useState('You\'ve earned');
  const [progressText, setProgressText] = useState('');
  const [suffixText] = useState('this month');

  useEffect(() => {
    setProgressText(numeral(charged).format('0,0'));
  }, [charged]);

  useEffect(() => {
    if(currency) {
      setPrefixText(`You've earned ${currency}`);
    }
  }, [currency]);
  return (
    <Card style={{height: '350px', maxHeight: '350px', flexWrap: 'wrap-reverse'}}>
      <Header>
        <Title>
          Total revenue
        </Title>
      </Header>
      <Body style={{padding: '15px'}}>
        <CircleChart
          prefixText={prefixText}
          progressText={progressText}
          suffixText={suffixText}
          progress={progress}
        />
      </Body>
      <Footer style={{paddingBottom: '10px'}}>
        <p className="fs-default text-muted" style={{margin: 0}}>
          Last month revenue (fees incl.)
        </p>
        <p className="fs-24 text-dark fw-bold" style={{margin: 0}}>
          <span style={{verticalAlign: 'middle'}}>{ transformToMoney(Number(prevCharged), currency) }</span>
        </p>
        <br/>
      </Footer>
    </Card>
  );
};

const mapStateToProps = state => ({
  charged: roundToDecimals(_.get(state.stats, 'payments.revenue.goal.charged', 0), 2),
  progress: roundToDecimals(_.get(state.stats, 'payments.revenue.goal.progress', 0), 0),
  prevCharged: roundToDecimals(_.get(state.stats, 'payments.revenue.metrics.last.total', 0), 2),
  currency: currencyCode(_.get(state.stats, 'payments.revenue.currency', 'usd')),
  difference: roundToDecimals(_.get(state.stats, 'payments.revenue.metrics.current.percentage_change', 0), 2),
});

export default connect(mapStateToProps)(TotalSalesCard);
