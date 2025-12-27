import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './context/AuthContext';
import { ToastProvider } from './context/ToastContext';
import ProtectedRoute from './components/ProtectedRoute';
import Login from './pages/Login';
import Register from './pages/Register';
import Home from './pages/Home';
import CreateListing from './pages/CreateListing';
import Listings from './pages/Listings';
import ListingDetail from './pages/ListingDetail';
import MyListings from './pages/MyListings';
import ManageImages from './pages/ManageImages';
import ProfilePage from './pages/ProfilePage';
import Messages from './pages/Messages';
import Chat from './pages/Chat';

function App() {
  return (
    <Router>
      <AuthProvider>
        <ToastProvider>
        <Routes>
          {/* Anasayfa - herkes görebilir */}
          <Route path="/" element={<Home />} />
          <Route path="/login" element={<Login />} />
          <Route path="/register" element={<Register />} />
          <Route
            path="/listings"
            element={
              <ProtectedRoute>
                <Listings />
              </ProtectedRoute>
            }
          />
          <Route
            path="/listings/:id"
            element={
              <ProtectedRoute>
                <ListingDetail />
              </ProtectedRoute>
            }
          />
          <Route
            path="/my-listings"
            element={
              <ProtectedRoute>
                <MyListings />
              </ProtectedRoute>
            }
          />
          <Route
            path="/listings/create"
            element={
              <ProtectedRoute>
                <CreateListing />
              </ProtectedRoute>
            }
          />
          <Route
            path="/listings/:id/images"
            element={
              <ProtectedRoute>
                <ManageImages />
              </ProtectedRoute>
            }
          />
          <Route
            path="/profile"
            element={
              <ProtectedRoute>
                <ProfilePage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/users/:id"
            element={
              <ProtectedRoute>
                <ProfilePage />
              </ProtectedRoute>
            }
          />
          <Route
            path="/messages"
            element={
              <ProtectedRoute>
                <Messages />
              </ProtectedRoute>
            }
          />
          <Route
            path="/messages/:id"
            element={
              <ProtectedRoute>
                <Chat />
              </ProtectedRoute>
            }
          />
        </Routes>
        </ToastProvider>
      </AuthProvider>
    </Router>
  );
}

export default App;
