import React, {Fragment, useState} from 'react';
import {connect} from "react-redux";
import Card, {
  Header,
  Body,
  Title,
} from '../../../../../shared/components/Card';
import Affiliate from "../../../Affiliate";
import AffiliateInfoModal from '../../../Modals/AffiliateInfo';
import { getAffiliateLink } from '../../../../store/stats/actions'

const AffiliateCard = ({affiliateLink, getAffiliateLink, earnings, ...props}) => {
  const [show, setShow] = useState(false);
  const [loadingLink, setLoadingLink] = useState(false);

  const handleRequestLink = () => {
    setLoadingLink(true);
    return getAffiliateLink()
      .then(() => setLoadingLink(false))
      .catch(() => setLoadingLink(false));
  };

  return (
    <Fragment>
      <Card id={'affiliate-card'}>
        <Header className={'bordered no-wrap'}>
          <Title className={'fs-14 fw-500'} style={{maxWidth: '70%', whiteSpace: 'normal'}}>
            <span>Want to earn up to $1,500 extra every month?</span>
          </Title>
          <a href="#"  className={'pull-right text-uppercase fw-500 fs-12'} onClick={() => setShow(true)}>
            Learn more
          </a>
        </Header>
        <Body className={'j-start a-start'}>
          <Affiliate
            earnings={earnings}
            loadingLink={loadingLink}
            link={affiliateLink}
            onRequestLink={handleRequestLink}
          />
        </Body>
        <div className={'affiliate-bg'}/>
      </Card>
      <AffiliateInfoModal show={show} onHide={() => setShow(false)}/>
    </Fragment>
  );
};

const mapStateToProps = state => ({
  affiliateLink: _.get(state.stats, 'referral.link', null),
  earnings: _.get(state.stats, 'referral.earnings', 0),
});

const mapDispatchToProps = dispatch => ({
  getAffiliateLink: () => dispatch(getAffiliateLink())
});

export default connect(mapStateToProps, mapDispatchToProps)(AffiliateCard);
