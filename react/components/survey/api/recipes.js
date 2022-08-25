import axios from 'axios';

export const suggest = async (q) => {
  const locale = (window && window.locale) || 'en';
  return axios.get(`/api/recipe/get-ingredients?locale=${locale}&q=${q}`)
    .then(response => {
      return response.data;
    })
    .catch((error) => {
      return [];
    });
};