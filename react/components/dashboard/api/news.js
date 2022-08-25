import { stringify } from 'query-string';

export const fetchData = async ({ limit, offset, order, sort }) => {
  const query = {
    limit,
    offset,
    order,
    sort
  };
  return fetch(`/api/news?${stringify(query)}`, {
    credentials: "include"
  })
    .then(response => response.json())
    .then(({data}) => data)
    .catch(err => console.log(err));
};