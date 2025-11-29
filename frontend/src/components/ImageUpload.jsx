import { useState } from 'react';
import './ImageUpload.css';

const ImageUpload = ({ onUpload, maxFiles = 5, accept = 'image/jpeg,image/png,image/webp' }) => {
  const [selectedFiles, setSelectedFiles] = useState([]);
  const [previews, setPreviews] = useState([]);
  const [uploading, setUploading] = useState(false);
  const [error, setError] = useState('');

  const handleFileSelect = (e) => {
    const files = Array.from(e.target.files);
    setError('');

    // Validate file count
    if (selectedFiles.length + files.length > maxFiles) {
      setError(`En fazla ${maxFiles} resim yükleyebilirsiniz`);
      return;
    }

    // Validate file types and sizes
    const validFiles = [];
    const newPreviews = [];

    files.forEach(file => {
      // Check file type
      if (!file.type.match(/^image\/(jpeg|png|webp)$/)) {
        setError('Sadece JPEG, PNG veya WebP formatında resimler yükleyebilirsiniz');
        return;
      }

      // Check file size (5MB)
      if (file.size > 5 * 1024 * 1024) {
        setError('Dosya boyutu en fazla 5MB olabilir');
        return;
      }

      validFiles.push(file);

      // Create preview
      const reader = new FileReader();
      reader.onloadend = () => {
        newPreviews.push(reader.result);
        if (newPreviews.length === validFiles.length) {
          setPreviews([...previews, ...newPreviews]);
        }
      };
      reader.readAsDataURL(file);
    });

    setSelectedFiles([...selectedFiles, ...validFiles]);
  };

  const handleRemove = (index) => {
    setSelectedFiles(selectedFiles.filter((_, i) => i !== index));
    setPreviews(previews.filter((_, i) => i !== index));
  };

  const handleUpload = async () => {
    if (selectedFiles.length === 0) {
      setError('Lütfen en az bir resim seçin');
      return;
    }

    setUploading(true);
    setError('');

    try {
      await onUpload(selectedFiles);
      setSelectedFiles([]);
      setPreviews([]);
    } catch (err) {
      setError(err.message || 'Resim yükleme başarısız oldu');
    } finally {
      setUploading(false);
    }
  };

  return (
    <div className="image-upload">
      <div className="upload-area">
        <input
          type="file"
          id="image-upload-input"
          multiple
          accept={accept}
          onChange={handleFileSelect}
          disabled={uploading || selectedFiles.length >= maxFiles}
          className="upload-input"
        />
        <label htmlFor="image-upload-input" className="upload-label">
          <div className="upload-icon">📸</div>
          <div className="upload-text">
            <strong>Resim Seçin</strong>
            <span>veya sürükleyip bırakın</span>
          </div>
          <div className="upload-hint">
            JPEG, PNG, WebP - En fazla 5MB
          </div>
        </label>
      </div>

      {error && <div className="upload-error">{error}</div>}

      {previews.length > 0 && (
        <div className="preview-grid">
          {previews.map((preview, index) => (
            <div key={index} className="preview-item">
              <img src={preview} alt={`Preview ${index + 1}`} />
              <button
                type="button"
                onClick={() => handleRemove(index)}
                className="remove-btn"
                disabled={uploading}
              >
                ✕
              </button>
            </div>
          ))}
        </div>
      )}

      {selectedFiles.length > 0 && (
        <div className="upload-actions">
          <button
            type="button"
            onClick={handleUpload}
            disabled={uploading}
            className="btn-upload"
          >
            {uploading ? 'Yükleniyor...' : `${selectedFiles.length} Resim Yükle`}
          </button>
        </div>
      )}
    </div>
  );
};

export default ImageUpload;

