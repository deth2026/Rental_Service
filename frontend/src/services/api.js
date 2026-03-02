import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  }
});

// Vehicle API calls
export const vehicleApi = {
  getAll: () => api.get('/vehicles'),
  getById: (id) => api.get(`/vehicles/${id}`),
  create: (data) => api.post('/vehicles', data),
  update: (id, data) => api.put(`/vehicles/${id}`, data),
  delete: (id) => api.delete(`/vehicles/${id}`)
};

// Shop API calls
export const shopApi = {
  getAll: () => api.get('/shops'),
  getById: (id) => api.get(`/shops/${id}`),
  create: (data) => api.post('/shops', data),
  update: (id, data) => api.put(`/shops/${id}`, data),
  delete: (id) => api.delete(`/shops/${id}`)
};

// City API calls
export const cityApi = {
  getAll: () => api.get('/cities')
};

// Payment API calls
export const paymentApi = {
  getAll: () => api.get('/payments'),
  getById: (id) => api.get(`/payments/${id}`),
  create: (data) => api.post('/payments', data),
  update: (id, data) => api.put(`/payments/${id}`, data),
  delete: (id) => api.delete(`/payments/${id}`)
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

// Loyalty Points API calls
export const loyaltyPointApi = {
  getAll: () => api.get('/loyalty-points'),
  getById: (id) => api.get(`/loyalty-points/${id}`),
  create: (data) => api.post('/loyalty-points', data),
  update: (id, data) => api.put(`/loyalty-points/${id}`, data),
  delete: (id) => api.delete(`/loyalty-points/${id}`)
};

export default api;
