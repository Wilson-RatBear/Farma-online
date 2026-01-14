import api from './api';

export const cartService = {
  // Obtener carrito del usuario
  async getCart() {
    try {
      const response = await api.get('/carrito');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Agregar producto al carrito
  async addToCart(productData) {
    try {
      const response = await api.post('/carrito/agregar', productData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Actualizar cantidad en carrito
  async updateCartItem(itemId, quantity) {
    try {
      const response = await api.put(`/carrito/actualizar/${itemId}`, {
        cantidad: quantity
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Eliminar item del carrito
  async removeFromCart(itemId) {
    try {
      const response = await api.delete(`/carrito/eliminar/${itemId}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Vaciar carrito completo
  async clearCart() {
    try {
      const response = await api.delete('/carrito/vaciar');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener resumen del carrito
  async getCartSummary() {
    try {
      const response = await api.get('/carrito/resumen');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};