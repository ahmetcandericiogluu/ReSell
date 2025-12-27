import { useState, useEffect, useCallback } from 'react';
import { useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { Container, Card, Avatar, Badge } from '../components/ui';
import { useAuth } from '../context/AuthContext';
import { useToast } from '../context/ToastContext';
import { useUserChannel } from '../hooks/usePusher';
import messagingApi from '../api/messagingApi';

/**
 * Messages Page - Conversations List
 */
const Messages = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const { showApiError } = useToast();
  const [conversations, setConversations] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  // Handle realtime new message notification
  const handleNewMessage = useCallback((data) => {
    // Refresh conversation list when new message arrives
    console.log('New message received, refreshing list...', data);
    fetchConversations();
  }, []);

  // Subscribe to user's private channel for notifications
  useUserChannel(user?.id, handleNewMessage);

  useEffect(() => {
    fetchConversations();
  }, []);

  const fetchConversations = async () => {
    try {
      setLoading(true);
      const data = await messagingApi.getConversations();
      setConversations(data);
    } catch (err) {
      console.error('Failed to fetch conversations:', err);
      setError('Mesajlar yüklenirken bir hata oluştu.');
      showApiError(err, 'Mesajlar yüklenemedi');
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now - date;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Az önce';
    if (diffMins < 60) return `${diffMins} dk önce`;
    if (diffHours < 24) return `${diffHours} saat önce`;
    if (diffDays < 7) return `${diffDays} gün önce`;
    return date.toLocaleDateString('tr-TR');
  };

  const getTotalUnread = () => {
    return conversations.reduce((sum, conv) => sum + (conv.unread_count || 0), 0);
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="messages" />

      <Container className="py-8">
        {/* Header */}
        <div className="flex items-center justify-between mb-6">
          <div>
            <h1 className="text-2xl font-bold text-slate-800">Mesajlarım</h1>
            <p className="text-slate-600">
              {conversations.length} konuşma
              {getTotalUnread() > 0 && (
                <span className="ml-2 text-primary-600 font-medium">
                  ({getTotalUnread()} okunmamış)
                </span>
              )}
            </p>
          </div>
        </div>

        {/* Error */}
        {error && (
          <Card padding="md" className="mb-6 bg-red-50 border-red-200">
            <p className="text-red-600">{error}</p>
          </Card>
        )}

        {/* Loading */}
        {loading ? (
          <div className="space-y-4">
            {[1, 2, 3].map((i) => (
              <Card key={i} padding="md" className="animate-pulse">
                <div className="flex items-center space-x-4">
                  <div className="w-12 h-12 bg-slate-200 rounded-full" />
                  <div className="flex-1">
                    <div className="h-4 bg-slate-200 rounded w-1/3 mb-2" />
                    <div className="h-3 bg-slate-200 rounded w-2/3" />
                  </div>
                </div>
              </Card>
            ))}
          </div>
        ) : conversations.length === 0 ? (
          /* Empty State */
          <Card padding="lg" className="text-center">
            <div className="text-6xl mb-4">💬</div>
            <h3 className="text-xl font-semibold text-slate-800 mb-2">
              Henüz mesajınız yok
            </h3>
            <p className="text-slate-600 mb-4">
              İlanlara göz atın ve satıcılarla iletişime geçin.
            </p>
            <button
              onClick={() => navigate('/listings')}
              className="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
            >
              İlanlara Göz At
            </button>
          </Card>
        ) : (
          /* Conversations List */
          <div className="space-y-3">
            {conversations.map((conversation) => (
              <Card
                key={conversation.id}
                padding="md"
                className={`cursor-pointer hover:shadow-md transition-all ${
                  conversation.unread_count > 0 ? 'border-l-4 border-l-primary-500 bg-primary-50/30' : ''
                }`}
                onClick={() => navigate(`/messages/${conversation.id}`)}
              >
                <div className="flex items-center space-x-4">
                  {/* Avatar */}
                  <Avatar 
                    name={conversation.other_user_name || 'Kullanıcı'} 
                    size="md"
                  />

                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <div className="flex items-center justify-between mb-1">
                      <h3 className="font-semibold text-slate-800 truncate">
                        {conversation.listing_title || 'İlan'}
                      </h3>
                      <span className="text-xs text-slate-500 whitespace-nowrap ml-2">
                        {formatDate(conversation.updated_at)}
                      </span>
                    </div>
                    <p className="text-sm text-slate-600 truncate">
                      <span className="text-primary-600 font-medium mr-1">
                        {conversation.other_user_name || 'Kullanıcı'}:
                      </span>
                      {conversation.last_message?.content || 'Henüz mesaj yok'}
                    </p>
                  </div>

                  {/* Unread Badge */}
                  {conversation.unread_count > 0 && (
                    <Badge variant="primary" size="sm">
                      {conversation.unread_count}
                    </Badge>
                  )}
                </div>
              </Card>
            ))}
          </div>
        )}
      </Container>
    </div>
  );
};

export default Messages;

