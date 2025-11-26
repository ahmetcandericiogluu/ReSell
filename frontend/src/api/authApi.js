import axios from 'axios';

// Use environment variable for API URL, fallback to proxy in development
const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api/auth`
  : '/api/auth';

// Configure axios defaults
axios.defaults.withCredentials = true;

const authApi = {
  register: async (userData) => {
    const response = await axios.post(`${API_BASE_URL}/register`, userData);
    return response.data;
  },

  login: async (credentials) => {
    const response = await axios.post(`${API_BASE_URL}/login`, credentials);
    return response.data;
  },

  logout: async () => {
    const response = await axios.post(`${API_BASE_URL}/logout`);
    return response.data;
  },

  me: async () => {
    const response = await axios.get(`${API_BASE_URL}/me`);
    return response.data;
  }
};

export default authApi;

