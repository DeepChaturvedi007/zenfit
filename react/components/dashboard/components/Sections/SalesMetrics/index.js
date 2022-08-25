import './styles.css';
import React, {useState} from 'react';
import TotalSalesCard from "./Cards/TotalSalesCard";
import EarningHistoryCard from "./Cards/EarningHistoryCard";
import ConnectStripeCard from "./Cards/ConnectStripeCard";
import RevenueStreamsCard from "./Cards/RevenueStreamsCard";
import StripeBalancesCard from "./Cards/StripeBalancesCard";
import { connect } from "react-redux";
import _ from "lodash";
import Section, {
  Header as SectionHeader,
  Title as SectionTitle,
  Body as SectionBody
} from "../../../../shared/components/Section";
import {Col, Row} from "../../../../shared/components/Grid";

import SalesMetricsLearnMoreModal from '../../Modals/SalesMetricsLearnMore'

const SalesMetrics = ({isConnected}) => {
  const [show, setShow] = useState(false);
  return (
    <Section>
      <SectionHeader>
        <SectionTitle>
          <span>
            Sales metrics
            {/*<a className={'learn-more'} href="#" onClick={() => setShow(true)}>Learn more</a>*/}
          </span>
        </SectionTitle>
      </SectionHeader>
      <SectionBody>
        {
          isConnected ?
            (
              <Row style={{height: '375px'}}>
                <Col size={3}>
                  <TotalSalesCard />
                </Col>
                <Col size={6}>
                  <EarningHistoryCard />
                </Col>
                <Col size={3}>
                  <StripeBalancesCard />
                </Col>
              </Row>
            ) :
            (
              <Row>
                <Col>
                  <ConnectStripeCard />
                </Col>
              </Row>
            )
        }
      </SectionBody>
      <SalesMetricsLearnMoreModal show={show} onHide={() => setShow(false)} />
    </Section>
  )
};

const mapStateToProps = state => ({
  isConnected: _.get(state.stats, 'payments.connected', false)
});

export default connect(mapStateToProps)(SalesMetrics);
