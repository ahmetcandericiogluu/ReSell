import { useState } from 'react';
import { Button, Input } from '../ui';

const ProfileEditModal = ({ profile, onClose, onUpdate }) => {
  const [formData, setFormData] = useState({
    name: profile.name || '',
    city: profile.city || '',
    phone: profile.phone || '',
  });
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [success, setSuccess] = useState(false);

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value,
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);

    try {
      await onUpdate(formData);
      setSuccess(true);
      setTimeout(() => {
        onClose();
      }, 1500);
    } catch (err) {
      setError(err.response?.data?.error || 'Profil güncellenemedi');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div className="bg-white rounded-xl max-w-md w-full p-6">
        <h2 className="text-2xl font-bold text-slate-800 mb-6">
          Profili Düzenle
        </h2>

        {success && (
          <div className="mb-4 p-3 bg-emerald-50 text-emerald-700 rounded-lg text-sm">
            ✅ Profiliniz başarıyla güncellendi!
          </div>
        )}

        {error && (
          <div className="mb-4 p-3 bg-red-50 text-red-700 rounded-lg text-sm">
            {error}
          </div>
        )}

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">
              İsim
            </label>
            <Input
              type="text"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
              placeholder="Adınız Soyadınız"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">
              Şehir
            </label>
            <Input
              type="text"
              name="city"
              value={formData.city}
              onChange={handleChange}
              placeholder="Şehir"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">
              Telefon
            </label>
            <Input
              type="tel"
              name="phone"
              value={formData.phone}
              onChange={handleChange}
              placeholder="Telefon numaranız"
            />
          </div>

          <div className="flex gap-3 pt-4">
            <Button
              type="button"
              variant="secondary"
              onClick={onClose}
              disabled={loading}
              className="flex-1"
            >
              İptal
            </Button>
            <Button
              type="submit"
              disabled={loading}
              className="flex-1"
            >
              {loading ? 'Kaydediliyor...' : 'Kaydet'}
            </Button>
          </div>
        </form>
      </div>
    </div>
  );
};

export default ProfileEditModal;

