import {stringify} from 'query-string'

const onSuccess = response => response.json();
const onError = err => {
  console.log(err);
  return err.message();
};

export const getUsers = (data) => {
  const url = `/admin/api/users?${stringify(data)}`;
  return fetch(url, {
    credentials: "include"
  })
    .then(onSuccess)
    .catch(onError)
};

export const getUsersStats = () => {
  const url = '/admin/api/stats';
  return fetch(url, {
    credentials: "include"
  })
    .then(onSuccess)
    .catch(onError)
};