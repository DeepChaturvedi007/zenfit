import React, {useEffect} from 'react';
import Card, {
  Header,
  Body,
  Title
} from '../../../../../shared/components/Card';
import ArticlesList from '../../../ArticlesList';
import {fetchArticles} from "../../../../store/articles/actions";
import {connect} from "react-redux";

const MembersOnly = ({ articles, fetchArticles }) => {
  useEffect(() => {
    fetchArticles();
  }, []);

  return (
    <Card id={'members-only-card'}>
      <Header className={'bordered'}>
        <Title className={'fs-14 fw-500'}>
          Members-Only Courses & Community
        </Title>
      </Header>
      <Body className={'j-start a-start'}>
        <ArticlesList items={articles} />
      </Body>
    </Card>
  );
};

const mapStateToProps = state => ({
  articles: state.articles.items
});

const mapDispatchToProps = dispatch => ({
  fetchArticles: () => dispatch(fetchArticles()),
});

export default connect(mapStateToProps, mapDispatchToProps)(MembersOnly);
