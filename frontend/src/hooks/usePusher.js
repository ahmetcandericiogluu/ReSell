import { useEffect, useRef, useState } from 'react';
import Pusher from 'pusher-js';

const MESSAGING_SERVICE_URL = import.meta.env.VITE_MESSAGING_SERVICE_URL 
  || 'https://resell-messaging-service.onrender.com';
const PUSHER_KEY = import.meta.env.VITE_PUSHER_KEY;
const PUSHER_CLUSTER = import.meta.env.VITE_PUSHER_CLUSTER || 'eu';

/**
 * Custom hook for Pusher realtime connection
 */
export const usePusher = () => {
  const pusherRef = useRef(null);
  const [isConnected, setIsConnected] = useState(false);

  useEffect(() => {
    if (!PUSHER_KEY) {
      console.log('Pusher not configured, realtime disabled');
      return;
    }

    const token = localStorage.getItem('token');
    if (!token) {
      console.log('No auth token, skipping Pusher connection');
      return;
    }

    // Initialize Pusher
    pusherRef.current = new Pusher(PUSHER_KEY, {
      cluster: PUSHER_CLUSTER,
      authEndpoint: `${MESSAGING_SERVICE_URL}/api/realtime/auth`,
      auth: {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      },
    });

    pusherRef.current.connection.bind('connected', () => {
      console.log('Pusher connected');
      setIsConnected(true);
    });

    pusherRef.current.connection.bind('disconnected', () => {
      console.log('Pusher disconnected');
      setIsConnected(false);
    });

    pusherRef.current.connection.bind('error', (err) => {
      console.error('Pusher error:', err);
    });

    return () => {
      if (pusherRef.current) {
        pusherRef.current.disconnect();
        pusherRef.current = null;
      }
    };
  }, []);

  return {
    pusher: pusherRef.current,
    isConnected,
  };
};

/**
 * Hook to subscribe to a conversation channel and receive messages
 */
export const useConversationChannel = (conversationId, onNewMessage) => {
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

    return () => {
      if (channelRef.current) {
        console.log('Unsubscribing from:', channelName);
        channelRef.current.unbind_all();
        pusher.unsubscribe(channelName);
        channelRef.current = null;
      }
    };
  }, [pusher, conversationId, isConnected, onNewMessage]);

  return { isConnected };
};

export default usePusher;

