<template>
  <div class="modal-overlay" @click="$router.push('/admin')">
    <div class="management-container" @click.stop>
      <!-- Header -->
      <div class="management-header">
        <button class="back-button" @click="$router.push('/admin')">
          <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="title">
          <i class="fas fa-cubes"></i> Gestión de Productos
        </h2>
        <button class="close-button" @click="$router.push('/')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="management-content">
        <!-- Barra de acciones -->
        <div class="filters-section">
          <div class="search-container">
            <i class="fas fa-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Buscar productos..."
              class="search-input"
              @input="filterProducts"
            >
          </div>
          <button class="btn-primary" @click="showAddProductModal">
            <i class="fas fa-plus"></i> Nuevo Producto
          </button>
        </div>

        <!-- Estados -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando productos...</p>
        </div>

        <div v-else-if="filteredProducts.length === 0" class="empty-state">
          <i class="fas fa-cubes"></i>
          <p>No se encontraron productos</p>
          <button @click="showAddProductModal" class="btn-retry">
            <i class="fas fa-plus"></i> Crear primer producto
          </button>
        </div>

        <!-- Grid de productos -->
        <div v-else class="products-grid">
          <div 
            v-for="product in filteredProducts" 
            :key="product.id" 
            class="product-card"
            :class="{ 'inactive': !product.activo }"
          >
            <div class="product-image">
              <img 
                :src="product.imagen || '/placeholder-product.png'" 
                :alt="product.nombre"
                @error="handleImageError"
              >
            </div>
            
            <div class="product-info">
              <h3>{{ product.nombre }}</h3>
              <p class="product-description">{{ product.descripcion }}</p>
              
              <div class="product-details">
                <div class="detail-item">
                  <strong>Precio:</strong> ${{ parseFloat(product.precio).toFixed(2) }}
                </div>
                <div class="detail-item">
                  <strong>Stock:</strong> {{ product.stock }}
                </div>
                <div class="detail-item">
                  <strong>Categoría:</strong> {{ getCategoryName(product.categoria_id) }}
                </div>
              </div>

              <div class="product-status">
                <span class="status-badge" :class="product.activo ? 'active' : 'inactive'">
                  {{ product.activo ? 'Activo' : 'Inactivo' }}
                </span>
              </div>
            </div>

            <div class="product-actions">
              <button class="action-btn edit" @click="editProduct(product)" title="Editar">
                <i class="fas fa-edit"></i>
              </button>
              
              <button 
                v-if="product.activo"
                class="action-btn deactivate" 
                @click="deactivateProduct(product.id)"
                title="Desactivar"
              >
                <i class="fas fa-eye-slash"></i>
              </button>
              
              <button 
                v-else
                class="action-btn activate" 
                @click="activateProduct(product.id)"
                title="Activar"
              >
                <i class="fas fa-eye"></i>
              </button>
              
              <button class="action-btn delete" @click="confirmDeleteProduct(product)" title="Eliminar">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para agregar/editar producto -->
    <div v-if="showProductModal" class="modal-overlay inner" @click="closeProductModal">
      <div class="management-container" @click.stop style="max-width: 500px;">
        <div class="management-header">
          <h2 class="title">
            <i class="fas" :class="editingProduct ? 'fa-edit' : 'fa-plus'"></i>
            {{ editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}
          </h2>
          <button class="close-button" @click="closeProductModal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="management-content">
          <form @submit.prevent="saveProduct" class="form-container">
            <div class="form-group">
              <label>Nombre *</label>
              <input 
                v-model="productForm.nombre" 
                type="text" 
                required
                placeholder="Nombre del producto"
              >
            </div>

            <div class="form-group">
              <label>Descripción *</label>
              <textarea 
                v-model="productForm.descripcion" 
                required
                placeholder="Descripción del producto"
                rows="3"
              ></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Precio *</label>
                <input 
                  v-model="productForm.precio" 
                  type="number" 
                  step="0.01"
                  min="0"
                  required
                  placeholder="0.00"
                >
              </div>

              <div class="form-group">
                <label>Stock *</label>
                <input 
                  v-model="productForm.stock" 
                  type="number" 
                  min="0"
                  required
                  placeholder="0"
                >
              </div>
            </div>

            <div class="form-group">
              <label>Categoría *</label>
              <select v-model="productForm.categoria_id" required>
                <option value="">Seleccionar categoría</option>
                <option v-for="category in categories" :key="category.id" :value="category.id">
                  {{ category.nombre }}
                </option>
              </select>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="closeProductModal">
                Cancelar
              </button>
              <button type="submit" class="btn-primary" :disabled="processing">
                <i class="fas fa-spinner fa-spin" v-if="processing"></i>
                {{ editingProduct ? 'Actualizar' : 'Crear' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación -->
    <div v-if="showDeleteModal" class="modal-overlay inner" @click="showDeleteModal = false">
      <div class="management-container" @click.stop style="max-width: 400px;">
        <div class="management-header">
          <h2 class="title">
            <i class="fas fa-exclamation-triangle"></i>
            Confirmar Eliminación
          </h2>
          <button class="close-button" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="management-content">
          <div class="confirm-content">
            <p>¿Eliminar <strong>"{{ productToDelete?.nombre }}"</strong>?</p>
            <p class="warning-text"><i class="fas fa-exclamation-circle"></i> Esta acción no se puede deshacer.</p>
            
            <div class="confirm-actions">
              <button class="btn-secondary" @click="showDeleteModal = false">
                Cancelar
              </button>
              <button class="btn-danger" @click="deleteProduct" :disabled="processing">
                <i class="fas fa-trash"></i> Eliminar
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/adminService'
import { productService } from '../services/productService'

export default {
  name: 'ProductsManagement',
  data() {
    return {
      loading: false,
      processing: false,
      products: [],
      categories: [],
      filteredProducts: [],
      searchQuery: '',
      showProductModal: false,
      showDeleteModal: false,
      editingProduct: null,
      productToDelete: null,
      
      productForm: {
        nombre: '',
        descripcion: '',
        precio: '',
        stock: 0,
        categoria_id: '',
        imagen: '',
        activo: true
      }
    }
  },
  async mounted() {
    await this.loadProducts()
    await this.loadCategories()
  },
  methods: {
    async loadProducts() {
      this.loading = true
      try {
        const response = await adminService.getAllProducts()
        this.products = response.productos || response.data || response
        this.filteredProducts = [...this.products]
      } catch (error) {
        console.error('Error cargando productos:', error)
        alert('Error al cargar productos')
      } finally {
        this.loading = false
      }
    },

    async loadCategories() {
      try {
        const response = await productService.getCategories()
        this.categories = response.categorias || response.data || response
      } catch (error) {
        console.error('Error cargando categorías:', error)
      }
    },

    filterProducts() {
      this.filteredProducts = this.products.filter(product => {
        return product.nombre.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
               product.descripcion.toLowerCase().includes(this.searchQuery.toLowerCase())
      })
    },

    getCategoryName(categoryId) {
      const category = this.categories.find(cat => cat.id === categoryId)
      return category ? category.nombre : 'Sin categoría'
    },

    showAddProductModal() {
      this.editingProduct = null
      this.resetProductForm()
      this.showProductModal = true
    },

    editProduct(product) {
      this.editingProduct = product
      this.productForm = { ...product }
      this.showProductModal = true
    },

    async saveProduct() {
      this.processing = true
      try {
        if (this.editingProduct) {
          await adminService.updateProduct(this.editingProduct.id, this.productForm)
          alert('Producto actualizado correctamente')
        } else {
          await adminService.createProduct(this.productForm)
          alert('Producto creado correctamente')
        }
        
        await this.loadProducts()
        this.closeProductModal()
      } catch (error) {
        console.error('Error guardando producto:', error)
        alert('Error al guardar producto')
      } finally {
        this.processing = false
      }
    },

    confirmDeleteProduct(product) {
      this.productToDelete = product
      this.showDeleteModal = true
    },

    async deleteProduct() {
      this.processing = true
      try {
        await adminService.permanentDeleteProduct(this.productToDelete.id)
        alert('Producto eliminado')
        await this.loadProducts()
        this.showDeleteModal = false
      } catch (error) {
        console.error('Error eliminando producto:', error)
        alert('Error al eliminar producto')
      } finally {
        this.processing = false
        this.productToDelete = null
      }
    },

    async deactivateProduct(productId) {
      try {
        await adminService.deleteProduct(productId)
        await this.loadProducts()
      } catch (error) {
        console.error('Error desactivando producto:', error)
        alert('Error al desactivar producto')
      }
    },

    async activateProduct(productId) {
      try {
        await adminService.restoreProduct(productId)
        await this.loadProducts()
      } catch (error) {
        console.error('Error activando producto:', error)
        alert('Error al activar producto')
      }
    },

    resetProductForm() {
      this.productForm = {
        nombre: '',
        descripcion: '',
        precio: '',
        stock: 0,
        categoria_id: '',
        imagen: '',
        activo: true
      }
    },

    closeProductModal() {
      this.showProductModal = false
      this.editingProduct = null
      this.resetProductForm()
    },

    handleImageError(event) {
      event.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTUwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2Y1ZjVmNSIvPjx0ZXh0IHg9Ijc1IiB5PSI3NSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzk5OTk5OSIgZm9udC1mYW1pbHk9IkFyaWFsIiBmb250LXNpemU9IjEyIj5Qcm9kdWN0bzwvdGV4dD48L3N2Zz4='
    }
  }
}
</script>

<style scoped>
/* OVERLAY BASE */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

/* CONTENEDOR PRINCIPAL - MÁS PEQUEÑO Y CENTRADO */
.management-container {
  background: white;
  border-radius: 10px;
  width: 90%;
  max-width: 900px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 5px 20px rgba(0,0,0,0.2);
}

/* HEADER MEJORADO */
.management-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.back-button, .close-button {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 5px;
  transition: background 0.3s;
}

.back-button:hover, .close-button:hover {
  background: rgba(255, 255, 255, 0.3);
}

.title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* CONTENIDO */
.management-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
  background: #f8f9fa;
}

/* FILTROS COMPACTOS */
.filters-section {
  display: flex;
  gap: 10px;
  margin-bottom: 20px;
  align-items: center;
}

.btn-primary {
  background: #27ae60;
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 6px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  font-weight: 500;
  white-space: nowrap;
}

.search-container {
  position: relative;
  flex: 1;
}

.search-container i {
  position: absolute;
  left: 10px;
  top: 50%;
  transform: translateY(-50%);
  color: #6c757d;
  font-size: 14px;
}

.search-input {
  width: 100%;
  padding: 10px 15px 10px 35px;
  border: 1px solid #ddd;
  border-radius: 6px;
  font-size: 14px;
}

/* ESTADOS */
.loading-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.loading-state i, .empty-state i {
  font-size: 2rem;
  margin-bottom: 10px;
}

.empty-state i {
  color: #bdc3c7;
}

.btn-retry {
  background: #3498db;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 10px;
  display: flex;
  align-items: center;
  gap: 5px;
  margin: 10px auto 0;
}

/* GRID DE PRODUCTOS */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 15px;
}

.product-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  border-left: 4px solid #3498db;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.product-card.inactive {
  opacity: 0.7;
  border-left-color: #95a5a6;
}

