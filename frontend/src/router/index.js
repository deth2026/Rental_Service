import { createRouter, createWebHistory } from 'vue-router';

import HomeView from '../views/HomeView.vue';
import ChooseRole from '../views/ChooseRole.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import Dashboard from '../views/shop/Dashboard.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import VehicleDetail from '../views/vehicle/VehicleDetail.vue';

// Check if user is authenticated
const isAuthenticated = () => {
  return Boolean(localStorage.getItem('auth_token') || localStorage.getItem('token'));
};

// Get user role from localStorage
const getUserRole = () => {
  const userStr = localStorage.getItem('user');
  if (userStr) {
    const user = JSON.parse(userStr);
    return user.role || 'customer';
  }
  return null;
};

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: { requiresAuth: false }
    },
    {
      path: '/chooserole',
      name: 'chooserole',
      component: ChooseRole,
      meta: { requiresAuth: false }
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { requiresAuth: false }
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { guest: true, allowAuthenticated: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: Dashboard,
      meta: { requiresAuth: true, allowedRoles: ['shop_owner', 'admin'] }
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminDashboard,
      meta: { requiresAuth: true, allowedRoles: ['admin'] }
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-detail',
      component: VehicleDetail,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'shop_owner', 'admin'] }
    }
   
  ]
})

// Navigation guard to check authentication and authorization
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const isGuestOnly = to.matched.some(record => record.meta.guest);
  const allowedRoles = to.matched.find(record => record.meta.allowedRoles)?.meta.allowedRoles || [];
  const isAuth = isAuthenticated();
  const userRole = getUserRole();

  // Redirect to login if authentication is required but user is not authenticated
  if (requiresAuth && !isAuth) {
    next('/login');
    return;
  }

  // Redirect to dashboard if user is already authenticated and trying to access guest pages (login/register)
  if (isGuestOnly && isAuth) {
    // Allow authenticated users to access login page (to switch accounts)
    const allowAuthenticated = to.matched.some(record => record.meta.allowAuthenticated);
    if (allowAuthenticated) {
      next();
      return;
    }
    // Redirect based on role
    if (userRole === 'admin') {
      next('/admin');
    } else if (userRole === 'shop_owner') {
      next('/dashboard');
    } else {
      next('/');
    }
    return;
  }

  // Check role-based access control
  if (requiresAuth && isAuth && allowedRoles.length > 0) {
    if (!allowedRoles.includes(userRole)) {
      // User doesn't have the required role, redirect to their appropriate dashboard
      if (userRole === 'admin') {
        next('/admin');
      } else if (userRole === 'shop_owner') {
        next('/dashboard');
      } else {
        next('/');
      }
      return;
    }
  }

  next();
});

export default router;
