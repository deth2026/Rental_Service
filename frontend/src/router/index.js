import { createRouter, createWebHistory } from 'vue-router';
import HomeView from '../views/HomeView.vue';
import ChooseRole from '../views/ChooseRole.vue';


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
<<<<<<< HEAD
      redirect: '/home'
    },
    {
      path: '/home',
=======
>>>>>>> develop
      name: 'home',
      component: HomeView
    },
    {
      path: '/choose-role',
      name: 'choose-role',
      component: ChooseRole
    },
    
  ]
})

export default router