.product-image {
  text-align: center;
}

.product-image img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 6px;
  background: #f8f9fa;
}

.product-info {
  flex: 1;
}

.product-info h3 {
  margin: 0 0 8px 0;
  color: #2c3e50;
  font-size: 1rem;
}

.product-description {
  color: #7f8c8d;
  font-size: 0.85rem;
  margin-bottom: 10px;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-clamp: 2;
  box-orient: vertical;
}

.product-details {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 10px;
}

.detail-item {
  font-size: 0.8rem;
  color: #5d6d7e;
}

.detail-item strong {
  color: #2c3e50;
}

.product-status {
  margin-top: 8px;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.active {
  background: #d4edda;
  color: #155724;
}

.status-badge.inactive {
  background: #f8d7da;
  color: #721c24;
}

.product-actions {
  display: flex;
  gap: 6px;
  justify-content: center;
}

.action-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  color: white;
  font-size: 0.75rem;
  transition: opacity 0.3s;
}

.action-btn:hover {
  opacity: 0.9;
}

.action-btn.edit { background: #3498db; }
.action-btn.activate { background: #27ae60; }
.action-btn.deactivate { background: #f39c12; }
.action-btn.delete { background: #e74c3c; }

/* FORM STYLES */
.form-container {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 600;
  color: #2c3e50;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 8px 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 14px;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #3498db;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 15px;
}

.btn-secondary {
  background: #95a5a6;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

.btn-danger {
  background: #e74c3c;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
}

.confirm-content {
  text-align: center;
  padding: 10px 0;
}

.warning-text {
  color: #e74c3c;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin: 12px 0;
  font-size: 0.9rem;
}

.confirm-actions {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 20px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
  .management-container {
    width: 95%;
    max-height: 90vh;
  }
  
  .management-content {
    padding: 15px;
  }
  
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }
  
  .products-grid {
    grid-template-columns: 1fr;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .confirm-actions {
    flex-direction: column;
  }
  
  .product-actions {
    justify-content: center;
  }
}
</style>