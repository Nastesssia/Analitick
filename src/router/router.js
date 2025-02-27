import { createRouter, createWebHistory } from 'vue-router';
import App from '../App.vue';  
import Login from '../components/login.vue'; 
import Lawyer from '../components/lawyer.vue';
import Assistant from '../components/assistant.vue';

const routes = [
  { path: '/', component: App },  
  { path: '/login', component: Login },  
  { path: '/lawyer', component: Lawyer, meta: { requiresAuth: true, role: 'lawyer' } },
  { path: '/assistant', component: Assistant, meta: { requiresAuth: true, role: 'assistant' } },
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

async function checkAuth(to, from, next) {
    try {
        const response = await fetch('/check_auth.php', {
            method: 'GET',
            credentials: 'include'
        });

        const data = await response.json();
        console.log('Ответ от check_auth.php:', data);

        if (data.success) {
            if (to.meta.role && to.meta.role !== data.role) {
                alert('У вас нет доступа к этой странице.');
                next('/login');
            } else {
                next();
            }
        } else {
            next('/login');
        }
    } catch (error) {
        console.error('Ошибка проверки авторизации:', error);
        next('/login');
    }
}

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth) {
        checkAuth(to, from, next);
    } else {
        next();
    }
});

export default router;
