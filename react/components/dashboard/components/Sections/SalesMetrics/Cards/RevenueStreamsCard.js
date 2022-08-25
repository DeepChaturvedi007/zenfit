import React, {useState, useEffect} from 'react';
import _ from "lodash";
import {connect} from "react-redux";

import Card, {Header, Body, Title, Footer} from '../../../../../shared/components/Card';
import { Row, Col } from '../../../../../shared/components/Grid';
import {DifferenceIndicator} from "../../../../../shared/components/Uncommon";

import {currencyCode, transformToMoney} from "../../../../helpers";

const RevenueStreamsCard = ({currency, streams = []}) => {
  const common  = streams.filter(({from}) => !!from);
  const other   = streams.find(({from}) => !from);
  const limit   = 4;
  const [hasMore, setHasMore] = useState(true);
  const [items, setItems] = useState([]);

  const fetchData = () => {
    const sliced = common.slice(items.length, items.length + limit);
    const concatenated = [...items, ...sliced];
    setItems(concatenated);
    setHasMore(concatenated.length < common.length)
  };

  useEffect(() => {
    fetchData()
  }, []);

  return (
    <Card id={'revenue-stream-card'} style={{maxHeight: '350px'}}>
      <Header>
        <Title>
          Revenue streams
        </Title>
      </Header>
      <Body className={'a-start j-start'} style={{overflow: 'auto'}}>
        {
          items.map((item, i) => {
            const { thisMonth, lastMonth, percentage, from } = item;
            return (
              <Row key={i} style={{padding: '15px 0'}}>
                <Col>
                  <div className="fs-md font-bold text-dark">
                    { transformToMoney(Number(thisMonth), currency) }
                    <DifferenceIndicator value={percentage} style={{verticalAlign: 'middle'}}/>
                  </div>
                  <div className="fs-normal text-muted">
                    from <strong>{ `${from}`.toLowerCase() }</strong> (Last month: {transformToMoney(Number(lastMonth), currency)})
                  </div>
                </Col>
              </Row>
            )
          })
        }
        {
          !!other &&
          (
            <Row>
              <Col>
                <div className="fs-md font-bold text-dark">
                  { transformToMoney(Number(other.thisMonth), currency) }
                  &nbsp;
                  <span className={'fs-default text-muted font-normal'}>
                  from 'Other'
                </span>
                </div>
              </Col>
            </Row>
          )
        }
      </Body>
      {
        !!hasMore && (
          <Footer className={'interactive'} onClick={fetchData}>
            <span className={'load-more'}>Show more</span>
          </Footer>
        )
      }
    </Card>
  );
};

const mapStateToProps = state => ({
  streams: _.get(state.stats, 'payments.revenue.streams', []),
  currency: currencyCode(_.get(state.stats, 'payments.revenue.currency', 'usd')),
});

export default connect(mapStateToProps)(RevenueStreamsCard);