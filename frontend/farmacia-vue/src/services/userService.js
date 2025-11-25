import { api } from './api';

export const userService = {
  // Actualizar perfil de usuario
  async updateProfile(userData) {
    try {
      const response = await api.put('/user/profile', userData);
      return response.data;
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Error al actualizar el perfil');
    }
  }
};