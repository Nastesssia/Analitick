import { createApp } from 'vue';
import App from './App.vue';
import Politic from './politic.vue';
import router from './router/router.js'; // Подключаем маршрутизатор

// Создаём приложение
const app = createApp(App);
app.use(router); // Подключаем роутер
app.mount('#app');

const politicApp = createApp(Politic);
politicApp.mount('#Politic');
