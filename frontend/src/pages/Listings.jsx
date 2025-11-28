import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import './Listings.css';

const Listings = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [filters, setFilters] = useState({
    search: '',
    location: '',
  });

  useEffect(() => {
    fetchListings();
  }, []);

  const fetchListings = async (filterParams = {}) => {
    setLoading(true);
    setError('');
    try {
      const data = await listingApi.getAll(filterParams);
      setListings(data);
    } catch (err) {
      setError('İlanlar yüklenirken bir hata oluştu');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e) => {
    e.preventDefault();
    const params = {};
    if (filters.search) params.search = filters.search;
    if (filters.location) params.location = filters.location;
    fetchListings(params);
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

  return (
    <div className="listings-container">
      <header className="listings-header">
        <div className="header-content">
          <h1>🛍️ ReSell</h1>
          <nav className="header-nav">
            <button onClick={() => navigate('/dashboard')} className="nav-link">Ana Sayfa</button>
            <button onClick={() => navigate('/listings')} className="nav-link active">İlanlar</button>
            <button onClick={() => navigate('/my-listings')} className="nav-link">İlanlarım</button>
            <div className="user-menu">
              <span>{user?.name || user?.email}</span>
              <button onClick={handleLogout} className="btn-logout">Çıkış</button>
            </div>
          </nav>
        </div>
      </header>

      <main className="listings-main">
        <div className="search-section">
          <h2>Tüm İlanlar</h2>
          <form onSubmit={handleSearch} className="search-form">
            <input
              type="text"
              placeholder="İlan ara..."
              value={filters.search}
              onChange={(e) => setFilters({ ...filters, search: e.target.value })}
              className="search-input"
            />
            <input
              type="text"
              placeholder="Konum..."
              value={filters.location}
              onChange={(e) => setFilters({ ...filters, location: e.target.value })}
              className="search-input"
            />
            <button type="submit" className="btn-search">🔍 Ara</button>
            <button 
              type="button" 
              onClick={() => {
                setFilters({ search: '', location: '' });
                fetchListings();
              }}
              className="btn-clear"
            >
              Temizle
            </button>
          </form>
        </div>

        {error && <div className="error-message">{error}</div>}

        {loading ? (
          <div className="loading">Yükleniyor...</div>
        ) : (
          <div className="listings-grid">
            {listings.length === 0 ? (
              <div className="no-listings">
                <p>Henüz ilan bulunmuyor.</p>
              </div>
            ) : (
              listings.map((listing) => (
                <div 
                  key={listing.id} 
                  className="listing-card"
                  onClick={() => navigate(`/listings/${listing.id}`)}
                >
                  <div className="listing-image-placeholder">
                    📦
                  </div>
                  <div className="listing-info">
                    <h3>{listing.title}</h3>
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
                      <span className="listing-seller">👤 {listing.seller_name}</span>
                      <span className="listing-status status-{listing.status}">
                        {listing.status === 'active' ? '✅ Aktif' : listing.status}
                      </span>
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

export default Listings;

