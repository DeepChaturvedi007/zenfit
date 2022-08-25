import axios from 'axios';

const Wrapper = document.getElementById('settings');
const propsStr = Wrapper.getAttribute('data-props');
let initialProps = JSON.parse(propsStr) || {};
localStorage.setItem('token', initialProps.token);

axios.defaults.headers.common = {
  'Authorization': localStorage.getItem('token'),
  'X-Requested-With': 'XMLHttpRequest'
}
export default axios;