import React, { Fragment, useState, useEffect } from 'react';
import { connect } from "react-redux";
import { fetchVideos } from "../../../../store/videos/actions";
import Card, {
  Header,
  Body,
  Title,
} from '../../../../../shared/components/Card';
import Videos from '../../../Videos';

const VideoCard = ({ videos, fetchVideos }) => {
  useEffect(() => {
    fetchVideos();
  }, []);

  return (
    <Fragment>
      <Card id={'video-card'}>
        <Header className={'bordered no-wrap'}>
          <Title className={'fs-14 fw-500'} style={{maxWidth: '70%', whiteSpace: 'normal'}}>
            <span>Growth Videos</span>
          </Title>
        </Header>
        <Body className={'j-start a-start'}>
          <Videos items={videos} />
        </Body>
      </Card>
    </Fragment>
  );
};

const mapStateToProps = state => ({
  videos: state.videos.items
});

const mapDispatchToProps = dispatch => ({
  fetchVideos: () => dispatch(fetchVideos()),
});

export default connect(mapStateToProps, mapDispatchToProps)(VideoCard);
