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

// Проверка перед переходом на защищенные страницы
router.beforeEach((to, from, next) => {
    const userRole = sessionStorage.getItem('role'); // ✅ Теперь используем sessionStorage
    const isAuthenticated = !!userRole; 
  
    if (to.meta.requiresAuth) {
      if (!isAuthenticated) {
        next('/login'); // Если не авторизован, отправляем на страницу входа
      } else if (to.meta.role && to.meta.role !== userRole) {
        next('/login'); // ❌ Вместо редиректа на главную, теперь отправляем на страницу входа
      } else {
        next();
      }
    } else {
      next();
    }
  });
  

export default router;
