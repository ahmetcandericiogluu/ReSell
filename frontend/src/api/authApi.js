import axios from 'axios';

// Use environment variable for API URL, fallback to proxy in development
const API_AUTH_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api/auth`
  : '/api/auth';

const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api`
  : '/api';

// Create axios instance with credentials
const apiClient = axios.create({
  baseURL: API_AUTH_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  }
});

const authApi = {
  register: async (userData) => {
    const response = await apiClient.post('/register', userData);
    return response.data;
  },

  login: async (credentials) => {
    const response = await apiClient.post('/login', credentials);
    return response.data;
  },

  logout: async () => {
    const response = await apiClient.post('/logout');
    return response.data;
  },

  me: async () => {
    const response = await axios.get(`${API_BASE_URL}/me`, {
      withCredentials: true,
      headers: {
        'Content-Type': 'application/json',
      }
    });
    return response.data;
  }
};

export default authApi;

