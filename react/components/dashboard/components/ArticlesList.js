import React from 'react';
import Avatar from "react-avatar";

const ArticleItem = ({item}) => (
  <article className={'item'}>
    <Avatar
      size={28}
      name={item.title}
      src={item.img}
      round={true}
    />
    <div className={'content text-normal'}>
      <a href={item.link} target="_blank">{item.title}</a>
    </div>
  </article>
);

const ArticlesList = ({items = []}) => (
  <section className={'list vertical'}>
    { items.map((item, i) => <ArticleItem key={i} item={item}/>) }
  </section>
);

export default ArticlesList;
