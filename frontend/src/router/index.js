import { createRouter, createWebHistory } from 'vue-router'
import UserBookings from '../views/user/Bookings.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'my-bookings',
      component: UserBookings,
    },
    {
      path: '/:pathMatch(.*)*',
      redirect: '/',
    },
  ],
})

export default router
