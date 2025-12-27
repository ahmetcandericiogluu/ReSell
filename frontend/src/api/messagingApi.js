import axios from 'axios';

const MESSAGING_SERVICE_URL = import.meta.env.VITE_MESSAGING_SERVICE_URL 
  || 'https://resell-messaging-service.onrender.com';

const messagingClient = axios.create({
  baseURL: `${MESSAGING_SERVICE_URL}/api`,
  headers: {
    'Content-Type': 'application/json',
  }
});

// Add JWT token to requests
messagingClient.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

const messagingApi = {
  // Create or get existing conversation for a listing
  createConversation: async (listingId) => {
    const response = await messagingClient.post('/conversations', { listing_id: listingId });
    return response.data;
  },

  // Get all user's conversations
  getConversations: async () => {
    const response = await messagingClient.get('/conversations');
    return response.data;
  },

  // Get conversation with messages
  getConversation: async (conversationId, page = 1, limit = 50) => {
    const response = await messagingClient.get(`/conversations/${conversationId}`, {
      params: { page, limit }
    });
    return response.data;
  },

  // Send a message
  sendMessage: async (conversationId, content) => {
    const response = await messagingClient.post(`/conversations/${conversationId}/messages`, { content });
    return response.data;
  },

  // Mark conversation as read
  markAsRead: async (conversationId) => {
    const response = await messagingClient.post(`/conversations/${conversationId}/read`);
    return response.data;
  },

  // Send typing indicator
  sendTyping: async (conversationId) => {
    const response = await messagingClient.post(`/conversations/${conversationId}/typing`);
    return response.data;
  }
};

export default messagingApi;

