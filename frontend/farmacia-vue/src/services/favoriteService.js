import api from './api';

export const favoriteService = {
  // Obtener todos los favoritos del usuario
  getFavorites() {
    return api.get('/favoritos');
  },

  // Agregar producto a favoritos
  addToFavorites(productoId) {
    return api.post(`/favoritos/agregar/${productoId}`);
  },

  // Eliminar producto de favoritos
  removeFromFavorites(productoId) {
    return api.delete(`/favoritos/eliminar/${productoId}`);
  },

  // Verificar si un producto está en favoritos
  checkFavorite(productoId) {
    return api.get(`/favoritos/verificar/${productoId}`);
  }
};