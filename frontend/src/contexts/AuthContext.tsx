"use client";
import { createContext, useContext, useEffect, useState } from 'react';
import axios from 'axios';

interface AuthContextType {
  isAuthenticated: boolean;
  setAuth: (value: boolean, userData?: any) => void;
  updateUser: (userData: { name: string; email: string }) => Promise<any>;
  deleteAccount: () => Promise<void>;
  user: any;
  updateUserField: (field: string, value: string) => Promise<any>;
  updatePassword: (currentPassword: string, newPassword: string) => Promise<any>;
}

const AuthContext = createContext<AuthContextType>({
  isAuthenticated: false,
  setAuth: () => {},
  updateUser: async () => {},
  deleteAccount: async () => {},
  user: null,
  updateUserField: async () => {},
  updatePassword: async () => {},
});

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [user, setUser] = useState(null);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      fetchUserProfile(token);
    }
    setIsAuthenticated(!!token);
  }, []);

  const fetchUserProfile = async (token: string) => {
    try {
      const response = await axios.get('/api/users/profile', {
        headers: {
          Authorization: `Bearer ${token}`
        }
      });
      setUser(response.data);
    } catch (error) {
      console.error('Error fetching user profile:', error);
    }
  };

  const setAuth = (value: boolean, userData?: any) => {
    setIsAuthenticated(value);
    if (userData) {
      setUser(userData);
    }
  };

  const updateUser = async (userData: { name: string; email: string }) => {
    try {
      const response = await axios.put('/api/users/profile', userData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`  // Fixed: Replaced getToken() with direct access
        }
      });
      
      const updatedUser = response.data;
      setUser(updatedUser);
      return updatedUser;
    } catch (error) {
      console.error('Error updating user:', error);
      throw error;
    }
  };

  const updateUserField = async (field: string, value: string) => {
    try {
      const response = await axios.patch('/api/users/profile', { [field]: value }, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      
      const updatedUser = response.data;
      setUser(prev => ({ ...prev, [field]: value }));
      return updatedUser;
    } catch (error) {
      console.error(`Error updating ${field}:`, error);
      throw error;
    }
  };

  const updatePassword = async (currentPassword: string, newPassword: string) => {
    try {
      const response = await axios.post('/api/users/change-password', {
        current_password: currentPassword,
        new_password: newPassword
      }, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      return response.data;
    } catch (error) {
      console.error('Error updating password:', error);
      throw error;
    }
  };

  const deleteAccount = async () => {
    try {
      await axios.delete('/api/users/profile', {
        headers: {
          Authorization: `Bearer ${localStorage.getItem('token')}`
        }
      });
      localStorage.removeItem('token');
      setIsAuthenticated(false);
      setUser(null);
    } catch (error) {
      console.error('Error deleting account:', error);
      throw error;
    }
  };

  return (
    <AuthContext.Provider value={{ 
      isAuthenticated, 
      setAuth, 
      updateUser, 
      deleteAccount,
      user,
      updateUserField,
      updatePassword,
    }}>
      {children}
    </AuthContext.Provider>
  );
}

export const useAuth = () => useContext(AuthContext);
