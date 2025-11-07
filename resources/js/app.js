import '../css/app.css';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import axios from 'axios';
import '@fortawesome/fontawesome-free/js/fontawesome';
import '@fortawesome/fontawesome-free/js/solid';

axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'https://wedding-upload.test';

const app = createApp(App);

app.config.globalProperties.$axios = axios;
app.use(router);

app.mount('#app');
