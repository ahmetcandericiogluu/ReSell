import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api`
  : '/api';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  }
});

const profileApi = {
  // Get current user profile
  getMe: async () => {
    const response = await apiClient.get('/me');
    return response.data;
  },

  // Get any user profile by ID
  getUserProfile: async (userId) => {
    const response = await apiClient.get(`/users/${userId}`);
    return response.data;
  },

  // Update current user profile
  updateProfile: async (data) => {
    const response = await apiClient.patch('/me', data);
    return response.data;
  },

  // Get user's listings
  getUserListings: async (userId, params = {}) => {
    const response = await axios.get(`${API_BASE_URL}/listings/users/${userId}/listings`, { 
      params,
      withCredentials: true 
    });
    return response.data;
  },

  // Get user's reviews
  getUserReviews: async (userId, params = {}) => {
    const response = await apiClient.get(`/users/${userId}/reviews`, { params });
    return response.data;
  }
};

export default profileApi;

