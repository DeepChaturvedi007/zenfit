import React, {useEffect} from 'react';
import ContentWrapper from "./components/common/ui/ContentWrapper";
import Content from './components/Content';
import {connect} from 'react-redux';
import {setup} from './store/config/actions';
import {setValue} from './store/survey/actions';
import _ from 'lodash';

const Main = ({ready = false, bundleId, setBundle, initial = {}, setup = () => null}) => {
  useEffect(() => {
    setup(initial);
  }, [initial]);
  useEffect(() => {
    setBundle(bundleId);
  }, [bundleId]);
  return (
    <ContentWrapper id={'survey-page-form'}>
      {!!ready && <Content />}
    </ContentWrapper>
  )
};

const mapStateToProps = (state) => ({
  ready: _.get(state.config, 'ready', false),
  bundleId: _.get(state.config, 'bundle.id', undefined),
});

const mapDispatchToProps = (dispatch) => ({
  setup: (data) => dispatch(setup(data)),
  setBundle: (id) => dispatch(setValue('bundle', id))
});

export default connect(mapStateToProps, mapDispatchToProps)(Main);
