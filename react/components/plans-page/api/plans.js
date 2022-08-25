import axios from 'axios';

const DEBUG = false;
if(DEBUG) {
  console.warn('Debug mode enabled!')
}
const http = axios.create({
  baseURL: `/plans/api`,
  withCredentials: true
});

export const fetchPlans = (limit, offset) => {
  return http.get(`/plans?offset=${offset}&limit=${limit}`)
    .then(({data}) => data)
};

export const deletePlan = (id) => {
  return http.delete(`/plans/${id}`)
    .then(({data}) => data)
};

export default {
  fetch: fetchPlans,
  remove: deletePlan
}
