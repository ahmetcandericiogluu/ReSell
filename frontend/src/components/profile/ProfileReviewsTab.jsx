import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import profileApi from '../../api/profileApi';
import { Card, Button } from '../ui';

const ProfileReviewsTab = ({ userId }) => {
  const navigate = useNavigate();
  const [reviews, setReviews] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [page, setPage] = useState(1);
  const [total, setTotal] = useState(0);
  const limit = 10;

  useEffect(() => {
    fetchReviews();
  }, [userId, page]);

  const fetchReviews = async () => {
    setLoading(true);
    setError(null);
    
    try {
      const data = await profileApi.getUserReviews(userId, { page, limit });
      setReviews(data.items);
      setTotal(data.total);
    } catch (err) {
      setError('Yorumlar yüklenemedi');
      console.error('Reviews fetch error:', err);
    } finally {
      setLoading(false);
    }
  };

  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    });
  };

  const renderStars = (rating) => {
    const stars = [];
    for (let i = 0; i < 5; i++) {
      stars.push(
        <span key={i} className={i < rating ? 'text-amber-400' : 'text-slate-300'}>
          ⭐
        </span>
      );
    }
    return <div className="flex">{stars}</div>;
  };

  if (loading) {
    return (
      <div className="space-y-4">
        {[...Array(3)].map((_, i) => (
          <Card key={i}>
            <div className="animate-pulse space-y-3">
              <div className="h-4 bg-slate-200 rounded w-1/4" />
              <div className="h-3 bg-slate-200 rounded w-full" />
              <div className="h-3 bg-slate-200 rounded w-3/4" />
            </div>
          </Card>
        ))}
      </div>
    );
  }

  if (error) {
    return (
      <Card>
        <div className="text-center py-8">
          <div className="text-4xl mb-2">😞</div>
          <p className="text-slate-600 mb-4">{error}</p>
          <Button onClick={fetchReviews}>Tekrar Dene</Button>
        </div>
      </Card>
    );
  }

  if (reviews.length === 0) {
    return (
      <Card>
        <div className="text-center py-12">
          <div className="text-6xl mb-4">⭐</div>
          <h3 className="text-xl font-semibold text-slate-800 mb-2">
            Henüz yorum yok
          </h3>
          <p className="text-slate-600">
            Bu kullanıcı hakkında henüz yorum yapılmamış.
          </p>
        </div>
      </Card>
    );
  }

  return (
    <div className="space-y-6">
      {/* Reviews List */}
      <div className="space-y-4">
        {reviews.map((review) => (
          <Card key={review.id}>
            <div className="space-y-3">
              {/* Rating & Date */}
              <div className="flex items-center justify-between">
                {renderStars(review.rating)}
                <span className="text-sm text-slate-500">
                  {formatDate(review.createdAt)}
                </span>
              </div>

              {/* Comment */}
              {review.comment && (
                <p className="text-slate-700">{review.comment}</p>
              )}

              {/* Buyer & Listing Info */}
              <div className="pt-3 border-t border-slate-200 flex flex-wrap items-center gap-4 text-sm text-slate-600">
                <div className="flex items-center gap-1">
                  <span>👤</span>
                  <span>{review.buyer.name}</span>
                </div>
                
                <div className="flex items-center gap-1">
                  <span>📦</span>
                  <button
                    onClick={() => navigate(`/listings/${review.listing.id}`)}
                    className="text-primary-600 hover:text-primary-700 font-medium"
                  >
                    {review.listing.title}
                  </button>
                </div>
              </div>
            </div>
          </Card>
        ))}
      </div>

      {/* Pagination */}
      {total > limit && (
        <div className="flex justify-center gap-2">
          <Button
            variant="secondary"
            size="sm"
            onClick={() => setPage(p => Math.max(1, p - 1))}
            disabled={page === 1}
          >
            Önceki
          </Button>
          <span className="px-4 py-2 text-sm text-slate-600">
            Sayfa {page} / {Math.ceil(total / limit)}
          </span>
          <Button
            variant="secondary"
            size="sm"
            onClick={() => setPage(p => p + 1)}
            disabled={page >= Math.ceil(total / limit)}
          >
            Sonraki
          </Button>
        </div>
      )}
    </div>
  );
};

export default ProfileReviewsTab;

