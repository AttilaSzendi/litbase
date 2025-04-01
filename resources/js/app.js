import '../css/app.css';
import './bootstrap';

import { createApp } from 'vue';
import App from './Pages/App.vue';
import router from './router';

const app = createApp(App);
app.use(router);
app.mount('#app');
