import React, { Fragment, useEffect } from 'react';
import { connect } from 'react-redux';
import ContentWrapper from "./components/ContentWrapper";
import SummarySection from "./components/Sections/Summary";
import GrowthToolsSection from "./components/Sections/GrowthTools";
import SalesMetricsSection from "./components/Sections/SalesMetrics";
import InstagramPromotion from "./components/InstagramPromotion";
import { setup } from './store/stats/actions';

const Main = ({ initial = {}, setup = () => null }) => {
  useEffect(() => {
    setup(initial);
    console.log(initial);
  }, []);

  return (
    <Fragment>
      {/*<InstagramPromotion />*/}
      <ContentWrapper style={{ paddingRight: '15px', paddingLeft: '15px' }}>
        <SummarySection />
        {!initial.hideSales &&
          <SalesMetricsSection />
        }
        <GrowthToolsSection />
      </ContentWrapper>
    </Fragment>
  )
};

const mapStateToProps = state => ({});

const mapDispatchToProps = dispatch => ({
  setup: (data) => dispatch(setup(data))
});

export default connect(mapStateToProps, mapDispatchToProps)(Main);
