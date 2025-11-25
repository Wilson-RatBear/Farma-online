import api from './api';

export const adminService = {
  // ==================== DASHBOARD ====================
  async getDashboardStats() {
    try {
      const response = await api.get('/admin/dashboard-stats');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== GESTIÓN DE USUARIOS ====================
  
  // Obtener todos los usuarios (admin)
  async getAllUsers() {
    try {
      const response = await api.get('/admin/usuarios');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Actualizar usuario existente
  async updateUser(id, userData) {
    try {
      const response = await api.put(`/admin/usuarios/${id}`, userData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Desactivar usuario (soft delete)
  async deleteUser(id) {
    try {
      const response = await api.delete(`/admin/usuarios/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Restaurar usuario desactivado
  async restoreUser(id) {
    try {
      const response = await api.put(`/admin/usuarios/${id}/restaurar`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== GESTIÓN DE PEDIDOS ====================
  async getAllOrders() {
    try {
      const response = await api.get('/admin/pedidos');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  async updateOrderStatus(orderId, status) {
    try {
      const response = await api.put(`/admin/pedidos/${orderId}/estado`, {
        estado: status
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== GESTIÓN DE PRODUCTOS ====================
  
  // Obtener todos los productos (admin - incluye inactivos)
  async getAllProducts() {
    try {
      const response = await api.get('/admin/productos');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Crear nuevo producto
  async createProduct(productData) {
    try {
      const response = await api.post('/admin/productos', productData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Actualizar producto existente
  async updateProduct(id, productData) {
    try {
      const response = await api.put(`/admin/productos/${id}`, productData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Desactivar producto (soft delete)
  async deleteProduct(id) {
    try {
      const response = await api.delete(`/admin/productos/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Restaurar producto desactivado
  async restoreProduct(id) {
    try {
      const response = await api.put(`/admin/productos/${id}/restaurar`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Eliminación permanente (CUIDADO - irreversible)
  async permanentDeleteProduct(id) {
    try {
      const response = await api.delete(`/admin/productos/${id}/permanent`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== GESTIÓN DE CATEGORÍAS ====================
  
  // Obtener todas las categorías para panel administrativo
  async getAllCategories() {
    try {
      const response = await api.get('/admin/categorias');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener estadísticas de categorías
  async getCategoriesStats() {
    try {
      const response = await api.get('/admin/categorias/estadisticas');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Crear nueva categoría
  async createCategory(categoryData) {
    try {
      const response = await api.post('/admin/categorias', categoryData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Actualizar categoría existente
  async updateCategory(id, categoryData) {
    try {
      const response = await api.put(`/admin/categorias/${id}`, categoryData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Eliminar categoría
  async deleteCategory(id) {
    try {
      const response = await api.delete(`/admin/categorias/${id}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== REPORTES ====================
  async getGeneralMetrics() {
    try {
      const response = await api.get('/admin/reports/metricas-generales');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // ==================== UTILIDADES ====================
  
  // Subir imagen de producto (si decides implementar uploads)
  async uploadProductImage(imageFile) {
    try {
      const formData = new FormData();
      formData.append('image', imageFile);
      
      const response = await api.post('/admin/upload', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // Obtener categorías para selects (público)
  async getCategories() {
    try {
      const response = await api.get('/categorias');
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  }
};
