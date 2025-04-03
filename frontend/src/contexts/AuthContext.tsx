"use client";
import { createContext, useContext, useEffect, useState } from 'react';
import api from '@/api/axios';

interface AuthContextType {
  isAuthenticated: boolean;
  setAuth: (value: boolean, userData?: any) => void;
  updateUser: (userData: { [key: string]: string }) => Promise<any>;
  deleteAccount: () => Promise<void>;
  user: any;
  updateUserField: (field: string, value: string) => Promise<void>;
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
      const userId = localStorage.getItem('userId');
      const response = await api.get(`/users/${userId}`, {
        headers: { Authorization: `Bearer ${token}` }
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

  const updateUser = async (userData: { [key: string]: string }) => {
    try {
      const userId = localStorage.getItem('userId');
      const token = localStorage.getItem('token');
      const response = await api.put(`/users/${userId}`, userData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        }
      });
      const updatedUser = response.data;
      setUser(prev => ({ ...prev, ...userData }));
      return updatedUser;
    } catch (error) {
      throw error;
    }
  };

  const updateUserField = async (field: string, value: string) => {
    try {
      const userId = localStorage.getItem('userId');
      const token = localStorage.getItem('token');

      if (!token) {
        throw new Error('No authentication token found');
      }

      const response = await api.put(`/users/${userId}`, { [field]: value }, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`
        }
      });

      if (response.data) {
        setUser(prev => ({ ...prev, [field]: value }));
        return response.data;
      }
      throw new Error('Failed to update user field');
    } catch (error) {
      console.error(`Error updating field ${field}:`, error);
      throw error;
    }
  };

  const updatePassword = async (currentPassword: string, newPassword: string) => {
    try {
      const userId = localStorage.getItem('userId');
      const response = await api.post(`/users/${userId}/password`, {
        current_password: currentPassword,
        new_password: newPassword
      });
      return response.data;
    } catch (error) {
      console.error('Error updating password:', error);
      throw error;
    }
  };

  const deleteAccount = async () => {
    try {
      const userId = localStorage.getItem('userId');
      await api.delete(`/users/${userId}`);
      localStorage.removeItem('token');
      localStorage.removeItem('userId');
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
