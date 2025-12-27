import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import { useAuth } from '../context/AuthContext';
import profileApi from '../api/profileApi';
import Navbar from '../components/Navbar';
import { Container } from '../components/ui';
import ProfileHeader from '../components/profile/ProfileHeader';
import ProfileTabs from '../components/profile/ProfileTabs';

const ProfilePage = () => {
  const { id } = useParams();
  const { user: currentUser } = useAuth();
  const navigate = useNavigate();
  
  const [profile, setProfile] = useState(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const isOwnProfile = !id || (currentUser && currentUser.id === parseInt(id));

  useEffect(() => {
    fetchProfile();
  }, [id, currentUser]);

  const fetchProfile = async () => {
    setLoading(true);
    setError(null);
    
    try {
      let data;
      if (isOwnProfile) {
        data = await profileApi.getMe();
      } else {
        data = await profileApi.getUserProfile(id);
      }
      setProfile(data);
    } catch (err) {
      setError('Profil yüklenemedi');
      console.error('Profile fetch error:', err);
    } finally {
      setLoading(false);
    }
  };

  const handleProfileUpdate = async (updatedData) => {
    try {
      const data = await profileApi.updateProfile(updatedData);
      setProfile(data);
      return data;
    } catch (err) {
      throw err;
    }
  };

  if (loading) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar />
        <Container>
          <div className="py-12">
            <div className="animate-pulse space-y-4">
              <div className="h-32 bg-slate-200 rounded-xl" />
              <div className="h-64 bg-slate-200 rounded-xl" />
            </div>
          </div>
        </Container>
      </div>
    );
  }

  if (error || !profile) {
    return (
      <div className="min-h-screen bg-slate-50">
        <Navbar />
        <Container>
          <div className="py-12 text-center">
            <div className="text-6xl mb-4">😞</div>
            <h2 className="text-2xl font-bold text-slate-800 mb-2">Profil Yüklenemedi</h2>
            <p className="text-slate-600 mb-6">{error || 'Bir hata oluştu'}</p>
            <button
              onClick={fetchProfile}
              className="px-6 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
            >
              Tekrar Dene
            </button>
          </div>
        </Container>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-blue-50">
      <Navbar />
      <Container>
        <div className="py-6 md:py-12">
          <ProfileHeader 
            profile={profile} 
            isOwnProfile={isOwnProfile}
            onProfileUpdate={handleProfileUpdate}
          />
          
          <div className="mt-6">
            <ProfileTabs 
              userId={profile.id}
              isOwnProfile={isOwnProfile}
            />
          </div>
        </div>
      </Container>
    </div>
  );
};

export default ProfilePage;

