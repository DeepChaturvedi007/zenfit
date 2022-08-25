import React from 'react';
import YouTube from 'react-youtube';
import Grid from '@material-ui/core/Grid';
import moment from 'moment';
const opts = {
  height: '200',
  width: '0',
  playerVars: {
    autoplay: 0,
    controls: 0,
    modestbranding: 1
  },
};

const VideoItem = ({ item }) => {
  return (
    <article>
      <YouTube videoId={item.videoId} opts={opts} />
      <div className="video-title">{item.title}</div>
      <div className="video-date">{moment(item.date).format('MMMM D, YY')}</div>
    </article>
  )
};

const Videos = ({ items = [] }) => (
  <section className={'videos-section'}>
    {items.map((item, i) =>
      <VideoItem item={item} key={i} />
    )}
  </section>
);

export default Videos;
