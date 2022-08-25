import axios from 'axios';


const client = axios.create({
    timeout: 15000,
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded'
    }
});

client.interceptors.request.use(async function requestConfig(config) {
    if(localStorage.getItem('token')) {
        config.headers['Authorization'] = localStorage.getItem('token');
    }

    return config;
});

export default client;
