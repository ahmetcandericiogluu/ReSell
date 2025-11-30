import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import Navbar from '../components/Navbar';
import { Container, Card, Avatar, Button } from '../components/ui';

/**
 * Dashboard Page
 * 
 * Main landing page after login.
 * Displays user info and quick action buttons using design system components.
 */
const Dashboard = () => {
  const { user } = useAuth();
  const navigate = useNavigate();

  const quickActions = [
    {
      icon: '➕',
      title: 'Yeni İlan Ekle',
      description: 'Satmak istediğiniz ürünü ekleyin',
      action: () => navigate('/listings/create'),
      color: 'bg-primary-50 hover:bg-primary-100 text-primary-700 border-primary-200'
    },
    {
      icon: '📦',
      title: 'İlanlarım',
      description: 'Tüm ilanlarınızı görüntüleyin',
      action: () => navigate('/my-listings'),
      color: 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200'
    },
    {
      icon: '🔍',
      title: 'Tüm İlanlar',
      description: 'Pazar yerindeki ürünlere göz atın',
      action: () => navigate('/listings'),
      color: 'bg-amber-50 hover:bg-amber-100 text-amber-700 border-amber-200'
    },
    {
      icon: '💬',
      title: 'Mesajlarım',
      description: 'Yakında aktif olacak',
      action: () => {},
      color: 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200',
      disabled: true
    }
  ];

  return (
    <div className="min-h-screen bg-slate-50">
      <Navbar activePage="dashboard" />

      <Container className="py-8">
        {/* Welcome Card */}
        <Card padding="lg" className="mb-8">
          <h2 className="text-3xl font-semibold text-slate-800 mb-2">
            Hoş Geldiniz! 👋
          </h2>
          <p className="text-slate-600">
            ReSell platformuna başarıyla giriş yaptınız.
          </p>

          {/* User Info - Using design system components */}
          <div className="mt-6 p-4 bg-slate-50 rounded-lg border border-slate-200">
            <div className="flex items-center space-x-4">
              <Avatar 
                name={user?.name || user?.email} 
                size="lg"
              />
              <div className="flex-1 min-w-0">
                <h3 className="text-lg font-semibold text-slate-800 truncate">
                  {user?.name || 'Kullanıcı'}
                </h3>
                <p className="text-sm text-slate-600 truncate">{user?.email}</p>
                {user?.roles && (
                  <p className="text-xs text-slate-500 mt-1">
                    Rol: {user.roles.join(', ')}
                  </p>
                )}
              </div>
            </div>
          </div>
        </Card>

        {/* Quick Actions */}
        <div className="mb-6">
          <h3 className="text-xl font-semibold text-slate-800 mb-4">Hızlı İşlemler</h3>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {quickActions.map((action, index) => (
              <Card
                key={index}
                padding="md"
                className={`${action.color} border cursor-pointer transition-all hover:shadow-md ${
                  action.disabled ? 'opacity-50 cursor-not-allowed' : ''
                }`}
                onClick={action.disabled ? undefined : action.action}
              >
                <div className="text-3xl mb-3">{action.icon}</div>
                <h4 className="font-semibold mb-1">{action.title}</h4>
                <p className="text-xs opacity-80">{action.description}</p>
              </Card>
            ))}
          </div>
        </div>

        {/* Info Cards - Stats */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Card padding="md">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600 mb-1">Aktif İlanlar</p>
                <p className="text-2xl font-semibold text-slate-800">-</p>
              </div>
              <div className="w-12 h-12 bg-primary-50 rounded-full flex items-center justify-center">
                <span className="text-primary-600 text-xl">📦</span>
              </div>
            </div>
          </Card>

          <Card padding="md">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600 mb-1">Satılan Ürünler</p>
                <p className="text-2xl font-semibold text-slate-800">-</p>
              </div>
              <div className="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center">
                <span className="text-emerald-600 text-xl">✅</span>
              </div>
            </div>
          </Card>

          <Card padding="md">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm text-slate-600 mb-1">Mesajlar</p>
                <p className="text-2xl font-semibold text-slate-800">-</p>
              </div>
              <div className="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center">
                <span className="text-amber-600 text-xl">💬</span>
              </div>
            </div>
          </Card>
        </div>
      </Container>
    </div>
  );
};

export default Dashboard;
