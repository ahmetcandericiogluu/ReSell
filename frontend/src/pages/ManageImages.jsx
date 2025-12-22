import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import ImageUpload from '../components/ImageUpload';
import Navbar from '../components/Navbar';
import { Container, Card, Button } from '../components/ui';

/**
 * ManageImages Page
 * 
 * Upload and manage images for a specific listing.
 * Owner-only page with image upload and delete functionality.
 */

const ManageImages = () => {
  const { id } = useParams();
  const { user } = useAuth();
  const navigate = useNavigate();
  const [listing, setListing] = useState(null);
  const [images, setImages] = useState([]);
  const [loading, setLoading] = useState(true);
  const [deleting, setDeleting] = useState(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  useEffect(() => {
    fetchListingAndImages();
  }, [id]);

  const fetchListingAndImages = async () => {
    setLoading(true);
    setError('');
    try {
      const listingData = await listingApi.getById(id);
      setListing(listingData);

      // Get images - either from listing response or separate endpoint
      const imagesData = listingData.images || await listingApi.getImages(id);
      setImages(imagesData);
    } catch (err) {
      setError('İlan yüklenirken bir hata oluştu');
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const handleUpload = async (files) => {
    setError('');
    setSuccess('');
    
    try {
      const uploadedImages = await listingApi.uploadImages(id, files);
      setImages([...images, ...uploadedImages]);
      setSuccess(`${files.length} resim başarıyla yüklendi`);
      
      // Clear success message after 3 seconds
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      throw new Error(err.response?.data?.error || 'Resim yükleme başarısız oldu');
    }
  };

  const handleDelete = async (imageId) => {
    if (!window.confirm('Bu resmi silmek istediğinizden emin misiniz?')) {
      return;
    }

    setDeleting(imageId);
    setError('');
    
    try {
      await listingApi.deleteImage(id, imageId);
      setImages(images.filter(img => img.id !== imageId));
      setSuccess('Resim başarıyla silindi');
      setTimeout(() => setSuccess(''), 3000);
    } catch (err) {
      setError(err.response?.data?.error || 'Resim silinirken bir hata oluştu');
    } finally {
      setDeleting(null);
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="my-listings" />
        <Container className="py-8">
          <div className="flex justify-center items-center py-20">
            <div className="text-slate-600">Yükleniyor...</div>
          </div>
        </Container>
      </div>
    );
  }

  if (!listing) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="my-listings" />
        <Container className="py-8">
          <div className="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4">
            İlan bulunamadı
          </div>
        </Container>
      </div>
    );
  }

  // Check if user is the owner (support both camelCase and snake_case)
  const sellerId = listing.sellerId || listing.seller_id;
  if (parseInt(sellerId) !== parseInt(user?.id)) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar activePage="my-listings" />
        <Container className="py-8">
          <div className="bg-red-50 border border-red-200 text-red-700 rounded-lg p-4 mb-4">
            Bu ilanın resimlerini yönetme yetkiniz yok
          </div>
          <Button
            variant="secondary"
            onClick={() => navigate('/my-listings')}
          >
            ← İlanlarıma Dön
          </Button>
        </Container>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="my-listings" />

      <Container className="py-8">
        {/* Back Button */}
        <Button
          variant="secondary"
          onClick={() => navigate('/my-listings')}
          className="mb-6"
        >
          ← İlanlarıma Dön
        </Button>

        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-semibold text-slate-800 mb-2">📸 Resim Yönetimi</h1>
          <p className="text-slate-600">{listing.title}</p>
        </div>

        {/* Messages */}
        {error && (
          <div className="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
            {error}
          </div>
        )}
        {success && (
          <div className="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg">
            {success}
          </div>
        )}

        {/* Upload Section */}
        <Card padding="md" className="mb-8">
          <h2 className="text-xl font-semibold text-slate-800 mb-4">Yeni Resim Ekle</h2>
          <ImageUpload onUpload={handleUpload} maxFiles={10} />
        </Card>

        {/* Current Images Section */}
        <Card padding="md">
          <h2 className="text-xl font-semibold text-slate-800 mb-4">
            Mevcut Resimler ({images.length})
          </h2>

          {images.length === 0 ? (
            <div className="text-center py-12">
              <div className="text-6xl mb-4">🖼️</div>
              <p className="text-slate-600 mb-2">Henüz resim eklenmemiş</p>
              <p className="text-sm text-slate-500">Yukarıdaki alandan resim ekleyebilirsiniz</p>
            </div>
          ) : (
            <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
              {images.map((image, index) => (
                <div key={image.id} className="group relative">
                  <div className="aspect-square rounded-lg overflow-hidden bg-slate-100 border-2 border-slate-200 relative">
                    <img
                      src={image.url}
                      alt={`${listing.title} - Resim ${index + 1}`}
                      className="w-full h-full object-cover"
                    />
                    {image.position === 1 && (
                      <span className="absolute top-2 left-2 px-2 py-1 bg-primary-600 text-white text-xs font-medium rounded">
                        Ana Resim
                      </span>
                    )}
                    <span className="absolute top-2 right-2 px-2 py-1 bg-black/60 text-white text-xs font-medium rounded">
                      #{image.position}
                    </span>
                  </div>
                  <Button
                    variant="danger"
                    size="sm"
                    onClick={() => handleDelete(image.id)}
                    disabled={deleting === image.id}
                    className="mt-2 w-full"
                  >
                    {deleting === image.id ? '⏳ Siliniyor...' : '🗑️ Sil'}
                  </Button>
                </div>
              ))}
            </div>
          )}
        </Card>
      </Container>
    </div>
  );
};

export default ManageImages;

