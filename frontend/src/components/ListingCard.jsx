import { useNavigate } from 'react-router-dom';
import { Card, Badge } from './ui';

/**
 * ListingCard Component
 * 
 * Reusable card for displaying a listing in grid views.
 * Used in Listings and MyListings pages.
 * 
 * @param {Object} listing - The listing data
 * @param {boolean} showActions - Whether to show management actions (for MyListings)
 */
const ListingCard = ({ listing, showActions = false }) => {
  const navigate = useNavigate();

  const formatPrice = (price, currency) => {
    const symbols = { TRY: '₺', USD: '$', EUR: '€' };
    return `${parseFloat(price).toLocaleString('tr-TR')} ${symbols[currency] || currency}`;
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      day: 'numeric',
      month: 'short',
    });
  };

  const getStatusVariant = (status) => {
    const variants = {
      active: 'success',
      draft: 'warning',
      sold: 'default',
      deleted: 'danger',
    };
    return variants[status] || 'default';
  };

  const getStatusLabel = (status) => {
    const labels = {
      draft: '📝 Taslak',
      active: '✅ Aktif',
      sold: '✔️ Satıldı',
      deleted: '🗑️ Silindi'
    };
    return labels[status] || status;
  };

  return (
    <Card padding="none" className="overflow-hidden hover:shadow-md transition-shadow">
      {/* Image */}
      <div
        onClick={() => navigate(`/listings/${listing.id}`)}
        className="aspect-[4/3] bg-slate-100 flex items-center justify-center cursor-pointer overflow-hidden group"
      >
        {listing.images && listing.images.length > 0 ? (
          <img
            src={listing.images[0].url}
            alt={listing.title}
            className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
          />
        ) : (
          <span className="text-6xl">📦</span>
        )}
      </div>

      {/* Content */}
      <div className="p-4">
        <h3
          onClick={() => navigate(`/listings/${listing.id}`)}
          className="text-lg font-semibold text-slate-800 mb-2 cursor-pointer hover:text-primary-600 transition-colors line-clamp-1"
        >
          {listing.title}
        </h3>

        <p className="text-sm text-slate-600 mb-3 line-clamp-2">
          {listing.description}
        </p>

        <div className="flex items-center justify-between mb-3">
          <span className="text-xl font-bold text-primary-600">
            {formatPrice(listing.price, listing.currency)}
          </span>
          <Badge variant={getStatusVariant(listing.status)}>
            {getStatusLabel(listing.status)}
          </Badge>
        </div>

        <div className="flex items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100 mb-3">
          <div className="flex items-center space-x-1">
            <span>{showActions ? '🕐' : '👤'}</span>
            <span className="truncate">
              {showActions ? formatDate(listing.created_at) : listing.seller_name}
            </span>
          </div>
          {listing.location && (
            <div className="flex items-center space-x-1">
              <span>📍</span>
              <span>{listing.location}</span>
            </div>
          )}
        </div>

        {/* Actions for MyListings */}
        {showActions && (
          <button
            onClick={() => navigate(`/listings/${listing.id}/images`)}
            className="w-full px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-lg transition-colors text-sm"
          >
            📸 Resimleri Yönet
          </button>
        )}
      </div>
    </Card>
  );
};

export default ListingCard;

