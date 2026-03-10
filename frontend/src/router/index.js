import { createRouter, createWebHistory } from 'vue-router';

import HomeView from '../views/HomeView.vue';
import ChooseRole from '../views/ChooseRole.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import ShopDashboard from '../views/shop/DashboardLayout.vue';
import UserDashboard from '../views/user/Dashboard.vue';
import SettingUser from '../views/user/Setting_user.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import ShopVehicles from '../views/user/ShopVehicles.vue';
import VehiclesByShop from '../views/User/VehiclesByShop.vue';
import ViewDetail from '../views/User/ViewDetail.vue';

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
      path: '/home',
      redirect: '/'
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
      component: ShopDashboard,
      meta: { requiresAuth: true, allowedRoles: ['shop_owner', 'admin'] }
    },
    {
      path: '/view_shop',
      name: 'view_shop',
      component: UserDashboard,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/user/profile',
      name: 'user-profile',
      component: SettingUser,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/settings',
      name: 'settings',
      component: SettingUser
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminDashboard,
      meta: { requiresAuth: true, allowedRoles: ['admin'] }
    },
    {
      path: '/vehicles',
      name: 'vehicles-by-shop',
      component: VehiclesByShop,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-detail',
      component: ViewDetail,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'shop_owner', 'admin'] }
    },
    {
      path: '/shop/:id/vehicles',
      name: 'shop-vehicles',
      component: ShopVehicles,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    }
  ]
});

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
      next('/view_shop');
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
        next('/view_shop');
      }
      return;
    }
  }

  next();
});

export default router;
