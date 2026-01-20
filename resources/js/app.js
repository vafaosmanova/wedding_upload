import '../css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import '@fortawesome/fontawesome-free/js/fontawesome';
import '@fortawesome/fontawesome-free/js/solid';

const api = axios.create({
    baseURL: 'https://wedding-upload.test',
    withCredentials: true
});
const app = createApp(App);
app.config.globalProperties.$axios = api;
app.use(router);
app.mount('#app');
