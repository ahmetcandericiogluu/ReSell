import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api/listings`
  : '/api/listings';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  withCredentials: true,
  headers: {
    'Content-Type': 'application/json',
  }
});

const listingApi = {
  create: async (listingData) => {
    const response = await apiClient.post('', listingData);
    return response.data;
  },

  getAll: async (params = {}) => {
    const response = await apiClient.get('', { params });
    return response.data;
  },

  getMyListings: async () => {
    const response = await apiClient.get('/me');
    return response.data;
  },

  getById: async (id) => {
    const response = await apiClient.get(`/${id}`);
    return response.data;
  },

  update: async (id, listingData) => {
    const response = await apiClient.put(`/${id}`, listingData);
    return response.data;
  },

  delete: async (id) => {
    const response = await apiClient.delete(`/${id}`);
    return response.data;
  }
};

export default listingApi;

