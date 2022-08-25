import axios from 'axios';

export const initializeHttp = (initialProps) => {
  axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest'
  }
  return axios
}
export default axios;
