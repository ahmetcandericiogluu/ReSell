import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import listingApi from '../../api/listingApi';
import { Card, Button, Badge } from '../ui';

const ProfileListingsTab = ({ userId, isOwnProfile }) => {
  const navigate = useNavigate();
  const [listings, setListings] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    if (isOwnProfile) {
      fetchListings();
    }
  }, [userId, isOwnProfile]);

  const fetchListings = async () => {
    if (!isOwnProfile) return;
    
    setLoading(true);
    setError(null);
    
    try {
      const data = await listingApi.getMyListings();
      // getMyListings listing service'den array döner
      const items = Array.isArray(data) ? data : (data.data || []);
      setListings(items);
    } catch (err) {
      setError('İlanlar yüklenemedi');
      console.error('Listings fetch error:', err);
    } finally {
      setLoading(false);
    }
  };

  const formatPrice = (price, currency) => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    });
  };

  const getStatusVariant = (status) => {
    const variants = {
      active: 'success',
      draft: 'warning',
      sold: 'default',
    };
    return variants[status] || 'default';
  };

  if (loading) {
    return (
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {[...Array(6)].map((_, i) => (
          <div key={i} className="animate-pulse">
            <Card padding="none">
              <div className="aspect-[4/3] bg-slate-200" />
              <div className="p-4 space-y-3">
                <div className="h-4 bg-slate-200 rounded w-3/4" />
                <div className="h-3 bg-slate-200 rounded w-1/2" />
              </div>
            </Card>
          </div>
        ))}
      </div>
    );
  }

  if (error) {
    return (
      <Card>
        <div className="text-center py-8">
          <div className="text-4xl mb-2">😞</div>
          <p className="text-slate-600 mb-4">{error}</p>
          <Button onClick={fetchListings}>Tekrar Dene</Button>
        </div>
      </Card>
    );
  }

  // Başka kullanıcı profili için henüz destek yok
  if (!isOwnProfile) {
    return (
      <Card>
        <div className="text-center py-12">
          <div className="text-6xl mb-4">🔒</div>
          <h3 className="text-xl font-semibold text-slate-800 mb-2">
            İlanlar görüntülenemiyor
          </h3>
          <p className="text-slate-600">
            Şu an sadece kendi ilanlarınızı görüntüleyebilirsiniz.
          </p>
        </div>
      </Card>
    );
  }

  if (listings.length === 0) {
    return (
      <Card>
        <div className="text-center py-12">
          <div className="text-6xl mb-4">📦</div>
          <h3 className="text-xl font-semibold text-slate-800 mb-2">
            Henüz ilan yok
          </h3>
          <p className="text-slate-600 mb-6">
            İlk ilanınızı oluşturarak başlayın!
          </p>
          <Button onClick={() => navigate('/listings/create')}>
            İlan Oluştur
          </Button>
        </div>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Listings Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        {listings.map((listing) => (
          <Card 
            key={listing.id} 
            padding="none" 
            className="overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
            onClick={() => navigate(`/listings/${listing.id}`)}
          >
            {/* Thumbnail */}
            <div className="aspect-[4/3] bg-slate-100 flex items-center justify-center overflow-hidden group">
              {listing.images && listing.images.length > 0 ? (
                <img
                  src={listing.images[0].url}
                  alt={listing.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
              ) : listing.thumbnailUrl ? (
                <img
                  src={listing.thumbnailUrl}
                  alt={listing.title}
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                />
              ) : (
                <span className="text-6xl">📦</span>
              )}
            </div>

            {/* Content */}
            <div className="p-4">
              <h3 className="text-lg font-semibold text-slate-800 mb-2 line-clamp-1">
                {listing.title}
              </h3>

              <div className="flex items-center justify-between mb-3">
                <span className="text-xl font-bold text-primary-600">
                  {formatPrice(listing.price, listing.currency)}
                </span>
                <Badge variant={getStatusVariant(listing.status)}>
                  {listing.status === 'active' ? '✅ Aktif' : listing.status}
                </Badge>
              </div>

              <div className="text-xs text-slate-500">
                <span>{formatDate(listing.createdAt)}</span>
              </div>
            </div>
          </Card>
        ))}
      </div>

    </div>
  );
};

export default ProfileListingsTab;

