import axios from 'axios';

// Use new Listing Service microservice
const LISTING_SERVICE_URL = import.meta.env.VITE_LISTING_SERVICE_URL 
  || 'https://resell-listing-service.onrender.com';

// Fallback to monolith for backward compatibility
const MONOLITH_URL = import.meta.env.VITE_API_URL 
  ? `${import.meta.env.VITE_API_URL}/api/listings`
  : 'https://resell-backend.onrender.com/api/listings';

const listingClient = axios.create({
  baseURL: `${LISTING_SERVICE_URL}/api/listings`,
  headers: {
    'Content-Type': 'application/json',
  }
});

const monolithClient = axios.create({
  baseURL: MONOLITH_URL,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add JWT token to requests
const addAuthInterceptor = (client) => {
  client.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });
};

addAuthInterceptor(listingClient);
addAuthInterceptor(monolithClient);

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

  // Get my listings using new microservice
  getMyListings: async () => {
    const response = await listingClient.get('/me');
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

  // Image operations - still use monolith (image upload not in microservice yet)
  uploadImages: async (listingId, files) => {
    const formData = new FormData();
    files.forEach(file => {
      formData.append('images[]', file);
    });

    const response = await monolithClient.post(`/${listingId}/images`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });
    return response.data;
  },

  deleteImage: async (listingId, imageId) => {
    const response = await monolithClient.delete(`/${listingId}/images/${imageId}`);
    return response.data;
  },

  getImages: async (listingId) => {
    const response = await monolithClient.get(`/${listingId}/images`);
    return response.data;
  }
};

export default listingApi;

