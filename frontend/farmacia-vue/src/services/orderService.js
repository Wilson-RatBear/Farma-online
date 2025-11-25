import api from './api';

export const orderService = {
  // Crear nuevo pedido desde el carrito
  async createOrder(orderData) {
    try {
      const response = await api.post('/pedidos/checkout', orderData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener historial de pedidos del usuario
  async getOrders() {
    try {
      const response = await api.get('/pedidos');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener detalle de un pedido específico
  async getOrder(orderId) {
    try {
      const response = await api.get(`/pedidos/${orderId}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};