import { createContext, useContext, useState, useEffect, useRef } from 'react';
import authApi from '../api/authApi';

const AuthContext = createContext(null);

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [loading, setLoading] = useState(true);
  const isCheckingAuth = useRef(false);

  useEffect(() => {
    // Prevent multiple simultaneous auth checks
    if (!isCheckingAuth.current) {
      checkAuth();
    }
  }, []);

  const checkAuth = async () => {
    if (isCheckingAuth.current) return;
    
    isCheckingAuth.current = true;
    try {
      const userData = await authApi.me();
      setUser(userData);
    } catch (error) {
      setUser(null);
    } finally {
      setLoading(false);
      isCheckingAuth.current = false;
    }
  };

  const login = async (credentials) => {
    const response = await authApi.login(credentials);
    setUser(response.user);
    return response;
  };

  const register = async (userData) => {
    const response = await authApi.register(userData);
    setUser(response.user);
    return response;
  };

  const logout = async () => {
    await authApi.logout();
    setUser(null);
  };

  const isAuthenticated = !!user;

  return (
    <AuthContext.Provider value={{ user, login, register, logout, loading, isAuthenticated }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (!context) {
    throw new Error('useAuth must be used within AuthProvider');
  }
  return context;
};

