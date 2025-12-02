import { useState } from 'react';
import { Card, Button, Avatar } from '../ui';
import ProfileEditModal from './ProfileEditModal';

const ProfileHeader = ({ profile, isOwnProfile, onProfileUpdate }) => {
  const [isEditModalOpen, setIsEditModalOpen] = useState(false);

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      year: 'numeric',
      month: 'long',
    });
  };

  const renderStars = (rating) => {
    if (!rating) return <span className="text-slate-400">Henüz değerlendirilmedi</span>;
    
    const stars = [];
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) {
      stars.push(<span key={`full-${i}`} className="text-amber-400">⭐</span>);
    }
    if (hasHalfStar) {
      stars.push(<span key="half" className="text-amber-400">⭐</span>);
    }
    
    return (
      <div className="flex items-center gap-1">
        {stars}
        <span className="text-sm text-slate-600 ml-1">({rating.toFixed(1)})</span>
      </div>
    );
  };

  return (
    <>
      <Card>
        <div className="flex flex-col md:flex-row gap-6">
          {/* Avatar */}
          <div className="flex-shrink-0">
            <Avatar name={profile.name} size="lg" />
          </div>

          {/* Profile Info */}
          <div className="flex-1">
            <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
              <div>
                <h1 className="text-2xl font-bold text-slate-800 mb-2">
                  {profile.name}
                </h1>
                
                <div className="flex flex-wrap gap-4 text-sm text-slate-600">
                  {profile.city && (
                    <div className="flex items-center gap-1">
                      <span>📍</span>
                      <span>{profile.city}</span>
                    </div>
                  )}
                  
                  <div className="flex items-center gap-1">
                    <span>📅</span>
                    <span>Üye: {formatDate(profile.createdAt)}</span>
                  </div>
                  
                  {isOwnProfile && profile.email && (
                    <div className="flex items-center gap-1">
                      <span>📧</span>
                      <span>{profile.email}</span>
                    </div>
                  )}
                  
                  {isOwnProfile && profile.phone && (
                    <div className="flex items-center gap-1">
                      <span>📱</span>
                      <span>{profile.phone}</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Edit Button */}
              {isOwnProfile && (
                <Button 
                  variant="secondary"
                  onClick={() => setIsEditModalOpen(true)}
                >
                  Profili Düzenle
                </Button>
              )}
            </div>

            {/* Rating */}
            <div className="pt-4 border-t border-slate-200">
              <div className="flex items-center gap-2">
                <span className="text-sm font-medium text-slate-700">Satıcı Puanı:</span>
                {renderStars(profile.ratingAverage)}
              </div>
            </div>
          </div>
        </div>
      </Card>

      {/* Edit Modal */}
      {isEditModalOpen && (
        <ProfileEditModal
          profile={profile}
          onClose={() => setIsEditModalOpen(false)}
          onUpdate={onProfileUpdate}
        />
      )}
    </>
  );
};

export default ProfileHeader;

