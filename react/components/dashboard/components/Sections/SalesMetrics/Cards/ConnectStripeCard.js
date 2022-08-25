import React from 'react';
import {connect} from "react-redux";
import _ from 'lodash'
import Card, {
  Body,
} from '../../../../../shared/components/Card';

const ConnectStripeCard = ({connectUrl = ''}) => {
  return (
    <Card>
      <Body className={'text-center m-b-lg text-muted fs-default'}>
        <img width={'250px'} src={'/bundles/app/images/cards-cta.svg'} alt="cards" />
        <p>
          <strong>
            No revenue data
          </strong>
        </p>
        <p>
          Collect your clients' payments automatically every month.<br />Zenfit transaction fee: 2,4%.
        </p>
        <div>
          <a href={connectUrl}
             target={'_blank'}
             style={{color: "#fff"}}
             className={'btn btn-success btn-upper'}>
            Setup your stripe account
          </a>
        </div>
      </Body>
    </Card>
  );
};

const mapStateToProps = state => ({
  connectUrl: _.get(state.stats, 'payments.stripeConnectUrl', ''),
});

export default connect(mapStateToProps)(ConnectStripeCard);
