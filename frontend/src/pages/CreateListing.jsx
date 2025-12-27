import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import listingApi from '../api/listingApi';
import categoryApi from '../api/categoryApi';
import Navbar from '../components/Navbar';
import { Container, Card, Button, Input, Textarea, FormField } from '../components/ui';

/**
 * CreateListing Page
 * 
 * Form for creating new listings.
 * Uses design system components for consistent form styling.
 */
const CreateListing = () => {
  const navigate = useNavigate();
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [categories, setCategories] = useState([]);
  
  const [formData, setFormData] = useState({
    title: '',
    description: '',
    price: '',
    currency: 'TRY',
    categoryId: '',
    location: '',
  });

  useEffect(() => {
    fetchCategories();
  }, []);

  const fetchCategories = async () => {
    try {
      const data = await categoryApi.getAll();
      setCategories(data);
    } catch (err) {
      console.error('Kategoriler yüklenirken hata:', err);
    }
  };

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => ({
      ...prev,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const listingData = {
        title: formData.title,
        description: formData.description,
        price: parseFloat(formData.price),
        currency: formData.currency,
        categoryId: parseInt(formData.categoryId),
        location: formData.location || null,
      };

      await listingApi.create(listingData);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.message || err.response?.data?.error || 'İlan oluşturulurken bir hata oluştu');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="my-listings" />

      <Container size="sm" className="py-8">
        {/* Header */}
        <div className="mb-8">
          <h1 className="text-3xl font-semibold text-slate-800 mb-2">📝 Yeni İlan Oluştur</h1>
          <p className="text-slate-600">İkinci el ürününüzü satışa çıkarın</p>
        </div>

        {/* Form Card */}
        <Card padding="lg">
          {error && (
            <div className="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-6">
            {/* Basic Info Section */}
            <div>
              <h2 className="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                Temel Bilgiler
              </h2>
              
              <div className="space-y-4">
                <FormField label="İlan Başlığı" required>
                  <Input
                    name="title"
                    value={formData.title}
                    onChange={handleChange}
                    placeholder="Örn: Satılık iPhone 12"
                    required
                    minLength={3}
                    maxLength={255}
                  />
                </FormField>

                <FormField label="Açıklama" required>
                  <Textarea
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    placeholder="Ürününüz hakkında detaylı bilgi verin..."
                    required
                    minLength={10}
                    rows={6}
                  />
                </FormField>
              </div>
            </div>

            {/* Category Section */}
            <div>
              <h2 className="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                Kategori
              </h2>
              
              <FormField label="Kategori" required>
                <select
                  name="categoryId"
                  value={formData.categoryId}
                  onChange={handleChange}
                  required
                  className="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors bg-white"
                >
                  <option value="">Kategori Seçin</option>
                  {categories.map(category => (
                    <option key={category.id} value={category.id}>
                      {category.name}
                    </option>
                  ))}
                </select>
              </FormField>
            </div>

            {/* Price Section */}
            <div>
              <h2 className="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                Fiyat
              </h2>
              
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <FormField label="Fiyat" required>
                  <Input
                    type="number"
                    name="price"
                    value={formData.price}
                    onChange={handleChange}
                    placeholder="0.00"
                    required
                    min="0.01"
                    step="0.01"
                  />
                </FormField>

                <FormField label="Para Birimi">
                  <select
                    name="currency"
                    value={formData.currency}
                    onChange={handleChange}
                    className="w-full px-4 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition-colors bg-white"
                  >
                    <option value="TRY">TRY (₺)</option>
                    <option value="USD">USD ($)</option>
                    <option value="EUR">EUR (€)</option>
                  </select>
                </FormField>
              </div>
            </div>

            {/* Location Section */}
            <div>
              <h2 className="text-lg font-semibold text-slate-800 mb-4 pb-2 border-b border-slate-200">
                Konum
              </h2>
              
              <FormField label="Konum">
                <Input
                  name="location"
                  value={formData.location}
                  onChange={handleChange}
                  placeholder="Örn: İstanbul, Kadıköy"
                  maxLength={255}
                />
              </FormField>
            </div>

            {/* Action Buttons */}
            <div className="flex flex-col-reverse sm:flex-row gap-3 pt-4">
              <Button
                type="button"
                variant="secondary"
                onClick={() => navigate('/dashboard')}
                disabled={loading}
                className="flex-1"
              >
                İptal
              </Button>
              <Button
                type="submit"
                variant="primary"
                disabled={loading}
                className="flex-1"
              >
                {loading ? 'Oluşturuluyor...' : '✅ İlanı Yayınla'}
              </Button>
            </div>
          </form>
        </Card>
      </Container>
    </div>
  );
};

export default CreateListing;

