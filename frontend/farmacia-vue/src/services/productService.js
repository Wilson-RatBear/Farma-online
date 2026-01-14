import api from './api';

export const productService = {
  // Obtener todos los productos
  async getProducts() {
    try {
      const response = await api.get('/productos');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener un producto por ID
  async getProduct(id) {
    try {
      const response = await api.get(`/productos/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Buscar productos
  async searchProducts(query) {
    try {
      const response = await api.get(`/productos/buscar/${query}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener todas las categorías
  async getCategories() {
    try {
      const response = await api.get('/categorias');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener una categoría por ID
  async getCategory(id) {
    try {
      const response = await api.get(`/categorias/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener promociones activas
  async getActivePromotions() {
    try {
      const response = await api.get('/promociones/activas');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};