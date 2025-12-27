import { createContext, useContext, useState, useCallback } from 'react';

const ToastContext = createContext(null);

// Toast types with their styles
const TOAST_TYPES = {
  success: {
    bg: 'bg-green-500',
    icon: '✓',
  },
  error: {
    bg: 'bg-red-500',
    icon: '✕',
  },
  warning: {
    bg: 'bg-yellow-500',
    icon: '⚠',
  },
  info: {
    bg: 'bg-blue-500',
    icon: 'ℹ',
  },
};

export const ToastProvider = ({ children }) => {
  const [toasts, setToasts] = useState([]);

  const addToast = useCallback((message, type = 'info', duration = 5000) => {
    const id = Date.now() + Math.random();
    
    const toast = {
      id,
      message,
      type,
      duration,
    };

    setToasts((prev) => [...prev, toast]);

    // Auto remove after duration
    if (duration > 0) {
      setTimeout(() => {
        removeToast(id);
      }, duration);
    }

    return id;
  }, []);

  const removeToast = useCallback((id) => {
    setToasts((prev) => prev.filter((t) => t.id !== id));
  }, []);

  // Convenience methods
  const success = useCallback((message, duration) => addToast(message, 'success', duration), [addToast]);
  const error = useCallback((message, duration) => addToast(message, 'error', duration), [addToast]);
  const warning = useCallback((message, duration) => addToast(message, 'warning', duration), [addToast]);
  const info = useCallback((message, duration) => addToast(message, 'info', duration), [addToast]);

  // Parse API error and show toast
  const showApiError = useCallback((err, fallbackMessage = 'Bir hata oluştu') => {
    let message = fallbackMessage;
    
    if (err?.response?.data?.message) {
      message = err.response.data.message;
    } else if (err?.response?.data?.error && typeof err.response.data.error === 'string') {
      message = err.response.data.error;
    } else if (err?.message) {
      message = err.message;
    }

    // Add status code for debugging
    const statusCode = err?.response?.status;
    if (statusCode) {
      message = `[${statusCode}] ${message}`;
    }

    return error(message, 7000); // Show API errors for longer
  }, [error]);

  return (
    <ToastContext.Provider value={{ 
      toasts, 
      addToast, 
      removeToast, 
      success, 
      error, 
      warning, 
      info,
      showApiError 
    }}>
      {children}
      
      {/* Toast Container */}
      <div className="fixed bottom-4 right-4 z-50 flex flex-col gap-2 max-w-sm">
        {toasts.map((toast) => (
          <div
            key={toast.id}
            className={`${TOAST_TYPES[toast.type]?.bg || 'bg-slate-700'} text-white px-4 py-3 rounded-lg shadow-lg flex items-start gap-3 animate-slide-in`}
            role="alert"
          >
            <span className="text-lg font-bold">
              {TOAST_TYPES[toast.type]?.icon}
            </span>
            <div className="flex-1 text-sm break-words">
              {toast.message}
            </div>
            <button
              onClick={() => removeToast(toast.id)}
              className="text-white/80 hover:text-white text-lg font-bold"
            >
              ×
            </button>
          </div>
        ))}
      </div>
    </ToastContext.Provider>
  );
};

export const useToast = () => {
  const context = useContext(ToastContext);
  if (!context) {
    throw new Error('useToast must be used within ToastProvider');
  }
  return context;
};

export default ToastContext;

