import React, { useEffect, useState } from 'react';
import Card, { Header, Body, Title, Footer } from '../../../../../shared/components/Card';
import NewsList from '../../../NewsList';
import { fetchNews } from "../../../../store/news/actions";
import { connect } from "react-redux";
import { Preloader } from '../../../../../shared/components/Common'

const NewsCard = ({title, news = [], hasMore, loadMore, ...props}) => {

  const [ ready, setReady ] = useState(true);
  useEffect(() => {
    fetchData()
  }, [null]);

  const fetchData = () => {
    if(!ready) return;
    setReady(false);
    return loadMore()
      .then(() => {
        setReady(true);
      });
  };

  return (
    <Card id={'news-card'}>
      <Header className={'bordered'}>
        <Title className={'fs-14 fw-500'}>News</Title>
      </Header>
      {
        !!ready ?
          (
            <Body className={'j-start a-start'}>
              <NewsList items={news} />
            </Body>
          ) :
          (
            <Body>
              <Preloader />
            </Body>
          )
      }

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
  news: state.news.items,
  hasMore: state.news.hasMore,
});

const mapDispatchToProps = dispatch => ({
  loadMore: () => dispatch(fetchNews()),
});

export default connect(mapStateToProps, mapDispatchToProps)(NewsCard);
