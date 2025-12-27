import { useEffect, useRef, useState, useCallback } from 'react';
import Pusher from 'pusher-js';

const MESSAGING_SERVICE_URL = import.meta.env.VITE_MESSAGING_SERVICE_URL 
  || 'https://resell-messaging-service.onrender.com';
const PUSHER_KEY = import.meta.env.VITE_PUSHER_KEY;
const PUSHER_CLUSTER = import.meta.env.VITE_PUSHER_CLUSTER || 'eu';

// Singleton Pusher instance
let pusherInstance = null;
let connectionState = 'disconnected';

const getPusherInstance = () => {
  if (pusherInstance) {
    return pusherInstance;
  }

  if (!PUSHER_KEY) {
    console.log('Pusher not configured, realtime disabled');
    return null;
  }

  const token = localStorage.getItem('token');
  if (!token) {
    console.log('No auth token, skipping Pusher connection');
    return null;
  }

  console.log('Creating new Pusher instance');
  pusherInstance = new Pusher(PUSHER_KEY, {
    cluster: PUSHER_CLUSTER,
    authEndpoint: `${MESSAGING_SERVICE_URL}/api/realtime/auth`,
    auth: {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    },
  });

  pusherInstance.connection.bind('connected', () => {
    console.log('Pusher connected');
    connectionState = 'connected';
  });

  pusherInstance.connection.bind('disconnected', () => {
    console.log('Pusher disconnected');
    connectionState = 'disconnected';
  });

  pusherInstance.connection.bind('error', (err) => {
    console.error('Pusher error:', err);
  });

  return pusherInstance;
};

/**
 * Custom hook for Pusher realtime connection
 */
export const usePusher = () => {
  const [isConnected, setIsConnected] = useState(connectionState === 'connected');
  const pusher = getPusherInstance();

  useEffect(() => {
    if (!pusher) return;

    const handleConnected = () => setIsConnected(true);
    const handleDisconnected = () => setIsConnected(false);

    pusher.connection.bind('connected', handleConnected);
    pusher.connection.bind('disconnected', handleDisconnected);

    // Check current state
    if (pusher.connection.state === 'connected') {
      setIsConnected(true);
    }

    return () => {
      pusher.connection.unbind('connected', handleConnected);
      pusher.connection.unbind('disconnected', handleDisconnected);
    };
  }, [pusher]);

  return { pusher, isConnected };
};

/**
 * Hook to subscribe to a conversation channel and receive messages
 * @param {string} conversationId - The conversation UUID
 * @param {function} onNewMessage - Callback when a new message arrives
 * @param {function} onTyping - Callback when someone starts typing
 */
export const useConversationChannel = (conversationId, onNewMessage, onTyping) => {
  const { pusher, isConnected } = usePusher();
  const channelRef = useRef(null);

  useEffect(() => {
    if (!pusher || !conversationId || !isConnected) {
      return;
    }

    const channelName = `private-conversation.${conversationId}`;
    
    console.log('Subscribing to channel:', channelName);
    channelRef.current = pusher.subscribe(channelName);

    channelRef.current.bind('pusher:subscription_succeeded', () => {
      console.log('Subscribed to:', channelName);
    });

    channelRef.current.bind('pusher:subscription_error', (error) => {
      console.error('Subscription error:', channelName, error);
    });

    // Bind to message.created event
    channelRef.current.bind('message.created', (data) => {
      console.log('Received message:', data);
      if (onNewMessage) {
        onNewMessage(data.message);
      }
    });

    // Bind to user.typing event
    channelRef.current.bind('user.typing', (data) => {
      console.log('User typing:', data);
      if (onTyping) {
        onTyping(data.user_id);
      }
    });

    return () => {
      if (channelRef.current) {
        console.log('Unsubscribing from:', channelName);
        channelRef.current.unbind_all();
        pusher.unsubscribe(channelName);
        channelRef.current = null;
      }
    };
  }, [pusher, conversationId, isConnected, onNewMessage, onTyping]);

  return { isConnected };
};

export default usePusher;

