import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import listingApi from '../api/listingApi';
import Navbar from '../components/Navbar';
import ListingCard from '../components/ListingCard';
import { Container, Card, Input, Button } from '../components/ui';

/**
 * Listings Page
 * 
 * Public listing browsing page with search/filter functionality.
 * Uses design system components for consistent UI.
 */

const Listings = () => {
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

  const formatPrice = (price, currency) => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      day: 'numeric',
      month: 'short',
    });
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="listings" />

      <Container className="py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-semibold text-slate-800 mb-2">Tüm İlanlar</h1>
          <p className="text-slate-600">İkinci el ürünleri keşfedin</p>
        </div>

        {/* Search Bar */}
        <Card padding="md" className="mb-8">
          <form onSubmit={handleSearch} className="flex flex-col sm:flex-row gap-3">
            <Input
              type="text"
              placeholder="İlan ara..."
              value={filters.search}
              onChange={(e) => setFilters({ ...filters, search: e.target.value })}
              className="flex-1"
            />
            <Input
              type="text"
              placeholder="Konum..."
              value={filters.location}
              onChange={(e) => setFilters({ ...filters, location: e.target.value })}
              className="flex-1"
            />
            <Button type="submit" variant="primary">
              🔍 Ara
            </Button>
            <Button
              type="button"
              variant="secondary"
              onClick={() => {
                setFilters({ search: '', location: '' });
                fetchListings();
              }}
            >
              Temizle
            </Button>
          </form>
        </Card>

        {/* Error Message */}
        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {error}
          </div>
        )}

        {/* Loading State */}
        {loading ? (
          <div className="flex justify-center items-center py-20">
            <div className="text-slate-600">Yükleniyor...</div>
          </div>
        ) : (
          <>
            {/* Empty State */}
            {listings.length === 0 ? (
              <Card padding="lg" className="text-center">
                <div className="text-6xl mb-4">📦</div>
                <h3 className="text-xl font-semibold text-slate-800 mb-2">Henüz ilan bulunmuyor</h3>
                <p className="text-slate-600">Filtreleri temizleyerek tekrar deneyin</p>
              </Card>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {listings.map((listing) => (
                  <ListingCard 
                    key={listing.id} 
                    listing={listing} 
                  />
                ))}
              </div>
            )}
          </>
        )}
      </Container>
    </div>
  );
};

export default Listings;

