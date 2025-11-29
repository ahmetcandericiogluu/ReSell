import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import './Listings.css';

const MyListings = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    fetchMyListings();
  }, []);

  const fetchMyListings = async () => {
    setLoading(true);
    setError('');
    try {
      const data = await listingApi.getMyListings();
      setListings(data);
    } catch (err) {
      setError('İlanlarınız yüklenirken bir hata oluştu');
      console.error(err);
    } finally {
      setLoading(false);
    }
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

  const getStatusLabel = (status) => {
    const labels = {
      draft: '📝 Taslak',
      active: '✅ Aktif',
      sold: '✔️ Satıldı',
      deleted: '🗑️ Silindi'
    };
    return labels[status] || status;
  };

  const getStatusClass = (status) => {
    return `status-${status}`;
  };

  return (
    <div className="listings-container">
      <header className="listings-header">
        <div className="header-content">
          <h1>🛍️ ReSell</h1>
          <nav className="header-nav">
            <button onClick={() => navigate('/dashboard')} className="nav-link">Ana Sayfa</button>
            <button onClick={() => navigate('/listings')} className="nav-link">İlanlar</button>
            <button onClick={() => navigate('/my-listings')} className="nav-link active">İlanlarım</button>
            <div className="user-menu">
              <span>{user?.name || user?.email}</span>
              <button onClick={handleLogout} className="btn-logout">Çıkış</button>
            </div>
          </nav>
        </div>
      </header>

      <main className="listings-main">
        <div className="search-section">
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <h2>İlanlarım</h2>
            <button 
              onClick={() => navigate('/listings/create')} 
              className="btn-search"
            >
              ➕ Yeni İlan Ekle
            </button>
          </div>
        </div>

        {error && <div className="error-message">{error}</div>}

        {loading ? (
          <div className="loading">Yükleniyor...</div>
        ) : (
          <div className="listings-grid">
            {listings.length === 0 ? (
              <div className="no-listings">
                <p>Henüz ilan oluşturmadınız.</p>
                <button 
                  onClick={() => navigate('/listings/create')} 
                  className="btn-search"
                  style={{ marginTop: '1rem' }}
                >
                  ➕ İlk İlanınızı Oluşturun
                </button>
              </div>
            ) : (
              listings.map((listing) => (
                <div 
                  key={listing.id} 
                  className="listing-card my-listing-card"
                >
                  <div 
                    className="listing-image-placeholder"
                    onClick={() => navigate(`/listings/${listing.id}`)}
                  >
                    📦
                  </div>
                  <div className="listing-info">
                    <h3 onClick={() => navigate(`/listings/${listing.id}`)} style={{ cursor: 'pointer' }}>
                      {listing.title}
                    </h3>
                    <p className="listing-description">
                      {listing.description.substring(0, 100)}
                      {listing.description.length > 100 ? '...' : ''}
                    </p>
                    <div className="listing-meta">
                      <span className="listing-price">{formatPrice(listing.price, listing.currency)}</span>
                      {listing.location && (
                        <span className="listing-location">📍 {listing.location}</span>
                      )}
                    </div>
                    <div className="listing-footer">
                      <span className="listing-date">
                        🕐 {new Date(listing.created_at).toLocaleDateString('tr-TR')}
                      </span>
                      <span className={`listing-status ${getStatusClass(listing.status)}`}>
                        {getStatusLabel(listing.status)}
                      </span>
                    </div>
                    <div className="listing-actions">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          navigate(`/listings/${listing.id}/images`);
                        }}
                        className="btn-manage-images"
                      >
                        📸 Resimleri Yönet
                      </button>
                    </div>
                  </div>
                </div>
              ))
            )}
          </div>
        )}
      </main>
    </div>
  );
};

export default MyListings;

