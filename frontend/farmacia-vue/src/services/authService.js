import api from './api';

export const authService = {
  // Login
  async login(credentials) {
    try {
      const response = await api.post('/login', credentials);
      if (response.data.authorization?.token) {
        localStorage.setItem('auth_token', response.data.authorization.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.user));
      }
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Registro
  async register(userData) {
    console.log('🔄 authService.register INICIADO', new Date().toISOString());
    try {
      const response = await api.post('/register', userData);
      console.log('✅ authService.register EXITOSO', response.status);
      if (response.data.authorization?.token) {
        localStorage.setItem('auth_token', response.data.authorization.token);
        localStorage.setItem('user_data', JSON.stringify(response.data.user));
      }
      return response.data;
    } catch (error) {
      console.log('❌ authService.register ERROR', error.response?.status);
      throw error.response?.data || error;
    }
  },

  // Logout
  logout() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user_data');
  },

  // Obtener usuario actual
  getCurrentUser() {
    const userData = localStorage.getItem('user_data');
    return userData ? JSON.parse(userData) : null;
  },

  // Verificar si está autenticado
  isAuthenticated() {
    return !!localStorage.getItem('auth_token');
  },

  // RECUPERACIÓN DE CONTRASEÑA - NUEVOS MÉTODOS
  async forgotPassword(email) {
    try {
      const response = await api.post('/password/forgot', { email });
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error de conexión' };
    }
  },

  async resetPassword(resetData) {
    try {
      const response = await api.post('/password/reset', resetData);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error de conexión' };
    }
  },



  // Interceptor para manejar errores globalmente
  /*
  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response?.status === 401) {
        // Redirigir a login si no está autenticado
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
  );
  */
};