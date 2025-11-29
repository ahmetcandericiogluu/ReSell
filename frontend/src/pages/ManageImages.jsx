import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import listingApi from '../api/listingApi';
import ImageUpload from '../components/ImageUpload';
import './ManageImages.css';

const ManageImages = () => {
  const { id } = useParams();
  const { user, logout } = useAuth();
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

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  if (loading) {
    return (
      <div className="manage-images-container">
        <div className="loading">Yükleniyor...</div>
      </div>
    );
  }

  if (!listing) {
    return (
      <div className="manage-images-container">
        <div className="error-message">İlan bulunamadı</div>
      </div>
    );
  }

  // Check if user is the owner
  if (listing.seller_id !== user?.id) {
    return (
      <div className="manage-images-container">
        <div className="error-message">Bu ilanın resimlerini yönetme yetkiniz yok</div>
        <button onClick={() => navigate('/my-listings')} className="btn-back">
          ← İlanlarıma Dön
        </button>
      </div>
    );
  }

  return (
    <div className="manage-images-container">
      <header className="manage-images-header">
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

      <main className="manage-images-main">
        <button onClick={() => navigate('/my-listings')} className="btn-back">
          ← İlanlarıma Dön
        </button>

        <div className="manage-images-content">
          <div className="page-header">
            <h1>Resim Yönetimi</h1>
            <p className="listing-title">{listing.title}</p>
          </div>

          {error && <div className="error-message">{error}</div>}
          {success && <div className="success-message">{success}</div>}

          <div className="upload-section">
            <h2>Yeni Resim Ekle</h2>
            <ImageUpload onUpload={handleUpload} maxFiles={10} />
          </div>

          <div className="images-section">
            <h2>Mevcut Resimler ({images.length})</h2>
            
            {images.length === 0 ? (
              <div className="no-images">
                <p>Henüz resim eklenmemiş</p>
                <p className="hint">Yukarıdaki alandan resim ekleyebilirsiniz</p>
              </div>
            ) : (
              <div className="images-grid">
                {images.map((image, index) => (
                  <div key={image.id} className="image-item">
                    <div className="image-wrapper">
                      <img 
                        src={image.url} 
                        alt={`${listing.title} - Resim ${index + 1}`}
                      />
                      {image.position === 1 && (
                        <span className="primary-badge">Ana Resim</span>
                      )}
                    </div>
                    <div className="image-actions">
                      <span className="image-position">#{image.position}</span>
                      <button
                        onClick={() => handleDelete(image.id)}
                        disabled={deleting === image.id}
                        className="btn-delete"
                      >
                        {deleting === image.id ? '⏳' : '🗑️'} Sil
                      </button>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        </div>
      </main>
    </div>
  );
};

export default ManageImages;

