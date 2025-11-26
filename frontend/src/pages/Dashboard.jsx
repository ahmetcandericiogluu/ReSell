import { useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import './Dashboard.css';

const Dashboard = () => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    try {
      await logout();
      navigate('/login');
    } catch (error) {
      console.error('Logout failed:', error);
    }
  };

  return (
    <div className="dashboard-container">
      <nav className="navbar">
        <div className="nav-content">
          <h1>🛍️ ReSell</h1>
          <div className="nav-actions">
            <span className="user-name">Merhaba, {user?.name}</span>
            <button onClick={handleLogout} className="btn-logout">
              Çıkış Yap
            </button>
          </div>
        </div>
      </nav>

      <main className="dashboard-main">
        <div className="welcome-card">
          <h2>Hoş Geldiniz! 👋</h2>
          <div className="user-info">
            <p><strong>ID:</strong> {user?.id}</p>
            <p><strong>İsim:</strong> {user?.name}</p>
            <p><strong>E-posta:</strong> {user?.email}</p>
          </div>
        </div>

        <div className="feature-grid">
          <div className="feature-card">
            <h3>📦 İlanlar</h3>
            <p>İlanlarınızı yönetin ve yeni ilan ekleyin</p>
            <button className="btn-secondary" disabled>Yakında</button>
          </div>

          <div className="feature-card">
            <h3>💬 Mesajlar</h3>
            <p>Alıcı ve satıcılarla iletişim kurun</p>
            <button className="btn-secondary" disabled>Yakında</button>
          </div>

          <div className="feature-card">
            <h3>⭐ Değerlendirmeler</h3>
            <p>Satıcı değerlendirmelerinizi görün</p>
            <button className="btn-secondary" disabled>Yakında</button>
          </div>

          <div className="feature-card">
            <h3>👤 Profil</h3>
            <p>Profil bilgilerinizi düzenleyin</p>
            <button className="btn-secondary" disabled>Yakında</button>
          </div>
        </div>
      </main>
    </div>
  );
};

export default Dashboard;

