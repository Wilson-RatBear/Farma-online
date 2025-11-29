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
                :src="getProductImage(product.imagen)" 
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
          <form @submit.prevent="saveProduct" class="form-container" enctype="multipart/form-data">
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

            <!-- ✅ SISTEMA DE IMÁGENES MEJORADO -->
            <div class="form-group">
              <label>Imagen del Producto</label>
              
              <!-- Vista previa de imagen -->
              <div v-if="imagePreview" class="image-preview-section">
                <img :src="imagePreview" alt="Vista previa" class="image-preview">
                <button type="button" class="remove-image-btn" @click="removeImagePreview">
                  <i class="fas fa-times"></i> Quitar imagen
                </button>
              </div>

              <!-- Opción 1: Subir archivo -->
              <div class="upload-section">
                <label class="upload-btn">
                  <i class="fas fa-upload"></i> Subir imagen
                  <input 
                    type="file" 
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    @change="handleFileUpload"
                    style="display: none;"
                  >
                </label>
                <span class="upload-text" v-if="uploadedFileName">
                  {{ uploadedFileName }}
                </span>
                <small class="form-help">Formatos: JPEG, PNG, JPG, GIF, WEBP (Máx. 2MB)</small>
              </div>

              <!-- Separador -->
              <div class="separator">
                <span>O</span>
              </div>

              <!-- Opción 2: URL externa -->
              <div class="url-section">
                <label>Usar URL de imagen:</label>
                <input 
                  v-model="productForm.imagen_url" 
                  type="url" 
                  placeholder="https://ejemplo.com/imagen.jpg"
                  class="form-input"
                  @input="handleUrlInput"
                >
                <small class="form-help">
                  Ejemplo: https://via.placeholder.com/300x200/4CAF50/white?text=Producto
                </small>
              </div>
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
      uploadedFileName: '',
      imagePreview: null,
      selectedFile: null,
      
      productForm: {
        nombre: '',
        descripcion: '',
        precio: '',
        stock: 0,
        categoria_id: '',
        imagen: '', // Para URL
        imagen_url: '', // Campo separado para URL
        activo: true
      }
    }
  },
  async mounted() {
    await this.loadProducts()
    await this.loadCategories()
  },
  methods: {
    // ✅ Obtener imagen del producto (maneja URLs y rutas locales)
    getProductImage(imagePath) {
      if (!imagePath) {
        return this.generatePlaceholder('Producto')
      }
      
      // Si es una URL completa
      if (imagePath.startsWith('http')) {
        return imagePath
      }
      
      // Si es una ruta local (storage)
      if (imagePath.startsWith('/storage/')) {
        return `${window.location.origin}${imagePath}`
      }
      
      // Si es base64
      if (imagePath.startsWith('data:image')) {
        return imagePath
      }
      
      return this.generatePlaceholder('Producto')
    },

    // ✅ Generar placeholder SVG
    generatePlaceholder(text) {
      return `data:image/svg+xml;base64,${btoa(`
        <svg width="150" height="150" xmlns="http://www.w3.org/2000/svg">
          <rect width="150" height="150" fill="#f5f5f5"/>
          <text x="75" y="75" text-anchor="middle" fill="#999" font-family="Arial" font-size="12">${text}</text>
        </svg>
      `)}`
    },

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
      this.productForm = { 
        ...product,
        imagen_url: product.imagen // Cargar imagen existente en campo URL
      }
      this.uploadedFileName = ''
      this.selectedFile = null
      
      // Mostrar vista previa de imagen existente
      if (product.imagen) {
        this.imagePreview = this.getProductImage(product.imagen)
      } else {
        this.imagePreview = null
      }
      
      this.showProductModal = true
    },

    // ✅ Manejar subida de archivos CORREGIDO
    handleFileUpload(event) {
      const file = event.target.files[0]
      if (file) {
        // Validar tamaño (2MB máximo)
        if (file.size > 2 * 1024 * 1024) {
          alert('La imagen es demasiado grande. Máximo 2MB permitido.')
          event.target.value = '' // Limpiar input
          return
        }
        
        // Validar tipo
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp']
        if (!validTypes.includes(file.type)) {
          alert('Formato de imagen no válido. Use JPEG, PNG, JPG, GIF o WEBP.')
          event.target.value = ''
          return
        }
        
        this.selectedFile = file
        this.uploadedFileName = file.name
        this.productForm.imagen_url = '' // Limpiar URL si se sube archivo
        
        // Crear vista previa
        const reader = new FileReader()
        reader.onload = (e) => {
          this.imagePreview = e.target.result
        }
        reader.readAsDataURL(file)
      }
    },

    // ✅ Manejar entrada de URL
    handleUrlInput() {
      if (this.productForm.imagen_url) {
        this.imagePreview = this.productForm.imagen_url
        this.selectedFile = null
        this.uploadedFileName = ''
      }
    },

    // ✅ Remover vista previa
    removeImagePreview() {
      this.imagePreview = null
      this.selectedFile = null
      this.uploadedFileName = ''
      this.productForm.imagen_url = ''
      
      // Limpiar input file
      const fileInput = document.querySelector('input[type="file"]')
      if (fileInput) fileInput.value = ''
    },

    // ✅ Guardar producto CORREGIDO (usa FormData para archivos)
    async saveProduct() {
  console.log('📤 INTENTANDO GUARDAR PRODUCTO...')
  console.log('📋 DATOS DEL FORMULARIO:', this.productForm)
  
  this.processing = true
  try {
    const formData = new FormData()
    
    // ✅ CAMPOS ESENCIALES
    formData.append('nombre', this.productForm.nombre)
    formData.append('descripcion', this.productForm.descripcion || '')
    formData.append('precio', parseFloat(this.productForm.precio))
    formData.append('stock', parseInt(this.productForm.stock))
    formData.append('categoria_id', parseInt(this.productForm.categoria_id))
    formData.append('activo', 1)
    
    // ✅ Campos de inventario con valores por defecto
    formData.append('stock_minimo', 10)
    formData.append('stock_maximo', 100)
    formData.append('alertar_stock_bajo', 1)
    
    // ✅ IMAGEN - ESTO ES LO QUE FALTABA
    if (this.productForm.imagen_url && this.productForm.imagen_url.trim() !== '') {
      console.log('🖼️ Enviando URL de imagen:', this.productForm.imagen_url)
      formData.append('imagen_url', this.productForm.imagen_url)
    } else if (this.selectedFile) {
      console.log('🖼️ Enviando archivo de imagen:', this.selectedFile.name)
      formData.append('imagen', this.selectedFile)
    } else {
      console.log('🖼️ No se enviará imagen')
    }

    // ✅ Debug: ver QUÉ se envía realmente
    console.log('🎯 DATOS FINALES QUE SE ENVÍAN AL BACKEND:')
    for (let pair of formData.entries()) {
      console.log(`  ${pair[0]}:`, pair[1], `(tipo: ${typeof pair[1]})`)
    }

    if (this.editingProduct) {
      await adminService.updateProduct(this.editingProduct.id, formData)
      alert('✅ Producto actualizado correctamente')
    } else {
      await adminService.createProduct(formData)
      alert('✅ Producto creado correctamente')
    }
    
    await this.loadProducts()
    this.closeProductModal()
  } catch (error) {
    console.error('❌ Error guardando producto:', error)
    alert('❌ Error al guardar producto: ' + (error.response?.data?.message || error.message))
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
        alert('✅ Producto eliminado')
        await this.loadProducts()
        this.showDeleteModal = false
      } catch (error) {
        console.error('Error eliminando producto:', error)
        alert('❌ Error al eliminar producto')
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
        alert('❌ Error al desactivar producto')
      }
    },

    async activateProduct(productId) {
      try {
        await adminService.restoreProduct(productId)
        await this.loadProducts()
      } catch (error) {
        console.error('Error activando producto:', error)
        alert('❌ Error al activar producto')
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
        imagen_url: '',
        activo: true
      }
      this.uploadedFileName = ''
      this.selectedFile = null
      this.imagePreview = null
    },

    closeProductModal() {
      this.showProductModal = false
      this.editingProduct = null
      this.resetProductForm()
    },

    handleImageError(event) {
      event.target.src = this.generatePlaceholder('Error')
    }
  }
}
</script>

