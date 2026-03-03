import axios from "axios";

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || "/api",
  headers: {
    "Content-Type": "application/json",
  },
});

export async function loginUser(payload) {
  const response = await api.post("/users/login", payload);
  const data = response.data;
  const token = data?.token || data?.access_token;
  const user = data?.user;

  if (token) {
    localStorage.setItem("auth_token", token);
    localStorage.setItem("token", token);
  }

  if (user) {
    localStorage.setItem("user", JSON.stringify(user));
  }

  return data;
}

export async function registerUser(payload) {
  const response = await api.post("/users/register", payload);
  return response.data;
}
