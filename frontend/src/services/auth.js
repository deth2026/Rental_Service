import api from './api';

export const setSession = ({ token, user }) => {
  if (token) {
    localStorage.setItem('token', token);
    localStorage.setItem('auth_token', token);
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
  const registerPaths = ['/register', '/users/register', '/auth/register'];
  let lastError = null;

  for (const path of registerPaths) {
    try {
      const { data } = await api.post(path, payload);
      setSession({ token: data?.token, user: data?.user });
      return data;
    } catch (error) {
      lastError = error;
      const status = error?.response?.status;
      // If there's no response (network/CORS/proxy issues), try fallbacks below.
      if (status && status !== 404 && status !== 405 && status !== 500) {
        throw error;
      }
    }
  }

  const directBases = ['/api', 'http://127.0.0.1:8000/api', 'http://localhost:8000/api'];
  for (const base of directBases) {
    for (const path of registerPaths) {
      try {
        const response = await fetch(`${base}${path}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const message =
            data?.message ||
            (data?.errors ? Object.values(data.errors)?.[0]?.[0] : '') ||
            `Registration failed (${response.status})`;
          throw new Error(message);
        }

        setSession({ token: data?.token, user: data?.user });
        return data;
      } catch (error) {
        lastError = error;
      }
    }
  }

  throw lastError || new Error('Registration request failed');
};

export const loginUser = async (payload) => {
  const loginPaths = ['/login', '/users/login', '/auth/login'];
  let lastError = null;

  for (const path of loginPaths) {
    try {
      const { data } = await api.post(path, payload);
      setSession({ token: data?.token, user: data?.user });
      return data;
    } catch (error) {
      lastError = error;
      const status = error?.response?.status;
      // Retry only when endpoint may not exist, or when there's no response at all.
      if (status && status !== 404 && status !== 405 && status !== 500) {
        throw error;
      }
    }
  }

  // Fallback for local dev when Vite proxy is unavailable/misconfigured.
  const directBases = ['/api', 'http://127.0.0.1:8000/api', 'http://localhost:8000/api'];
  for (const base of directBases) {
    for (const path of loginPaths) {
      try {
        const response = await fetch(`${base}${path}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
          },
          body: JSON.stringify(payload),
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const message =
            data?.message ||
            (data?.errors ? Object.values(data.errors)?.[0]?.[0] : '') ||
            `Login failed (${response.status})`;
          throw new Error(message);
        }

        setSession({ token: data?.token, user: data?.user });
        return data;
      } catch (error) {
        lastError = error;
      }
    }
  }

  throw lastError || new Error('Login request failed');
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
  localStorage.removeItem('auth_token');
  localStorage.removeItem('user');
  delete api.defaults.headers.common.Authorization;
};
