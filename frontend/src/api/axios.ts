import axios from 'axios';
import { authService } from './services/auth.service';

export const API_URL = "http://192.168.68.103:8000/api";

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token');
  if (token) {
    // Asegurarse de que config.headers existe y es un objeto
    if (!config.headers) {
      config.headers = {};
    }
    
    // Agregar el token en el formato exacto "Bearer {token}"
    config.headers.Authorization = `Bearer ${token}`;
    
    // Log para debugging
    console.log('Sending request with headers:', {
      Authorization: config.headers.Authorization,
      'Content-Type': config.headers['Content-Type']
    });
  } else {
    console.log('No token found in localStorage');
  }
  return config;
}, (error) => {
  console.error('Error in request interceptor:', error);
  return Promise.reject(error);
});

api.interceptors.response.use(
  (response) => {
    console.log('Response received:', {
      status: response.status,
      headers: response.headers,
      url: response.config.url
    });
    return response;
  },
  (error) => {
    console.error('API Error:', {
      status: error.response?.status,
      message: error.response?.data?.message || error.message,
      url: error.config?.url
    });
    
    if (error.response?.status === 401) {
      // Si recibimos un 401 (Unauthorized), limpiamos el localStorage y cerramos sesión
      authService.logout();
      window.location.href = '/login'; // Redirigimos al login
    }
    
    if (error.response?.data instanceof Document) {
      throw new Error('Unable to connect to API. Please check your connection.');
    }
    return Promise.reject(error);
  }
);

export default api;
