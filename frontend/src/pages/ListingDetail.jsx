import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import './ListingDetail.css';

const ListingDetail = () => {
  const { id } = useParams();
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [listing, setListing] = useState(null);
  const [images, setImages] = useState([]);
  const [currentImageIndex, setCurrentImageIndex] = useState(0);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

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
      <div className="listing-detail-container">
        <div className="loading">Yükleniyor...</div>
      </div>
    );
  }

  if (error || !listing) {
    return (
      <div className="listing-detail-container">
        <div className="error-message">{error || 'İlan bulunamadı'}</div>
        <button onClick={() => navigate('/listings')} className="btn-back">
          ← İlanlara Dön
        </button>
      </div>
    );
  }

  return (
    <div className="listing-detail-container">
      <header className="listing-detail-header">
        <div className="header-content">
          <h1>🛍️ ReSell</h1>
          <nav className="header-nav">
            <button onClick={() => navigate('/dashboard')} className="nav-link">Ana Sayfa</button>
            <button onClick={() => navigate('/listings')} className="nav-link">İlanlar</button>
            <button onClick={() => navigate('/my-listings')} className="nav-link">İlanlarım</button>
            <div className="user-menu">
              <span>{user?.name || user?.email}</span>
              <button onClick={handleLogout} className="btn-logout">Çıkış</button>
            </div>
          </nav>
        </div>
      </header>

      <main className="listing-detail-main">
        <button onClick={() => navigate('/listings')} className="btn-back">
          ← İlanlara Dön
        </button>

        <div className="listing-detail-content">
          <div className="listing-detail-images">
            {images.length > 0 ? (
              <div className="image-gallery">
                <div className="main-image">
                  <img 
                    src={images[currentImageIndex].url} 
                    alt={`${listing.title} - Resim ${currentImageIndex + 1}`}
                  />
                  {images.length > 1 && (
                    <>
                      <button 
                        onClick={handlePrevImage} 
                        className="gallery-btn gallery-btn-prev"
                        aria-label="Önceki resim"
                      >
                        ‹
                      </button>
                      <button 
                        onClick={handleNextImage} 
                        className="gallery-btn gallery-btn-next"
                        aria-label="Sonraki resim"
                      >
                        ›
                      </button>
                      <div className="image-counter">
                        {currentImageIndex + 1} / {images.length}
                      </div>
                    </>
                  )}
                </div>
                {images.length > 1 && (
                  <div className="thumbnail-strip">
                    {images.map((image, index) => (
                      <div 
                        key={image.id}
                        className={`thumbnail ${index === currentImageIndex ? 'active' : ''}`}
                        onClick={() => setCurrentImageIndex(index)}
                      >
                        <img src={image.url} alt={`Thumbnail ${index + 1}`} />
                      </div>
                    ))}
                  </div>
                )}
              </div>
            ) : (
              <div className="main-image-placeholder">
                <span className="image-icon">📦</span>
                <p>Henüz Resim Eklenmemiş</p>
                {listing && user?.id === listing.seller_id && (
                  <button 
                    onClick={() => navigate(`/listings/${id}/images`)}
                    className="btn-add-images"
                  >
                    📸 Resim Ekle
                  </button>
                )}
              </div>
            )}
          </div>

          <div className="listing-detail-info">
            <div className="listing-detail-header-info">
              <h1 className="listing-title">{listing.title}</h1>
              <div className={`listing-status-badge ${getStatusClass(listing.status)}`}>
                {getStatusText(listing.status)}
              </div>
            </div>

            <div className="listing-price-section">
              <div className="listing-price-large">
                {formatPrice(listing.price, listing.currency)}
              </div>
            </div>

            <div className="listing-meta-section">
              {listing.location && (
                <div className="meta-item">
                  <span className="meta-icon">📍</span>
                  <span className="meta-label">Konum:</span>
                  <span className="meta-value">{listing.location}</span>
                </div>
              )}
              {listing.category_id && (
                <div className="meta-item">
                  <span className="meta-icon">🏷️</span>
                  <span className="meta-label">Kategori:</span>
                  <span className="meta-value">Kategori #{listing.category_id}</span>
                </div>
              )}
              <div className="meta-item">
                <span className="meta-icon">📅</span>
                <span className="meta-label">Yayınlanma:</span>
                <span className="meta-value">{formatDate(listing.created_at)}</span>
              </div>
              {listing.created_at !== listing.updated_at && (
                <div className="meta-item">
                  <span className="meta-icon">🔄</span>
                  <span className="meta-label">Güncellenme:</span>
                  <span className="meta-value">{formatDate(listing.updated_at)}</span>
                </div>
              )}
            </div>

            <div className="listing-description-section">
              <h2>Açıklama</h2>
              <p className="listing-description">{listing.description}</p>
            </div>

            <div className="listing-seller-section">
              <h2>Satıcı Bilgileri</h2>
              <div className="seller-info">
                <div className="seller-avatar">👤</div>
                <div className="seller-details">
                  <h3>{listing.seller_name}</h3>
                  <p className="seller-id">Satıcı ID: #{listing.seller_id}</p>
                </div>
              </div>
              {listing.status === 'active' && (
                <div className="contact-buttons">
                  <button className="btn-contact">
                    💬 Mesaj Gönder
                  </button>
                  <button className="btn-contact-secondary">
                    📞 İletişim Bilgilerini Gör
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default ListingDetail;

