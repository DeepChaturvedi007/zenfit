import React from 'react';
import {connect} from "react-redux";

import Section, { Body as SectionBody } from "../../../../shared/components/Section";
import { Col, Row } from "../../../../shared/components/Grid";

import StatCard from "./Cards/StatCard";
import Popover from "./Popover";

const Summary = ({leadsStats = {}, clientsStats = {}, conversionStats = {}}) => {

  const conversionRatePopover = (
    <Popover>
      <p>
        This is the percentage of leads you get in and convert into clients.
      </p>
      <p>
        Eg. 2 clients from 10 leads = 20% conversion rate.
      </p>
      <p>
        Learn how to get more leads automatically and convert more leads into clients. <a href="#">Learn more.</a>
      </p>
    </Popover>
  );

  return (
    <Section>
      <SectionBody>
        <Row>
          <Col size={4}>
            <StatCard
              title={'New Leads'}
              thisMonth={leadsStats.thisMonth}
              lastMonth={leadsStats.lastMonth}
              percentage={leadsStats.percentage}
              link={"/dashboard/leads"}
            />
          </Col>
          <Col size={4}>
            <StatCard
              title={'Total Clients'}
              thisMonth={clientsStats.thisMonth}
              lastMonth={clientsStats.lastMonth}
              percentage={clientsStats.percentage}
              link={"/dashboard/clients"}
            />
          </Col>
          <Col size={4}>
            <StatCard
              title={'Conversion Rate'}
              thisMonth={`${conversionStats.thisMonth || 0}%`}
              lastMonth={`${conversionStats.lastMonth || 0}%`}
              percentage={conversionStats.percentage}
              popover={conversionRatePopover}
            />
          </Col>
        </Row>
      </SectionBody>
    </Section>
  );
};

const mapStateToProps = state => ({
  leadsStats: _.get(state.stats, 'metrics.leads', {}),
  clientsStats: _.get(state.stats, 'metrics.clients', {}),
  conversionStats: _.get(state.stats, 'metrics.conversion', {}),
});

export default connect(mapStateToProps)(Summary);
