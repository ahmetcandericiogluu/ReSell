import { useAuth } from '../context/AuthContext';
import { useNavigate } from 'react-router-dom';
import './Dashboard.css';

const Dashboard = () => {
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

  return (
    <div className="dashboard-container">
      <header className="dashboard-header">
        <div className="header-content">
          <h1>🛍️ ReSell</h1>
          <nav className="header-nav">
            <button onClick={() => navigate('/dashboard')} className="nav-link active">Ana Sayfa</button>
            <button onClick={() => navigate('/listings')} className="nav-link">İlanlar</button>
            <button onClick={() => navigate('/my-listings')} className="nav-link">İlanlarım</button>
            <div className="user-menu">
              <span>{user?.name || user?.email}</span>
              <button onClick={handleLogout} className="btn-logout">Çıkış</button>
            </div>
          </nav>
        </div>
      </header>

      <main className="dashboard-main">
        <div className="welcome-card">
          <h2>Hoş Geldiniz! 👋</h2>
          <p>ReSell platformuna başarıyla giriş yaptınız.</p>
          
          <div className="user-details">
            <h3>Kullanıcı Bilgileriniz:</h3>
            <p><strong>Ad:</strong> {user?.firstName} {user?.lastName}</p>
            <p><strong>E-posta:</strong> {user?.email}</p>
            <p><strong>Rol:</strong> {user?.roles?.join(', ')}</p>
          </div>

          <div className="quick-actions">
            <h3>Hızlı İşlemler:</h3>
            <div className="action-buttons">
              <button className="btn-action" onClick={() => navigate('/listings/create')}>➕ Yeni İlan Ekle</button>
              <button className="btn-action" onClick={() => navigate('/my-listings')}>📦 İlanlarım</button>
              <button className="btn-action" onClick={() => navigate('/listings')}>🔍 Tüm İlanlar</button>
              <button className="btn-action">💬 Mesajlarım</button>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Dashboard;
