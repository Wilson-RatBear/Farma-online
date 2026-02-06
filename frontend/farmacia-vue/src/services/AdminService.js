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
 // Actualizar producto existente - VERSIÓN PARA FORMDATA
// Actualizar producto existente - VERSIÓN SIMPLIFICADA Y FUNCIONAL
async updateProduct(id, formData) {
  try {
    console.log('🔄 updateProduct - ID:', id);
    console.log('📦 FormData recibido:', formData instanceof FormData ? 'SÍ' : 'NO');
    
    // Verificar que formData es realmente FormData
    if (!(formData instanceof FormData)) {
      console.error('❌ ERROR: productData no es FormData');
      // Convertir si es necesario
      const newFormData = new FormData();
      if (typeof formData === 'object') {
        Object.keys(formData).forEach(key => {
          if (formData[key] !== undefined && formData[key] !== null) {
            newFormData.append(key, formData[key]);
          }
        });
        formData = newFormData;
      }
    }
    
    // DEBUG: Ver TODOS los campos del FormData
    console.log('📤 VERIFICANDO CAMPOS EN FORMDATA:');
    let hasNombre = false;
    let hasCategoria = false;
    
    for (let pair of formData.entries()) {
      console.log(`  "${pair[0]}" = "${pair[1]}" (tipo: ${typeof pair[1]})`);
      if (pair[0] === 'nombre') hasNombre = true;
      if (pair[0] === 'categoria_id') hasCategoria = true;
    }
    
    if (!hasNombre) {
      console.error('❌ ERROR CRÍTICO: El campo "nombre" NO está en FormData');
    }
    if (!hasCategoria) {
      console.error('❌ ERROR CRÍTICO: El campo "categoria_id" NO está en FormData');
    }
    
    // IMPORTANTE: No especificar Content-Type aquí, déjalo que axios lo maneje
    const response = await api.put(`/admin/productos/${id}`, formData);
    
    console.log('✅ Producto actualizado exitosamente');
    return response.data;
    
  } catch (error) {
    console.error('❌ Error completo en updateProduct:', {
      status: error.response?.status,
      data: error.response?.data,
      error: error.message,
      config: error.config
    });
    throw error.response?.data || error;
  }
},

// Crear producto - también actualizado
async createProduct(productData) {
  try {
    console.log('🆕 createProduct llamado');
    console.log('📦 Tipo de productData:', typeof productData);
    
    // Si es FormData
    if (productData instanceof FormData) {
      console.log('📎 Usando FormData para crear producto');
      
      // DEBUG
      console.log('📤 Contenido del FormData:');
      for (let pair of productData.entries()) {
        console.log(`${pair[0]}:`, pair[1]);
      }
      
      const response = await api.post('/admin/productos', productData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      return response.data;
    } 
    // Si es objeto normal
    else {
      console.log('📝 Creando producto desde objeto normal');
      
      // Si tiene imagen como archivo, convertir a FormData
      if (productData.imagen && productData.imagen instanceof File) {
        console.log('📎 Convirtiendo a FormData porque hay archivo');
        
        const formData = new FormData();
        Object.keys(productData).forEach(key => {
          if (key === 'imagen') {
            formData.append('imagen', productData.imagen);
          } else {
            const value = productData[key];
            if (value !== undefined && value !== null) {
              formData.append(key, String(value));
            }
          }
        });
        
        const response = await api.post('/admin/productos', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        });
        return response.data;
      } else {
        console.log('📤 Enviando como JSON normal');
        const response = await api.post('/admin/productos', productData);
        return response.data;
      }
    }
    
  } catch (error) {
    console.error('❌ Error en createProduct:', error);
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

  // ✅ NUEVOS MÉTODOS PARA REPORTES AVANZADOS
  async getVentasPorPeriodo(rango = 'mensual') {
    try {
      const response = await api.get(`/admin/reports/ventas-periodo?rango=${rango}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error al obtener ventas por período' };
    }
  },

  async getProductosMasVendidos(limite = 10, periodo = 'todo') {
    try {
      const response = await api.get(`/admin/reports/productos-mas-vendidos?limite=${limite}&periodo=${periodo}`);
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error al obtener productos más vendidos' };
    }
  },

  async getMetricasUsuarios() {
    try {
      const response = await api.get('/admin/reports/metricas-usuarios');
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error al obtener métricas de usuarios' };
    }
  },

  async getEstadisticasCategorias() {
    try {
      const response = await api.get('/admin/reports/estadisticas-categorias');
      return response.data;
    } catch (error) {
      throw error.response?.data || { message: 'Error al obtener estadísticas de categorías' };
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

export default adminService;