import { createRouter, createWebHistory } from 'vue-router';

import ChooseRole from '../views/ChooseRole.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';
import HomeView from '../views/HomeView.vue';
import ShopDashboard from '../views/shop/DashboardLayout.vue';
import UserDashboard from '../views/user/Dashboard.vue';
import UserBookings from '../views/user/MyBookings.vue';
import PromotionView from '../views/user/Promotion.vue';
import SettingUser from '../views/user/Setting_user.vue';
import AdminDashboard from '../views/admin/Dashboard.vue';
import ShopVehicles from '../views/user/ShopVehicles.vue';
import VehiclesByShop from '../views/user/VehiclesByShop.vue';
import Booking from '../views/user/Booking.vue';
import ViewDetail from '../views/user/ViewDetail.vue';
import AdminLayout from '../views/admin/AdminLayout.vue';

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
      name: 'landing',
      component: HomeView,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
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
      meta: { requiresAuth: false, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/booking/:id?',
      name: 'booking',
      component: Booking,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/bookings/:id?',
      name: 'user-booking',
      component: UserBookings,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/my-bookings',
      name: 'my-bookings',
      component: UserBookings,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/user/profile',
      name: 'user-profile',
      component: SettingUser,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/promotions',
      name: 'promotions',
      component: PromotionView,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] }
    },
    {
      path: '/settings',
      name: 'settings',
      component: SettingUser
    },
    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true, allowedRoles: ['admin'] },
      children: [
        {
          path: '',
          name: 'admin-dashboard',
          component: AdminDashboard
        },
        {
          path: 'dashboard',
          redirect: { name: 'admin-dashboard' }
        },
        {
          path: 'users',
          name: 'admin-users',
          component: () => import('../views/admin/UserManagement.vue')
        },
        {
          path: 'shops',
          name: 'admin-shops',
          component: () => import('../views/admin/ShopManagement.vue')
        },
        {
          path: 'vehicles',
          name: 'admin-vehicles',
          component: () => import('../views/admin/VehicleManagement.vue')
        },
        {
          path: 'bookings',
          name: 'admin-bookings',
          component: () => import('../views/admin/BookingManagement.vue')
        },
        {
          path: 'coupons',
          name: 'admin-coupons',
          component: () => import('../views/admin/CouponManagement.vue')
        },
        {
          path: 'categories',
          name: 'admin-categories',
          component: () => import('../views/admin/CategoryManagement.vue')
        },
        {
          path: 'cities',
          name: 'admin-cities',
          component: () => import('../views/admin/CityManagement.vue')
        },
        {
          path: 'financials',
          name: 'admin-financials',
          component: () => import('../views/admin/FinancialManagement.vue')
        },
        {
          path: 'reports',
          name: 'admin-reports',
          component: () => import('../views/admin/ReportManagement.vue')
        },
        {
          path: 'settings',
          name: 'admin-settings',
          component: () => import('../views/admin/SettingManagement.vue')
        }
      ]
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

// Navigation guard
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth);
  const isGuestOnly = to.matched.some(record => record.meta.guest);
  const allowedRoles = to.matched.find(record => record.meta.allowedRoles)?.meta.allowedRoles || [];
  const isAuth = isAuthenticated();
  const userRole = getUserRole();

  if (requiresAuth && !isAuth) {
    next('/login');
    return;
  }

  if (isGuestOnly && isAuth) {
    const allowAuthenticated = to.matched.some(record => record.meta.allowAuthenticated);
    if (allowAuthenticated) {
      next();
      return;
    }
    if (userRole === 'admin') {
      next('/admin');
    } else if (userRole === 'shop_owner') {
      next('/dashboard');
    } else {
      next('/view_shop');
    }
    return;
  }

  if (requiresAuth && isAuth && allowedRoles.length > 0) {
    if (!allowedRoles.includes(userRole)) {
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
