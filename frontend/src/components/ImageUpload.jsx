import { useState } from 'react';

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
    <div className="space-y-4">
      {/* Upload Area */}
      <div className="relative">
        <input
          type="file"
          id="image-upload-input"
          multiple
          accept={accept}
          onChange={handleFileSelect}
          disabled={uploading || selectedFiles.length >= maxFiles}
          className="hidden"
        />
        <label
          htmlFor="image-upload-input"
          className={`block border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors ${
            uploading || selectedFiles.length >= maxFiles
              ? 'border-slate-200 bg-slate-50 cursor-not-allowed'
              : 'border-slate-300 hover:border-primary-400 hover:bg-primary-50'
          }`}
        >
          <div className="text-5xl mb-3">📸</div>
          <div className="mb-2">
            <span className="text-slate-700 font-medium">Resim Seçin</span>
            <span className="text-slate-500"> veya sürükleyip bırakın</span>
          </div>
          <div className="text-sm text-slate-500">
            JPEG, PNG, WebP - En fazla 5MB
          </div>
        </label>
      </div>

      {/* Error Message */}
      {error && (
        <div className="p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
          {error}
        </div>
      )}

      {/* Preview Grid */}
      {previews.length > 0 && (
        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
          {previews.map((preview, index) => (
            <div key={index} className="relative group">
              <div className="aspect-square rounded-lg overflow-hidden bg-slate-100 border-2 border-slate-200">
                <img
                  src={preview}
                  alt={`Preview ${index + 1}`}
                  className="w-full h-full object-cover"
                />
              </div>
              <button
                type="button"
                onClick={() => handleRemove(index)}
                disabled={uploading}
                className="absolute -top-2 -right-2 w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-md"
              >
                ✕
              </button>
            </div>
          ))}
        </div>
      )}

      {/* Upload Button */}
      {selectedFiles.length > 0 && (
        <div className="flex justify-end">
          <button
            type="button"
            onClick={handleUpload}
            disabled={uploading}
            className="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {uploading ? 'Yükleniyor...' : `${selectedFiles.length} Resim Yükle`}
          </button>
        </div>
      )}
    </div>
  );
};

export default ImageUpload;

