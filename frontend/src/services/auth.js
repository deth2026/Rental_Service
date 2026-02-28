import api from './api';

export const setSession = ({ token, user }) => {
  if (token) {
    localStorage.setItem('token', token);
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
  }
  if (user) {
    localStorage.setItem('user', JSON.stringify(user));
  }
};

export const getSessionUser = () => {
  try {
    const raw = localStorage.getItem('user');
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
};

export const registerUser = async (payload) => {
  const { data } = await api.post('/auth/register', payload);
  setSession({ token: data?.token, user: data?.user });
  return data;
};

export const loginUser = async (payload) => {
  const { data } = await api.post('/auth/login', payload);
  setSession({ token: data?.token, user: data?.user });
  return data;
};

export const logoutUser = async () => {
  try {
    await api.post('/auth/logout');
  } catch {
    // Clear local session even if backend token is already invalid/expired.
  }
  clearSession();
};

export const clearSession = () => {
  localStorage.removeItem('token');
  localStorage.removeItem('user');
  delete api.defaults.headers.common.Authorization;
};
