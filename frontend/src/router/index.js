import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import Login from '../views/auth/Login.vue'
import Register from '../views/auth/Register.vue'
import UserDashboard from '../views/user/Dashboard.vue'
import ShopDashboard from '../views/shop/Dashboard.vue'
import { userService } from '../services/database.js'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      redirect: '/home',
    },
    {
      path: '/home',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { guestOnly: true },
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { guestOnly: true },
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: UserDashboard,
      meta: { requiresAuth: true },
    },
    {
      path: '/shop-dashboard',
      name: 'shop-dashboard',
      component: ShopDashboard,
    },
  ],
})

router.beforeEach((to) => {
  const isAuthenticated = userService.isAuthenticated()

  if (to.meta?.requiresAuth && !isAuthenticated) {
    return { path: '/login' }
  }

  if (to.meta?.guestOnly && isAuthenticated) {
    return { path: '/dashboard' }
  }

  return true
})

export default router
