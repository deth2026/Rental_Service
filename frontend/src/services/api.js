import axios from 'axios';

const api = axios.create({
  baseURL: '/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

const getStoredToken = () => {
  return localStorage.getItem('auth_token') || localStorage.getItem('token') || '';
};

api.interceptors.request.use((config) => {
  const nextConfig = { ...config };
  const authToken = getStoredToken();

  // Log for debugging
  console.log('API Interceptor: Token from localStorage:', authToken ? 'present' : 'missing');

  if (authToken) {
    nextConfig.headers = nextConfig.headers || {};
    nextConfig.headers.Authorization = `Bearer ${authToken}`;
    console.log('API Interceptor: Authorization header set');
  } else {
    console.log('API Interceptor: No token found, skipping Authorization header');
  }

  // Let browser set correct multipart boundary automatically.
  if (typeof FormData !== 'undefined' && nextConfig.data instanceof FormData && nextConfig.headers) {
    delete nextConfig.headers['Content-Type'];
  }

  return nextConfig;
});

// Vehicle API calls
export const vehicleApi = {
  getAll: (params = {}) => {
    const queryString = new URLSearchParams(params).toString();
    const url = queryString ? `/vehicles?${queryString}` : '/vehicles';
    return api.get(url);
  },
  getById: (id) => api.get(`/vehicles/${id}`),
  create: (data) => api.post('/vehicles', data),
  update: (id, data) => api.put(`/vehicles/${id}`, data),
  delete: (id) => api.delete(`/vehicles/${id}`)
};

// Shop API calls
export const shopApi = {
  getAll: (params = {}) => api.get('/shops', { params }),
  getById: (id) => api.get(`/shops/${id}`),
  create: (data) => api.post('/shops', data),
  update: (id, data) => {
    if (typeof FormData !== 'undefined' && data instanceof FormData) {
      data.append('_method', 'PUT');
      return api.post(`/shops/${id}`, data);
    }
    return api.put(`/shops/${id}`, data);
  },
  delete: (id) => api.delete(`/shops/${id}`)
};

// City API calls
export const cityApi = {
  getAll: (params = {}) => api.get('/cities', { params })
};

// Category API calls
export const categoryApi = {
  getAll: () => api.get('/categories'),
  getById: (id) => api.get(`/categories/${id}`),
  create: (data) => api.post('/categories', data),
  update: (id, data) => api.put(`/categories/${id}`, data),
  delete: (id) => api.delete(`/categories/${id}`)
};

// Payment API calls
export const paymentApi = {
  getAll: () => api.get('/payments'),
  getById: (id) => api.get(`/payments/${id}`),
  create: (data) => api.post('/payments', data),
  update: (id, data) => api.put(`/payments/${id}`, data),
  delete: (id) => api.delete(`/payments/${id}`)
};

export const userApi = {
  getAll: (params = {}) => api.get('/users', { params }),
  create: (payload) => api.post('/users', payload),
  update: (id, payload) => api.put(`/users/${id}`, payload),
  delete: (id) => api.delete(`/users/${id}`)
};

// Coupon API calls
export const couponApi = {
  getAll: () => api.get('/coupons'),
  getById: (id) => api.get(`/coupons/${id}`),
  create: (data) => api.post('/coupons', data),
  update: (id, data) => api.put(`/coupons/${id}`, data),
  delete: (id) => api.delete(`/coupons/${id}`)
};

// Feedback API calls
export const feedbackApi = {
  getAll: () => api.get('/feedback'),
  getById: (id) => api.get(`/feedback/${id}`),
  create: (data) => api.post('/feedback', data),
  update: (id, data) => api.put(`/feedback/${id}`, data),
  delete: (id) => api.delete(`/feedback/${id}`)
};

// Histories (Activity) API calls
export const historiesApi = {
  getAll: () => api.get('/histories'),
  getById: (id) => api.get(`/histories/${id}`),
  create: (data) => api.post('/histories', data),
  update: (id, data) => api.put(`/histories/${id}`, data),
  delete: (id) => api.delete(`/histories/${id}`)
};

// Loyalty Points API calls
export const loyaltyPointApi = {
  getAll: () => api.get('/loyalty-points'),
  getById: (id) => api.get(`/loyalty-points/${id}`),
  create: (data) => api.post('/loyalty-points', data),
  update: (id, data) => api.put(`/loyalty-points/${id}`, data),
  delete: (id) => api.delete(`/loyalty-points/${id}`)
};

// Bookings API calls
export const bookingApi = {
  getAll: () => api.get('/bookings'),
  getById: (id) => api.get(`/bookings/${id}`),
  create: (data) => api.post('/bookings', data),
  update: (id, data) => api.put(`/bookings/${id}`, data),
  delete: (id) => api.delete(`/bookings/${id}`),
  getMyBookings: () => api.get('/my-bookings')
};

// Rating API calls
export const ratingApi = {
  getVehicleRatings: () => api.get('/vehicle-ratings'),
  getVehicleRatingsSummary: () => api.get('/vehicle-ratings-summary'),
  create: (bookingId, data) => api.post(`/bookings/${bookingId}/rating`, data)
};

export default api;
