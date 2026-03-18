import { createRouter, createWebHistory } from 'vue-router'
import Shopbooking from '../views/shop/Shopbooking.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'my-bookings',
      component: Shopbooking,
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

export default router
