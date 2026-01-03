import axios from 'axios';

// Use new Listing Service microservice
const LISTING_SERVICE_URL = import.meta.env.VITE_LISTING_SERVICE_URL 
  || 'https://resell-listing-service.onrender.com';

const listingClient = axios.create({
  baseURL: `${LISTING_SERVICE_URL}/api/listings`,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add JWT token to requests
listingClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const listingApi = {
  // Create listing using new microservice
  create: async (listingData) => {
    const response = await listingClient.post('', listingData);
    return response.data;
  },

  // Get all listings using new microservice
  getAll: async (params = {}) => {
    const response = await listingClient.get('', { params });
    // New service returns {data: [...], meta: {...}}
    // Transform to match old format for backward compatibility
    return response.data.data || response.data;
  },

  // Search listings using Elasticsearch
  search: async (params = {}) => {
    const response = await listingClient.get('/search', { params });
    // Returns {data: [...], meta: {page, limit, total, totalPages}}
    return response.data;
  },

  // Get my listings using new microservice
  getMyListings: async () => {
    const response = await listingClient.get('/my-listings');
    return response.data;
  },

  // Get listings by user ID (public endpoint)
  getByUserId: async (userId) => {
    const response = await listingClient.get(`/user/${userId}`);
    return response.data;
  },

  // Get single listing using new microservice
  getById: async (id) => {
    const response = await listingClient.get(`/${id}`);
    return response.data;
  },

  // Update listing using new microservice
  update: async (id, listingData) => {
    const response = await listingClient.put(`/${id}`, listingData);
    return response.data;
  },

  // Delete listing using new microservice
  delete: async (id) => {
    const response = await listingClient.delete(`/${id}`);
    return response.data;
  },

  // Image operations - now using listing-service
  uploadImages: async (listingId, files) => {
    const formData = new FormData();
    files.forEach(file => {
      formData.append('images[]', file);
    });

    const response = await listingClient.post(`/${listingId}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  deleteImage: async (listingId, imageId) => {
    const response = await listingClient.delete(`/${listingId}/images/${imageId}`);
    return response.data;
  },

  getImages: async (listingId) => {
    const response = await listingClient.get(`/${listingId}/images`);
    return response.data;
  }
};

export default listingApi;

