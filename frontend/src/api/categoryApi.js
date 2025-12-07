import axios from 'axios';

// Use new Listing Service microservice for categories
const LISTING_SERVICE_URL = import.meta.env.VITE_LISTING_SERVICE_URL 
  || 'http://localhost:8082';

const categoryClient = axios.create({
  baseURL: `${LISTING_SERVICE_URL}/api/categories`,
  headers: {
    'Content-Type': 'application/json',
  }
});

const categoryApi = {
  // Get all categories
  getAll: async () => {
    const response = await categoryClient.get('');
    return response.data;
  },

  // Get category by ID
  getById: async (id) => {
    const response = await categoryClient.get(`/${id}`);
    return response.data;
  },

  // Get category by slug
  getBySlug: async (slug) => {
    const categories = await categoryApi.getAll();
    return categories.find(cat => cat.slug === slug);
  }
};

export default categoryApi;

