import { LoginCredentials, SignupCredentials, AuthResponse } from '@/interfaces/auth';
import axios from 'axios';

const API_URL = 'http://192.168.68.103:8000/api';

const authApi = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export const login = async (credentials: LoginCredentials): Promise<AuthResponse> => {
  const response = await authApi.post('/auth/login', credentials);
  return response.data;
};

export const signup = async (credentials: SignupCredentials): Promise<AuthResponse> => {
  const response = await authApi.post('/auth/register', credentials);
  return response.data;
};
