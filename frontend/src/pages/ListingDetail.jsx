import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import messagingApi from '../api/messagingApi';
import Navbar from '../components/Navbar';
import { Container, Card, Badge, Avatar, Button } from '../components/ui';

/**
 * ListingDetail Page
 * 
 * Detailed view of a single listing with images, seller info, and contact options.
 * Uses design system components for consistent styling.
 */

const ListingDetail = () => {
  const { id } = useParams();
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [listing, setListing] = useState(null);
  const [images, setImages] = useState([]);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [contacting, setContacting] = useState(false);

  useEffect(() => {
    fetchListing();
  }, [id]);

  const fetchListing = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await listingApi.getById(id);
      setListing(data);
      
      // Backend'den images gelirse kullan (şimdilik boş array)
      // İleride ListingResponse'a images eklediğinde otomatik çalışacak
      setImages(data.images || []);
    } catch (err) {
      setError('İlan yüklenirken bir hata oluştu');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleNextImage = () => {
    setCurrentImageIndex((prev) => (prev + 1) % images.length);
  };

  const handlePrevImage = () => {
    setCurrentImageIndex((prev) => (prev - 1 + images.length) % images.length);
  };

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  const handleContact = async () => {
    if (contacting) return;
    try {
      setContacting(true);
      const conversation = await messagingApi.createConversation(parseInt(id));
      navigate(`/messages/${conversation.id}`);
    } catch (err) {
      console.error('Failed to create conversation:', err);
      setError('Mesajlaşma başlatılamadı.');
    } finally {
      setContacting(false);
    }
  };

  const formatPrice = (price, currency) => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  };

  const getStatusText = (status) => {
    const statusMap = {
      'draft': 'Taslak',
      'active': 'Aktif',
      'sold': 'Satıldı',
      'deleted': 'Silindi'
    };
    return statusMap[status] || status;
  };

  const getStatusClass = (status) => {
    return `status-${status}`;
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="listings" />
        <Container className="py-8">
          <div className="flex justify-center items-center py-20">
            <div className="text-slate-600">Yükleniyor...</div>
          </div>
        </Container>
      </div>
    );
  }

  if (error || !listing) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="listings" />
        <Container className="py-8">
          <div className="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">
            {error || 'İlan bulunamadı'}
          </div>
          <Button
            variant="secondary"
            onClick={() => navigate('/listings')}
          >
            ← İlanlara Dön
          </Button>
        </Container>
      </div>
    );
  }

  const getStatusVariant = (status) => {
    const variants = {
      active: 'success',
      draft: 'warning',
      sold: 'default',
      deleted: 'danger',
    };
    return variants[status] || 'default';
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="listings" />

      <Container className="py-8">
        {/* Back Button */}
        <Button
          variant="secondary"
          onClick={() => navigate('/listings')}
          className="mb-6"
        >
          ← İlanlara Dön
        </Button>

        {/* Content Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Images Section - Left Side (2/3 on large screens) */}
          <div className="lg:col-span-2">
            {images.length > 0 ? (
              <Card padding="none" className="overflow-hidden">
                {/* Main Image */}
                <div className="relative aspect-[4/3] bg-slate-100">
                  <img
                    src={images[currentImageIndex].url}
                    alt={`${listing.title} - Resim ${currentImageIndex + 1}`}
                    className="w-full h-full object-cover"
                  />
                  {images.length > 1 && (
                    <>
                      <button
                        onClick={handlePrevImage}
                        className="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-colors"
                        aria-label="Önceki resim"
                      >
                        ‹
                      </button>
                      <button
                        onClick={handleNextImage}
                        className="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 bg-black/50 hover:bg-black/70 text-white rounded-full flex items-center justify-center transition-colors"
                        aria-label="Sonraki resim"
                      >
                        ›
                      </button>
                      <div className="absolute bottom-4 right-4 px-3 py-1 bg-black/60 text-white text-sm rounded-full">
                        {currentImageIndex + 1} / {images.length}
                      </div>
                    </>
                  )}
                </div>
                {/* Thumbnails */}
                {images.length > 1 && (
                  <div className="p-4 flex gap-2 overflow-x-auto">
                    {images.map((image, index) => (
                      <button
                        key={image.id}
                        onClick={() => setCurrentImageIndex(index)}
                        className={`flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all ${
                          index === currentImageIndex
                            ? 'border-primary-500 ring-2 ring-primary-200'
                            : 'border-slate-200 hover:border-slate-300'
                        }`}
                      >
                        <img
                          src={image.url}
                          alt={`Thumbnail ${index + 1}`}
                          className="w-full h-full object-cover"
                        />
                      </button>
                    ))}
                  </div>
                )}
              </Card>
            ) : (
              <Card padding="lg" className="text-center">
                <div className="text-6xl mb-4">📦</div>
                <p className="text-slate-600 mb-4">Henüz Resim Eklenmemiş</p>
                {listing && user?.id === (listing.sellerId || listing.seller_id) && (
                  <Button
                    variant="primary"
                    onClick={() => navigate(`/listings/${id}/images`)}
                  >
                    📸 Resim Ekle
                  </Button>
                )}
              </Card>
            )}
          </div>

          {/* Info Section - Right Side (1/3 on large screens) */}
          <div className="lg:col-span-1 space-y-6">
            {/* Main Info Card */}
            <Card padding="md">
              <div className="flex items-start justify-between mb-4">
                <h1 className="text-2xl font-semibold text-slate-800 flex-1 pr-2">
                  {listing.title}
                </h1>
                <Badge variant={getStatusVariant(listing.status)}>
                  {getStatusText(listing.status)}
                </Badge>
              </div>

              <div className="text-3xl font-bold text-primary-600 mb-6">
                {formatPrice(listing.price, listing.currency)}
              </div>

              {/* Meta Info */}
              <div className="space-y-3 pb-6 border-b border-slate-200">
                {listing.location && (
                  <div className="flex items-center text-sm text-slate-600">
                    <span className="mr-2">📍</span>
                    <span className="font-medium mr-2">Konum:</span>
                    <span>{listing.location}</span>
                  </div>
                )}
                {(listing.categoryId || listing.categoryName) && (
                  <div className="flex items-center text-sm text-slate-600">
                    <span className="mr-2">🏷️</span>
                    <span className="font-medium mr-2">Kategori:</span>
                    <span>{listing.categoryName || `Kategori #${listing.categoryId}`}</span>
                  </div>
                )}
                <div className="flex items-center text-sm text-slate-600">
                  <span className="mr-2">📅</span>
                  <span className="font-medium mr-2">Yayınlanma:</span>
                  <span>{formatDate(listing.createdAt || listing.created_at)}</span>
                </div>
                {(listing.createdAt || listing.created_at) !== (listing.updatedAt || listing.updated_at) && (
                  <div className="flex items-center text-sm text-slate-600">
                    <span className="mr-2">🔄</span>
                    <span className="font-medium mr-2">Güncellenme:</span>
                    <span>{formatDate(listing.updatedAt || listing.updated_at)}</span>
                  </div>
                )}
              </div>

              {/* Description */}
              <div className="pt-6">
                <h2 className="text-lg font-semibold text-slate-800 mb-3">Açıklama</h2>
                <p className="text-slate-600 text-sm leading-relaxed whitespace-pre-wrap">
                  {listing.description}
                </p>
              </div>
            </Card>

            {/* Seller Card */}
            <Card padding="md">
              <h2 className="text-lg font-semibold text-slate-800 mb-4">Satıcı Bilgileri</h2>
              
              <div 
                onClick={() => navigate(`/users/${listing.sellerId || listing.seller_id}`)}
                className="flex items-center space-x-3 mb-4 cursor-pointer hover:bg-slate-50 -mx-2 px-2 py-2 rounded-lg transition-colors"
              >
                <Avatar 
                  name={listing.sellerName || listing.seller_name || `Satıcı ${listing.sellerId || listing.seller_id}`}
                  size="md"
                />
                <div className="flex-1 min-w-0">
                  <h3 className="font-semibold text-slate-800 truncate hover:text-primary-600 transition-colors">
                    {listing.sellerName || listing.seller_name || `Satıcı #${listing.sellerId || listing.seller_id}`}
                  </h3>
                  <p className="text-xs text-slate-500">Satıcı ID: #{listing.sellerId || listing.seller_id}</p>
                </div>
                <span className="text-slate-400">→</span>
              </div>

              {listing.status === 'active' && user?.id !== (listing.sellerId || listing.seller_id) && (
                <div className="space-y-2">
                  <Button 
                    variant="primary" 
                    className="w-full"
                    onClick={handleContact}
                    disabled={contacting}
                  >
                    {contacting ? '⏳ Başlatılıyor...' : '💬 Mesaj Gönder'}
                  </Button>
                </div>
              )}
            </Card>
          </div>
        </div>
      </Container>
    </div>
  );
};

export default ListingDetail;

