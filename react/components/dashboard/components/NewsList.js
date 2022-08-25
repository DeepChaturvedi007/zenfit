import React from 'react';
import Avatar from "react-avatar";
import moment from "moment";

const NewsItem = ({item}) => (
  <article className={'item'}>
    <Avatar
      size={28}
      name={item.title}
      src={item.picture}
      round={true}
    />
    <div className={'content text-normal'}>
      <a href={item.link} target="_blank">{item.title}</a>
      <br />
      <small className={'fs-sm text-muted'}>{moment(item.date).format('DD, MMM YYYY')}</small>
    </div>
  </article>
);

const NewsList = ({items = []}) => (
  <section className={'list vertical'}>
    { items.map((item, i) => <NewsItem key={i} item={item}/>) }
  </section>
);

export default NewsList;
