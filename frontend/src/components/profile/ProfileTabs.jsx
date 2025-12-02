import { useState } from 'react';
import ProfileListingsTab from './ProfileListingsTab';
import ProfileReviewsTab from './ProfileReviewsTab';

const ProfileTabs = ({ userId, isOwnProfile }) => {
  const [activeTab, setActiveTab] = useState('listings');

  const tabs = [
    { id: 'listings', label: 'İlanlar', icon: '📦' },
    { id: 'reviews', label: 'Yorumlar', icon: '⭐' },
  ];

  return (
    <div>
      {/* Tab Headers */}
      <div className="border-b border-slate-200 mb-6">
        <div className="flex gap-2 overflow-x-auto">
          {tabs.map((tab) => (
            <button
              key={tab.id}
              onClick={() => setActiveTab(tab.id)}
              className={`
                px-4 py-3 font-medium text-sm transition-colors whitespace-nowrap
                border-b-2 -mb-px
                ${activeTab === tab.id
                  ? 'border-primary-600 text-primary-600'
                  : 'border-transparent text-slate-600 hover:text-slate-800'
                }
              `}
            >
              <span className="mr-2">{tab.icon}</span>
              {tab.label}
            </button>
          ))}
        </div>
      </div>

      {/* Tab Content */}
      <div>
        {activeTab === 'listings' && (
          <ProfileListingsTab userId={userId} isOwnProfile={isOwnProfile} />
        )}
        {activeTab === 'reviews' && (
          <ProfileReviewsTab userId={userId} />
        )}
      </div>
    </div>
  );
};

export default ProfileTabs;

