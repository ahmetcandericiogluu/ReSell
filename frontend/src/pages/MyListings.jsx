import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import listingApi from '../api/listingApi';
import Navbar from '../components/Navbar';
import ListingCard from '../components/ListingCard';
import { Container, Card, Button } from '../components/ui';

/**
 * MyListings Page
 * 
 * User's personal listing management page.
 * Shows all listings created by the logged-in user.
 */
const MyListings = () => {
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

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="my-listings" />

      <Container className="py-8">
        {/* Header with Add Button */}
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 bg-gradient-to-r from-purple-50 to-violet-50 border border-purple-200 rounded-xl p-6">
          <div>
            <div className="flex items-center gap-3 mb-2">
              <span className="text-3xl">📦</span>
              <h1 className="text-3xl font-bold text-slate-800">İlanlarım</h1>
            </div>
            <p className="text-slate-700 font-medium">Tüm ilanlarınızı yönetin</p>
          </div>
          <Button
            variant="primary"
            onClick={() => navigate('/listings/create')}
            className="mt-4 sm:mt-0 bg-purple-600 hover:bg-purple-700"
          >
            ➕ Yeni İlan Ekle
          </Button>
        </div>

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
              <Card padding="lg" className="text-center bg-gradient-to-br from-purple-50 to-violet-50 border-purple-200">
                <div className="text-6xl mb-4">📦</div>
                <h3 className="text-xl font-semibold text-slate-800 mb-2">Henüz ilan oluşturmadınız</h3>
                <p className="text-slate-600 mb-6">İlk ilanınızı oluşturarak satışa başlayın</p>
                <Button
                  variant="primary"
                  onClick={() => navigate('/listings/create')}
                  className="bg-purple-600 hover:bg-purple-700"
                >
                  ➕ İlk İlanınızı Oluşturun
                </Button>
              </Card>
            ) : (
              <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                {listings.map((listing) => (
                  <ListingCard 
                    key={listing.id} 
                    listing={listing}
                    showActions={true}
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

export default MyListings;

