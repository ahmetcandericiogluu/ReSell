import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import Navbar from '../components/Navbar';
import { Container, Card, Badge } from '../components/ui';

/**
 * Home Page
 * 
 * Ana sayfa - Sitedeki aktif ilanları gösterir.
 * Herkes görebilir (login gerekmez).
 */
const Home = () => {
  const { user, isAuthenticated } = useAuth();
  const navigate = useNavigate();
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchListings();
  }, []);

  const fetchListings = async () => {
    try {
      setLoading(true);
      const response = await listingApi.search({ limit: 12 });
      setListings(response.data || []);
    } catch (err) {
      // Silently fail - show empty state
    } finally {
      setLoading(false);
    }
  };

  const formatPrice = (price, currency) => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="home" />

      {/* Hero Section */}
      <div className="bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 text-white">
        <Container className="py-16 md:py-24">
          <div className="max-w-3xl">
            <h1 className="text-4xl md:text-5xl font-bold mb-4">
              İkinci El Alışverişin
              <span className="text-primary-200"> Yeni Adresi</span>
            </h1>
            <p className="text-lg md:text-xl text-primary-100 mb-8">
              Kullanmadığın eşyaları sat, aradığını bul. Güvenli ve kolay!
            </p>
            <div className="flex flex-wrap gap-4">
              <button
                onClick={() => navigate('/listings')}
                className="px-6 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-primary-50 transition-colors"
              >
                🔍 İlanlara Göz At
              </button>
              {isAuthenticated ? (
                <button
                  onClick={() => navigate('/listings/create')}
                  className="px-6 py-3 bg-primary-500 text-white font-semibold rounded-lg border-2 border-primary-400 hover:bg-primary-400 transition-colors"
                >
                  ➕ İlan Ver
                </button>
              ) : (
                <button
                  onClick={() => navigate('/register')}
                  className="px-6 py-3 bg-primary-500 text-white font-semibold rounded-lg border-2 border-primary-400 hover:bg-primary-400 transition-colors"
                >
                  🚀 Hemen Başla
                </button>
              )}
            </div>
          </div>
        </Container>
      </div>

      <Container className="py-12">
        {/* Welcome message for logged in users */}
        {isAuthenticated && user && (
          <Card padding="md" className="mb-8 bg-gradient-to-r from-emerald-50 to-teal-50 border-emerald-200">
            <div className="flex items-center justify-between flex-wrap gap-4">
              <div>
                <h2 className="text-xl font-semibold text-slate-800">
                  Hoş geldin, {user.name || 'Kullanıcı'}! 👋
                </h2>
                <p className="text-slate-600">Bugün ne satmak istersin?</p>
              </div>
              <div className="flex gap-3">
                <button
                  onClick={() => navigate('/my-listings')}
                  className="px-4 py-2 bg-white text-slate-700 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors text-sm font-medium"
                >
                  📦 İlanlarım
                </button>
                <button
                  onClick={() => navigate('/messages')}
                  className="px-4 py-2 bg-white text-slate-700 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors text-sm font-medium"
                >
                  💬 Mesajlar
                </button>
              </div>
            </div>
          </Card>
        )}

        {/* Active Listings Section */}
        <div className="mb-8">
          <div className="flex items-center justify-between mb-6">
            <h2 className="text-2xl font-bold text-slate-800">🔥 Yeni İlanlar</h2>
            <button
              onClick={() => navigate('/listings')}
              className="text-primary-600 hover:text-primary-700 font-medium text-sm"
            >
              Tümünü Gör →
            </button>
          </div>

          {loading ? (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {[...Array(8)].map((_, i) => (
                <Card key={i} padding="none" className="animate-pulse">
                  <div className="aspect-square bg-slate-200" />
                  <div className="p-3 space-y-2">
                    <div className="h-4 bg-slate-200 rounded w-3/4" />
                    <div className="h-5 bg-slate-200 rounded w-1/2" />
                  </div>
                </Card>
              ))}
            </div>
          ) : listings.length === 0 ? (
            <Card padding="lg" className="text-center">
              <div className="text-6xl mb-4">📦</div>
              <h3 className="text-xl font-semibold text-slate-800 mb-2">
                Henüz ilan yok
              </h3>
              <p className="text-slate-600 mb-4">
                İlk ilanı sen ekle!
              </p>
              {isAuthenticated ? (
                <button
                  onClick={() => navigate('/listings/create')}
                  className="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                  İlan Oluştur
                </button>
              ) : (
                <button
                  onClick={() => navigate('/register')}
                  className="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                >
                  Kayıt Ol
                </button>
              )}
            </Card>
          ) : (
            <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
              {listings.map((listing) => (
                <Card
                  key={listing.id}
                  padding="none"
                  className="overflow-hidden cursor-pointer hover:shadow-lg transition-all group"
                  onClick={() => navigate(`/listings/${listing.id}`)}
                >
                  {/* Image */}
                  <div className="aspect-square bg-slate-100 relative overflow-hidden">
                    {listing.images && listing.images.length > 0 ? (
                      <img
                        src={listing.images[0].url}
                        alt={listing.title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                      />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center">
                        <span className="text-5xl">📦</span>
                      </div>
                    )}
                    {listing.category_name && (
                      <Badge
                        variant="default"
                        size="sm"
                        className="absolute top-2 left-2 bg-white/90 backdrop-blur-sm"
                      >
                        {listing.category_name}
                      </Badge>
                    )}
                  </div>

                  {/* Content */}
                  <div className="p-3">
                    <h3 className="font-medium text-slate-800 line-clamp-1 mb-1 text-sm">
                      {listing.title}
                    </h3>
                    <p className="text-lg font-bold text-primary-600">
                      {formatPrice(listing.price, listing.currency)}
                    </p>
                    {listing.location && (
                      <p className="text-xs text-slate-500 mt-1 truncate">
                        📍 {listing.location}
                      </p>
                    )}
                  </div>
                </Card>
              ))}
            </div>
          )}
        </div>

        {/* Features Section */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mt-12">
          <Card padding="lg" className="text-center">
            <div className="text-4xl mb-4">🔒</div>
            <h3 className="text-lg font-semibold text-slate-800 mb-2">Güvenli Alışveriş</h3>
            <p className="text-slate-600 text-sm">
              Doğrulanmış kullanıcılar ve güvenli mesajlaşma sistemi
            </p>
          </Card>
          <Card padding="lg" className="text-center">
            <div className="text-4xl mb-4">⚡</div>
            <h3 className="text-lg font-semibold text-slate-800 mb-2">Hızlı & Kolay</h3>
            <p className="text-slate-600 text-sm">
              Dakikalar içinde ilan oluştur, hemen satışa başla
            </p>
          </Card>
          <Card padding="lg" className="text-center">
            <div className="text-4xl mb-4">💬</div>
            <h3 className="text-lg font-semibold text-slate-800 mb-2">Anlık Mesajlaşma</h3>
            <p className="text-slate-600 text-sm">
              Satıcılarla gerçek zamanlı iletişim kur
            </p>
          </Card>
        </div>
      </Container>
    </div>
  );
};

export default Home;

