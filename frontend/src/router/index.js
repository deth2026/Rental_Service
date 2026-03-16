import { createRouter, createWebHistory } from 'vue-router';

import HomeView from '../views/HomeView.vue';
import ChooseRole from '../views/ChooseRole.vue';
import Login from '../views/auth/Login.vue';
import Register from '../views/auth/Register.vue';

import ShopDashboard from '../views/shop/DashboardLayout.vue';
<<<<<<< HEAD

import UserDashboard from '../views/user/Dashboard.vue';
import UserBookings from '../views/user/Bookings.vue';
import PromotionView from '../views/user/Promotion.vue';
import SettingUser from '../views/user/Setting_user.vue';
import ShopVehicles from '../views/user/ShopVehicles.vue';
import VehiclesByShop from '../views/user/VehiclesByShop.vue';
import ViewDetail from '../views/user/ViewDetail.vue';

import AdminLayout from '../views/admin/AdminLayout.vue';
=======
import UserDashboard from '../views/User/Dashboard.vue';
import UserBookings from '../views/User/Booking.vue';
import PromotionView from '../views/User/Promotion.vue';
import SettingUser from '../views/User/Setting_user.vue';
>>>>>>> 8271724c22765314e6947ff91487c4007960f0d9
import AdminDashboard from '../views/admin/Dashboard.vue';
import AdminShopManagement from '../views/admin/ShopManagement.vue';
import AdminUserManagement from '../views/admin/UserManagement.vue';
import AdminVehicleManagement from '../views/admin/VehicleManagement.vue';
import AdminBookingManagement from '../views/admin/BookingManagement.vue';
import AdminCouponManagement from '../views/admin/CouponManagement.vue';
import AdminCategoryManagement from '../views/admin/CategoryManagement.vue';
import AdminCityManagement from '../views/admin/CityManagement.vue';
import AdminFinancials from '../views/admin/Financials.vue';
import AdminReports from '../views/admin/Reports.vue';
import AdminSettings from '../views/admin/Settings.vue';

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
      meta: { requiresAuth: false },
    },
    {
      path: '/home',
      redirect: '/',
    },
    {
      path: '/chooserole',
      name: 'chooserole',
      component: ChooseRole,
      meta: { requiresAuth: false },
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      meta: { requiresAuth: false },
    },
    {
      path: '/login',
      name: 'login',
      component: Login,
      meta: { guest: true, allowAuthenticated: true },
    },

    {
      path: '/dashboard',
      name: 'dashboard',
      component: ShopDashboard,
      meta: { requiresAuth: true, allowedRoles: ['shop_owner', 'admin'] },
    },

    {
      path: '/view_shop',
      name: 'view_shop',
      component: UserDashboard,
      meta: { requiresAuth: false, allowedRoles: ['customer', 'user', 'admin'] },
    },
    {
      path: '/bookings/:id?',
      name: 'user-booking',
      component: UserBookings,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },
    {
      path: '/user/profile',
      name: 'user-profile',
      component: SettingUser,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },
    {
      path: '/promotions',
      name: 'promotions',
      component: PromotionView,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },
    {
      path: '/settings',
      name: 'settings',
      component: SettingUser,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },

    {
      path: '/admin',
      component: AdminLayout,
      meta: { requiresAuth: true, allowedRoles: ['admin'] },
      children: [
        { path: '', redirect: '/admin/dashboard' },
        { path: 'dashboard', name: 'admin-dashboard', component: AdminDashboard },
        { path: 'shops', name: 'admin-shops', component: AdminShopManagement },
        { path: 'users', name: 'admin-users', component: AdminUserManagement },
        { path: 'vehicles', name: 'admin-vehicles', component: AdminVehicleManagement },
        { path: 'bookings', name: 'admin-bookings', component: AdminBookingManagement },
        { path: 'coupons', name: 'admin-coupons', component: AdminCouponManagement },
        { path: 'categories', name: 'admin-categories', component: AdminCategoryManagement },
        { path: 'cities', name: 'admin-cities', component: AdminCityManagement },
        { path: 'financials', name: 'admin-financials', component: AdminFinancials },
        { path: 'reports', name: 'admin-reports', component: AdminReports },
        { path: 'settings', name: 'admin-settings', component: AdminSettings },
      ],
    },

    {
      path: '/admin/users',
      name: 'admin-users',
      component: () => import('../views/admin/User_management.vue'),
      meta: { requiresAuth: true, allowedRoles: ['admin'] }
    },
    {
      path: '/vehicles',
      name: 'vehicles-by-shop',
      component: VehiclesByShop,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },
    {
      path: '/vehicles/:id',
      name: 'vehicle-detail',
      component: ViewDetail,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'shop_owner', 'admin'] },
    },
    {
      path: '/shop/:id/vehicles',
      name: 'shop-vehicles',
      component: ShopVehicles,
      meta: { requiresAuth: true, allowedRoles: ['customer', 'user', 'admin'] },
    },
  ],
});

// Navigation guard to check authentication and authorization
router.beforeEach((to, from, next) => {
  const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
  const isGuestOnly = to.matched.some((record) => record.meta.guest);
  const allowedRoles = to.matched.find((record) => record.meta.allowedRoles)?.meta.allowedRoles || [];
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
    const allowAuthenticated = to.matched.some((record) => record.meta.allowAuthenticated);
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
