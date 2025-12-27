import { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Button, Input, Card, FormField } from '../components/ui';

/**
 * Login Page
 * 
 * Uses design system components for consistent styling.
 * - FormField for form inputs with labels
 * - Card for the auth container
 * - Button for the submit action
 */
const Login = () => {
  const [formData, setFormData] = useState({
    email: '',
    password: ''
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  
  const { login } = useAuth();
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
    setLoading(true);

    try {
      await login(formData);
      navigate('/');
    } catch (err) {
      setError(err.response?.data?.error || 'Giriş başarısız');
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
          <h2 className="text-2xl font-semibold text-slate-800 mb-6">Giriş Yap</h2>

          {error && (
            <div className="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="space-y-5">
            <FormField label="E-posta" required>
              <Input
                type="email"
                name="email"
                value={formData.email}
                onChange={handleChange}
                placeholder="ornek@email.com"
                required
              />
            </FormField>

            <FormField label="Şifre" required>
              <Input
                type="password"
                name="password"
                value={formData.password}
                onChange={handleChange}
                placeholder="••••••••"
                required
              />
            </FormField>

            <Button
              type="submit"
              variant="primary"
              disabled={loading}
              className="w-full"
            >
              {loading ? 'Giriş yapılıyor...' : 'Giriş Yap'}
            </Button>
          </form>

          <p className="mt-6 text-center text-sm text-slate-600">
            Hesabınız yok mu?{' '}
            <Link to="/register" className="text-primary-600 hover:text-primary-700 font-medium">
              Kayıt olun
            </Link>
          </p>
        </Card>
      </div>
    </div>
  );
};

export default Login;

