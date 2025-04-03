import api from '../axios';
import { LoginCredentials, SignupCredentials, AuthResponse } from '@/interfaces/auth';

let setAuthCallback: ((value: boolean) => void) | null = null;

export const authService = {
  setAuthHandler(callback: (value: boolean) => void) {
    setAuthCallback = callback;
  },

  async login(credentials: LoginCredentials): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/login', credentials);
    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('userId', response.data.user.id);
      setAuthCallback?.(true);
      return response.data;
    }
    return response.data;
  },

  async signup(credentials: SignupCredentials): Promise<AuthResponse> {
    const response = await api.post<AuthResponse>('/auth/register', credentials);
    if (response.data.token) {
      localStorage.setItem('token', response.data.token);
      localStorage.setItem('userId', response.data.user.id);
      setAuthCallback?.(true);
      return response.data;
    }
    return response.data;
  },

  async logout() {
    try {
      // Llamar al endpoint de logout antes de limpiar
      await api.post('/auth/logout');
    } catch (error) {
      console.error('Error during logout:', error);
    } finally {
      // Siempre limpiar localStorage y actualizar estado aunque falle la llamada
      localStorage.clear();
      setAuthCallback?.(false);
    }
  }
};
