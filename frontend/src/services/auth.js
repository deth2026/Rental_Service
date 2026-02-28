import api from './api';

export const clearSession = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  delete api.defaults.headers.common.Authorization;
};
