import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import listingApi from '../api/listingApi';
import categoryApi from '../api/categoryApi';
import Navbar from '../components/Navbar';
import ListingCard from '../components/ListingCard';
import { Container, Card, Input, Button } from '../components/ui';

/**
 * Listings Page
 * 
 * Public listing browsing page with Elasticsearch-powered search.
 * Supports full-text search, category, price range, and location filters.
 */

const Listings = () => {
  const navigate = useNavigate();
  const [searchParams, setSearchParams] = useSearchParams();
  
  const [listings, setListings] = useState([]);
  const [categories, setCategories] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');
  const [meta, setMeta] = useState({ page: 1, limit: 20, total: 0, totalPages: 0 });
  
  // Filter state - initialized from URL params
  const [filters, setFilters] = useState({
    q: searchParams.get('q') || '',
    categoryId: searchParams.get('categoryId') || '',
    minPrice: searchParams.get('minPrice') || '',
    maxPrice: searchParams.get('maxPrice') || '',
    location: searchParams.get('location') || '',
    sort: searchParams.get('sort') || 'created_at',
    order: searchParams.get('order') || 'desc',
  });
  
  const [page, setPage] = useState(parseInt(searchParams.get('page')) || 1);

  // Fetch categories on mount
  useEffect(() => {
    const fetchCategories = async () => {
      try {
        const data = await categoryApi.getAll();
        setCategories(data);
      } catch (err) {
        console.error('Failed to fetch categories:', err);
      }
    };
    fetchCategories();
  }, []);

  // Submitted filters (only update on form submit)
  const [submittedFilters, setSubmittedFilters] = useState(filters);

  // Fetch listings when submittedFilters or page changes
  useEffect(() => {
    const fetchListings = async () => {
      setLoading(true);
      setError('');
      
      try {
        // Build search params from submitted filters
        const params = { page, limit: 20 };
        
        if (submittedFilters.q) params.q = submittedFilters.q;
        if (submittedFilters.categoryId) params.categoryId = submittedFilters.categoryId;
        if (submittedFilters.minPrice) params.minPrice = submittedFilters.minPrice;
        if (submittedFilters.maxPrice) params.maxPrice = submittedFilters.maxPrice;
        if (submittedFilters.location) params.location = submittedFilters.location;
        if (submittedFilters.sort) params.sort = submittedFilters.sort;
        if (submittedFilters.order) params.order = submittedFilters.order;

        // Use Elasticsearch search endpoint
        const response = await listingApi.search(params);
        
        setListings(response.data || []);
        setMeta(response.meta || { page: 1, limit: 20, total: 0, totalPages: 0 });
        
        // Update URL params
        const newParams = new URLSearchParams();
        Object.entries(params).forEach(([key, value]) => {
          if (value) newParams.set(key, value);
        });
        setSearchParams(newParams, { replace: true });
        
      } catch (err) {
        setError('İlanlar yüklenirken bir hata oluştu');
        console.error(err);
      } finally {
        setLoading(false);
      }
    };

    fetchListings();
  }, [page, submittedFilters]);

  const handleSearch = (e) => {
    e.preventDefault();
    setPage(1);
    setSubmittedFilters({ ...filters });
  };

  const handleClearFilters = () => {
    const emptyFilters = {
      q: '',
      categoryId: '',
      minPrice: '',
      maxPrice: '',
      location: '',
      sort: 'created_at',
      order: 'desc',
    };
    setFilters(emptyFilters);
    setSubmittedFilters(emptyFilters);
    setPage(1);
    setSearchParams({});
  };

  const handlePageChange = (newPage) => {
    setPage(newPage);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  const formatPrice = (price, currency = 'TRY') => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="listings" />

      <Container className="py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-semibold text-slate-800 mb-2">Tüm İlanlar</h1>
          <p className="text-slate-600">
            {meta.total > 0 
              ? `${meta.total} ilan bulundu` 
              : 'İkinci el ürünleri keşfedin'}
          </p>
        </div>

        {/* Search & Filters */}
        <Card padding="md" className="mb-8">
          <form onSubmit={handleSearch} className="space-y-4">
            {/* Main Search Row */}
            <div className="flex flex-col sm:flex-row gap-3">
              <div className="flex-1 relative">
                <Input
                  type="text"
                  placeholder="Ne arıyorsunuz? (ör: iPhone, bisiklet, koltuk...)"
                  value={filters.q}
                  onChange={(e) => setFilters({ ...filters, q: e.target.value })}
                  className="pl-10"
                />
                <span className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
              </div>
              <Button type="submit" variant="primary" className="whitespace-nowrap">
                Ara
              </Button>
            </div>

            {/* Advanced Filters Row */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
              {/* Category */}
              <select
                value={filters.categoryId}
                onChange={(e) => setFilters({ ...filters, categoryId: e.target.value })}
                className="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="">Tüm Kategoriler</option>
                {categories.map((cat) => (
                  <option key={cat.id} value={cat.id}>{cat.name}</option>
                ))}
              </select>

              {/* Min Price */}
              <Input
                type="number"
                placeholder="Min Fiyat"
                value={filters.minPrice}
                onChange={(e) => setFilters({ ...filters, minPrice: e.target.value })}
                min="0"
              />

              {/* Max Price */}
              <Input
                type="number"
                placeholder="Max Fiyat"
                value={filters.maxPrice}
                onChange={(e) => setFilters({ ...filters, maxPrice: e.target.value })}
                min="0"
              />

              {/* Location */}
              <Input
                type="text"
                placeholder="Konum (ör: İstanbul)"
                value={filters.location}
                onChange={(e) => setFilters({ ...filters, location: e.target.value })}
              />

              {/* Sort */}
              <select
                value={`${filters.sort}_${filters.order}`}
                onChange={(e) => {
                  const [sort, order] = e.target.value.split('_');
                  setFilters({ ...filters, sort, order });
                }}
                className="w-full px-3 py-2 border border-slate-300 rounded-lg text-slate-700 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
              >
                <option value="created_at_desc">En Yeni</option>
                <option value="created_at_asc">En Eski</option>
                <option value="price_asc">Fiyat: Düşükten Yükseğe</option>
                <option value="price_desc">Fiyat: Yüksekten Düşüğe</option>
              </select>
            </div>

            {/* Active Filters & Clear Button */}
            {(filters.q || filters.categoryId || filters.minPrice || filters.maxPrice || filters.location) && (
              <div className="flex items-center justify-between pt-2 border-t border-slate-100">
                <div className="flex flex-wrap gap-2">
                  {filters.q && (
                    <span className="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-700 rounded-full text-sm">
                      🔍 "{filters.q}"
                      <button type="button" onClick={() => setFilters({ ...filters, q: '' })} className="hover:text-primary-900">×</button>
                    </span>
                  )}
                  {filters.categoryId && (
                    <span className="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-700 rounded-full text-sm">
                      📁 {categories.find(c => c.id == filters.categoryId)?.name}
                      <button type="button" onClick={() => setFilters({ ...filters, categoryId: '' })} className="hover:text-primary-900">×</button>
                    </span>
                  )}
                  {(filters.minPrice || filters.maxPrice) && (
                    <span className="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-700 rounded-full text-sm">
                      💰 {filters.minPrice || '0'} - {filters.maxPrice || '∞'} ₺
                      <button type="button" onClick={() => setFilters({ ...filters, minPrice: '', maxPrice: '' })} className="hover:text-primary-900">×</button>
                    </span>
                  )}
                  {filters.location && (
                    <span className="inline-flex items-center gap-1 px-2 py-1 bg-primary-50 text-primary-700 rounded-full text-sm">
                      📍 {filters.location}
                      <button type="button" onClick={() => setFilters({ ...filters, location: '' })} className="hover:text-primary-900">×</button>
                    </span>
                  )}
                </div>
                <Button type="button" variant="ghost" size="sm" onClick={handleClearFilters}>
                  Tümünü Temizle
                </Button>
              </div>
            )}
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
            <div className="flex items-center gap-3 text-slate-600">
              <div className="w-6 h-6 border-2 border-primary-500 border-t-transparent rounded-full animate-spin"></div>
              Aranıyor...
            </div>
          </div>
        ) : (
          <>
            {/* Empty State */}
            {listings.length === 0 ? (
              <Card padding="lg" className="text-center">
                <div className="text-6xl mb-4">🔍</div>
                <h3 className="text-xl font-semibold text-slate-800 mb-2">Sonuç bulunamadı</h3>
                <p className="text-slate-600 mb-4">Farklı anahtar kelimeler veya filtreler deneyin</p>
                <Button variant="secondary" onClick={handleClearFilters}>
                  Filtreleri Temizle
                </Button>
              </Card>
            ) : (
              <>
                {/* Listings Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                  {listings.map((listing) => (
                    <ListingCard 
                      key={listing.id} 
                      listing={listing} 
                    />
                  ))}
                </div>

                {/* Pagination */}
                {meta.totalPages > 1 && (
                  <div className="flex justify-center items-center gap-2 mt-8">
                    <Button
                      variant="secondary"
                      size="sm"
                      disabled={page <= 1}
                      onClick={() => handlePageChange(page - 1)}
                    >
                      ← Önceki
                    </Button>
                    
                    <div className="flex items-center gap-1">
                      {Array.from({ length: Math.min(5, meta.totalPages) }, (_, i) => {
                        let pageNum;
                        if (meta.totalPages <= 5) {
                          pageNum = i + 1;
                        } else if (page <= 3) {
                          pageNum = i + 1;
                        } else if (page >= meta.totalPages - 2) {
                          pageNum = meta.totalPages - 4 + i;
                        } else {
                          pageNum = page - 2 + i;
                        }
                        
                        return (
                          <button
                            key={pageNum}
                            onClick={() => handlePageChange(pageNum)}
                            className={`w-10 h-10 rounded-lg text-sm font-medium transition-colors ${
                              page === pageNum
                                ? 'bg-primary-500 text-white'
                                : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50'
                            }`}
                          >
                            {pageNum}
                          </button>
                        );
                      })}
                    </div>
                    
                    <Button
                      variant="secondary"
                      size="sm"
                      disabled={page >= meta.totalPages}
                      onClick={() => handlePageChange(page + 1)}
                    >
                      Sonraki →
                    </Button>
                  </div>
                )}
              </>
            )}
          </>
        )}
      </Container>
    </div>
  );
};

export default Listings;
