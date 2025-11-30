import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Button, Input, Card, FormField } from '../components/ui';

/**
 * Register Page
 * 
 * User registration form using design system components.
 * Handles field validation errors from backend.
 */
const Register = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: '',
    name: '',
    phone: '',
    city: ''
  });
  const [error, setError] = useState('');
  const [violations, setViolations] = useState({});
  const [loading, setLoading] = useState(false);
  
  const { register } = useAuth();
  const navigate = useNavigate();

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setError('');
    setViolations({});
    setLoading(true);

    try {
      await register(formData);
      alert('✅ Kayıt başarılı! Giriş yapabilirsiniz.');
      navigate('/login');
    } catch (err) {
      setError(err.response?.data?.error || 'Kayıt başarısız');
      if (err.response?.data?.violations) {
        setViolations(err.response.data.violations);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 flex items-center justify-center p-4">
      <div className="w-full max-w-md">
        {/* Logo */}
        <div className="text-center mb-8">
          <div className="inline-flex items-center space-x-2 mb-2">
            <span className="text-4xl">🛍️</span>
            <h1 className="text-3xl font-bold text-slate-800">ReSell</h1>
          </div>
          <p className="text-slate-600">İkinci el pazar yerine hoş geldiniz</p>
        </div>

        {/* Auth Card */}
        <Card padding="lg">
          <h2 className="text-2xl font-semibold text-slate-800 mb-6">Kayıt Ol</h2>

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-4">
            <FormField label="İsim" required error={violations.name}>
              <Input
                type="text"
                name="name"
                value={formData.name}
                onChange={handleChange}
                placeholder="Ad Soyad"
                error={!!violations.name}
                required
              />
            </FormField>

            <FormField label="E-posta" required error={violations.email}>
              <Input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="ornek@email.com"
                error={!!violations.email}
                required
              />
            </FormField>

            <FormField label="Şifre" required hint="min 6 karakter" error={violations.password}>
              <Input
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="••••••••"
                error={!!violations.password}
                required
                minLength={6}
              />
            </FormField>

            <FormField label="Telefon" error={violations.phone}>
              <Input
                type="tel"
                name="phone"
                value={formData.phone}
                onChange={handleChange}
                placeholder="0555 555 55 55"
                error={!!violations.phone}
              />
            </FormField>

            <FormField label="Şehir" error={violations.city}>
              <Input
                type="text"
                name="city"
                value={formData.city}
                onChange={handleChange}
                placeholder="İstanbul"
                error={!!violations.city}
              />
            </FormField>

            <Button
              type="submit"
              variant="primary"
              disabled={loading}
              className="w-full mt-6"
            >
              {loading ? 'Kaydediliyor...' : 'Kayıt Ol'}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-slate-600">
            Zaten hesabınız var mı?{' '}
            <Link to="/login" className="text-primary-600 hover:text-primary-700 font-medium">
              Giriş yapın
            </Link>
          </p>
        </Card>
      </div>
    </div>
  );
};

export default Register;

