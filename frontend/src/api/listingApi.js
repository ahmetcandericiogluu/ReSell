import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api/listings`
  : 'http://localhost:8000/api/listings';

const apiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add JWT token to requests
apiClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
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
  },

  // Image operations
  uploadImages: async (listingId, files) => {
    const formData = new FormData();
    files.forEach(file => {
      formData.append('images[]', file);
    });

    const response = await apiClient.post(`/${listingId}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  deleteImage: async (listingId, imageId) => {
    const response = await apiClient.delete(`/${listingId}/images/${imageId}`);
    return response.data;
  },

  getImages: async (listingId) => {
    const response = await apiClient.get(`/${listingId}/images`);
    return response.data;
  }
};

export default listingApi;

