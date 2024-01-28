import './bootstrap';
import { createApp } from 'vue';

const app = createApp({});

import WeatherApp from './components/WeatherApp.vue';
app.component('weather-app', WeatherApp);

app.mount('#app');
