import axios from 'axios';

// Auth service URL (separate microservice) - append /auth path
const AUTH_SERVICE_URL = import.meta.env.VITE_AUTH_SERVICE_URL 
  ? `${import.meta.env.VITE_AUTH_SERVICE_URL}/auth`
  : 'http://localhost:8001/auth';

// Main API URL (monolith) - already includes /api in the path
const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api`
  : 'http://localhost:8000/api';

// Get token from localStorage
const getToken = () => localStorage.getItem('token');

// Set token in localStorage
const setToken = (token) => localStorage.setItem('token', token);

// Remove token from localStorage
const removeToken = () => localStorage.removeItem('token');

// Create axios instance for auth service
const authClient = axios.create({
  baseURL: AUTH_SERVICE_URL,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Create axios instance for main API with JWT
const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add JWT token to requests
apiClient.interceptors.request.use((config) => {
  const token = getToken();
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const authApi = {
  register: async (userData) => {
    const response = await authClient.post('/register', userData);
    if (response.data.token) {
      setToken(response.data.token);
    }
    return response.data;
  },

  login: async (credentials) => {
    const response = await authClient.post('/login', credentials);
    if (response.data.token) {
      setToken(response.data.token);
    }
    return response.data;
  },

  logout: async () => {
    removeToken();
    return { message: 'Logged out successfully' };
  },

  me: async () => {
    const response = await apiClient.get('/me');
    return response.data;
  },

  getToken,
  setToken,
  removeToken,
  isAuthenticated: () => !!getToken(),
};

export default authApi;