<style scoped>
/* Estilos para el sistema de imágenes */
.upload-section {
  margin-bottom: 15px;
}

.upload-btn {
  display: inline-block;
  padding: 8px 16px;
  background: #1e88e5;
  color: white;
  border-radius: 4px;
  cursor: pointer;
  transition: background 0.3s;
}

.upload-btn:hover {
  background: #0d47a1;
}

.upload-text {
  margin-left: 10px;
  color: #666;
  font-size: 14px;
}

.url-section {
  margin-top: 15px;
}

.separator {
  text-align: center;
  margin: 15px 0;
  position: relative;
}

.separator::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #ddd;
}

.separator span {
  background: white;
  padding: 0 10px;
  color: #666;
}

.image-preview-section {
  text-align: center;
  margin-bottom: 15px;
}

.image-preview {
  max-width: 200px;
  max-height: 150px;
  border-radius: 8px;
  border: 2px solid #e0e0e0;
}

.remove-image-btn {
  display: block;
  margin: 8px auto 0;
  padding: 4px 8px;
  background: #f44336;
  color: white;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
}

.remove-image-btn:hover {
  background: #d32f2f;
}

.form-help {
  color: #666;
  font-size: 12px;
  margin-top: 4px;
  display: block;
}

/* Mejoras visuales para el grid de productos */
.product-image img {
  width: 100%;
  height: 120px;
  object-fit: cover;
  border-radius: 8px 8px 0 0;
}
</style>

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
  /* Estilos para el campo de imagen */
.image-help {
  margin-top: 8px;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 4px;
  font-size: 0.85rem;
}

.image-help code {
  background: #e9ecef;
  padding: 2px 4px;
  border-radius: 3px;
  font-size: 0.8rem;
}

.image-examples {
  margin-top: 8px;
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  align-items: center;
}

.image-examples span {
  font-weight: 600;
  color: #495057;
}

.example-btn {
  background: #e3f2fd;
  border: 1px solid #1e88e5;
  color: #1e88e5;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
}

.example-btn:hover {
  background: #1e88e5;
  color: white;
}

.image-preview {
  margin-top: 8px;
  text-align: center;
}

.image-preview img {
  max-width: 150px;
  max-height: 100px;
  border-radius: 8px;
  border: 2px solid #e9ecef;
}

.image-error {
  color: #dc3545;
  padding: 10px;
  background: #f8d7da;
  border-radius: 4px;
  text-align: center;
}
}
</style>