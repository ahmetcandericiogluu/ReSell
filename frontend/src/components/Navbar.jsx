import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import { Avatar, Button } from './ui';

/**
 * Navbar Component
 * 
 * Global navigation bar using design system components.
 * Displays brand, navigation links, user info, and logout button.
 */
const Navbar = ({ activePage = '' }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Logout error:', error);
    }
  };

  const navItems = [
    { path: '/dashboard', label: 'Ana Sayfa', name: 'dashboard' },
    { path: '/listings', label: 'İlanlar', name: 'listings' },
    { path: '/my-listings', label: 'İlanlarım', name: 'my-listings' },
  ];

  return (
    <header className="bg-white border-b border-slate-200 sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          {/* Logo */}
          <div 
            onClick={() => navigate('/dashboard')}
            className="flex items-center space-x-2 cursor-pointer group"
          >
            <span className="text-2xl">🛍️</span>
            <span className="text-xl font-semibold text-slate-800 group-hover:text-primary-600 transition-colors">
              ReSell
            </span>
          </div>

          {/* Navigation */}
          <nav className="hidden md:flex items-center space-x-1">
            {navItems.map((item) => (
              <button
                key={item.name}
                onClick={() => navigate(item.path)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-colors ${
                  activePage === item.name
                    ? 'bg-primary-50 text-primary-700'
                    : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'
                }`}
              >
                {item.label}
              </button>
            ))}
          </nav>

          {/* User menu */}
          <div className="flex items-center space-x-4">
            <div className="hidden sm:flex items-center space-x-2 text-sm text-slate-600">
              <Avatar 
                name={user?.name || user?.email}
                size="sm"
              />
              <span className="font-medium text-slate-700">{user?.name || user?.email}</span>
            </div>
            <Button
              variant="ghost"
              size="sm"
              onClick={handleLogout}
            >
              Çıkış
            </Button>
          </div>
        </div>

        {/* Mobile navigation */}
        <div className="md:hidden pb-3 space-x-1">
          {navItems.map((item) => (
            <button
              key={item.name}
              onClick={() => navigate(item.path)}
              className={`px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                activePage === item.name
                  ? 'bg-primary-50 text-primary-700'
                  : 'text-slate-600 hover:bg-slate-50'
              }`}
            >
              {item.label}
            </button>
          ))}
        </div>
      </div>
    </header>
  );
};

export default Navbar;

