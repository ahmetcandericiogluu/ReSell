import { useState, useEffect, useRef, useCallback } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { Container, Card, Avatar } from '../components/ui';
import { useAuth } from '../context/AuthContext';
import messagingApi from '../api/messagingApi';
import { useConversationChannel } from '../hooks/usePusher';

/**
 * Chat Page - Conversation Detail with Realtime Support
 */
const Chat = () => {
  const { id } = useParams();
  const navigate = useNavigate();
  const { user } = useAuth();
  const messagesEndRef = useRef(null);
  
  const [conversation, setConversation] = useState(null);
  const [messages, setMessages] = useState([]);
  const [newMessage, setNewMessage] = useState('');
  const [loading, setLoading] = useState(true);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState('');

  // Realtime message handler - dedupe by message ID
  const handleNewMessage = useCallback((message) => {
    setMessages((prev) => {
      // Check if message already exists (dedupe)
      const exists = prev.some((m) => m.id === message.id);
      if (exists) {
        return prev;
      }
      return [...prev, message];
    });
  }, []);

  // Subscribe to realtime channel
  const { isConnected: realtimeConnected } = useConversationChannel(id, handleNewMessage);

  useEffect(() => {
    fetchConversation();
    // Mark as read when opening
    messagingApi.markAsRead(id).catch(() => {});
  }, [id]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  const fetchConversation = async () => {
    try {
      setLoading(true);
      const data = await messagingApi.getConversation(id);
      setConversation(data.conversation);
      setMessages(data.messages || []);
    } catch (err) {
      console.error('Failed to fetch conversation:', err);
      setError('Konuşma yüklenirken bir hata oluştu.');
    } finally {
      setLoading(false);
    }
  };

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  const handleSendMessage = async (e) => {
    e.preventDefault();
    if (!newMessage.trim() || sending) return;

    try {
      setSending(true);
      const message = await messagingApi.sendMessage(id, newMessage.trim());
      setMessages((prev) => [...prev, message]);
      setNewMessage('');
    } catch (err) {
      console.error('Failed to send message:', err);
      setError('Mesaj gönderilemedi.');
    } finally {
      setSending(false);
    }
  };

  const formatTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) return 'Bugün';
    if (date.toDateString() === yesterday.toDateString()) return 'Dün';
    return date.toLocaleDateString('tr-TR', { day: 'numeric', month: 'long' });
  };

  const groupMessagesByDate = () => {
    const groups = {};
    messages.forEach((msg) => {
      const dateKey = new Date(msg.created_at).toDateString();
      if (!groups[dateKey]) {
        groups[dateKey] = [];
      }
      groups[dateKey].push(msg);
    });
    return groups;
  };

  const isMyMessage = (senderId) => {
    return senderId === user?.id;
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="messages" />
        <Container className="py-8">
          <Card padding="lg" className="animate-pulse">
            <div className="h-8 bg-slate-200 rounded w-1/3 mb-4" />
            <div className="space-y-4">
              {[1, 2, 3].map((i) => (
                <div key={i} className="h-16 bg-slate-200 rounded" />
              ))}
            </div>
          </Card>
        </Container>
      </div>
    );
  }

  if (error && !conversation) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="messages" />
        <Container className="py-8">
          <Card padding="lg" className="text-center">
            <p className="text-red-600 mb-4">{error}</p>
            <button
              onClick={() => navigate('/messages')}
              className="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg hover:bg-slate-300"
            >
              Geri Dön
            </button>
          </Card>
        </Container>
      </div>
    );
  }

  const messageGroups = groupMessagesByDate();

  return (
    <div className="min-h-screen bg-slate-50 flex flex-col">
      <Navbar activePage="messages" />

      {/* Chat Header */}
      <div className="bg-white border-b border-slate-200 sticky top-0 z-10">
        <Container className="py-4">
          <div className="flex items-center space-x-4">
            <button
              onClick={() => navigate('/messages')}
              className="p-2 hover:bg-slate-100 rounded-lg transition-colors"
            >
              ← 
            </button>
            <div className="flex-1 min-w-0">
              <h1 className="font-semibold text-slate-800 truncate">
                {conversation?.listing_title || 'Konuşma'}
              </h1>
              <p className="text-sm text-slate-500 flex items-center">
                {conversation?.other_user_name || 'Kullanıcı'}
                {realtimeConnected && (
                  <span className="ml-2 w-2 h-2 bg-green-500 rounded-full" title="Canlı bağlantı" />
                )}
              </p>
            </div>
            {conversation?.listing_id && (
              <button
                onClick={() => navigate(`/listings/${conversation.listing_id}`)}
                className="text-sm text-primary-600 hover:text-primary-700"
              >
                İlana Git →
              </button>
            )}
          </div>
        </Container>
      </div>

      {/* Messages */}
      <div className="flex-1 overflow-y-auto">
        <Container className="py-4">
          {messages.length === 0 ? (
            <div className="text-center py-12">
              <div className="text-4xl mb-4">💬</div>
              <p className="text-slate-500">Henüz mesaj yok. İlk mesajı gönderin!</p>
            </div>
          ) : (
            <div className="space-y-6">
              {Object.entries(messageGroups).map(([dateKey, msgs]) => (
                <div key={dateKey}>
                  {/* Date Separator */}
                  <div className="flex items-center justify-center mb-4">
                    <span className="px-3 py-1 bg-slate-200 text-slate-600 text-xs rounded-full">
                      {formatDate(msgs[0].created_at)}
                    </span>
                  </div>

                  {/* Messages */}
                  <div className="space-y-3">
                    {msgs.map((message) => (
                      <div
                        key={message.id}
                        className={`flex ${isMyMessage(message.sender_id) ? 'justify-end' : 'justify-start'}`}
                      >
                        <div
                          className={`max-w-[75%] px-4 py-2 rounded-2xl ${
                            isMyMessage(message.sender_id)
                              ? 'bg-primary-600 text-white rounded-br-md'
                              : 'bg-white text-slate-800 border border-slate-200 rounded-bl-md'
                          }`}
                        >
                          <p className="break-words">{message.content}</p>
                          <p
                            className={`text-xs mt-1 ${
                              isMyMessage(message.sender_id) ? 'text-primary-200' : 'text-slate-400'
                            }`}
                          >
                            {formatTime(message.created_at)}
                          </p>
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}
              <div ref={messagesEndRef} />
            </div>
          )}
        </Container>
      </div>

      {/* Message Input */}
      <div className="bg-white border-t border-slate-200 sticky bottom-0">
        <Container className="py-4">
          <form onSubmit={handleSendMessage} className="flex items-center space-x-3">
            <input
              type="text"
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              placeholder="Mesajınızı yazın..."
              className="flex-1 px-4 py-3 border border-slate-200 rounded-full focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              disabled={sending}
            />
            <button
              type="submit"
              disabled={!newMessage.trim() || sending}
              className="px-6 py-3 bg-primary-600 text-white rounded-full hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              {sending ? '...' : 'Gönder'}
            </button>
          </form>
        </Container>
      </div>
    </div>
  );
};

export default Chat;

