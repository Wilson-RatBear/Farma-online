<template>
  <div class="products-management-page">
    <!-- Header fijo -->
    <header class="page-header">
      <div class="header-content">
         <button class="back-btn" @click="$router.push('/admin')">
            <i class="fas fa-arrow-left"></i>
          </button>
        <div class="header-left">
          <div class="page-title">
            <h1>
              <i class="fas fa-capsules"></i>
              Gestión de Productos
            </h1>
            <p class="subtitle">Administra el inventario de productos de farmacia</p>
          </div>
        </div>
        
        <div class="header-right">
          <div class="header-stats">
            <span class="stat-item">
              <i class="fas fa-box"></i>
              {{ stats.total || 0 }} Total
            </span>
            <span class="stat-item">
              <i class="fas fa-check-circle"></i>
              {{ stats.activos || 0 }} Activos
            </span>
          </div>
        </div>
      </div>
    </header>

    <main class="page-main">
      <!-- Panel de control superior -->
     <div class="control-panel">
  <div class="panel-grid">
    <!-- Búsqueda -->
    <div class="search-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input 
          v-model="searchQuery" 
          type="text" 
          placeholder="Buscar productos..."
          class="search-input"
          @input="filterProducts"
        >
      </div>
    </div>
    
    <!-- Filtros en línea -->
    <div class="filters-section">
      <div class="filter-row">
        <div class="filter-group">
          <select v-model="filters.estado" @change="filterProducts" class="filter-select compact">
            <option value="">Todos los estados</option>
            <option value="activo">🟢 Activos</option>
            <option value="inactivo">⚫ Inactivos</option>
          </select>
        </div>
        
        <div class="filter-group">
          <select v-model="filters.categoria" @change="filterProducts" class="filter-select compact">
            <option value="">Todas las categorías</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.nombre }}
            </option>
          </select>
        </div>
      </div>
    </div>
    
    <!-- Acciones en línea -->
    <div class="actions-section">
      <button class="action-btn icon-btn reset-btn" @click="resetFilters" title="Limpiar filtros">
        <i class="fas fa-times"></i>
      </button>
      <button class="action-btn icon-btn refresh-btn" @click="loadProducts" :disabled="loading" title="Actualizar">
        <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
      </button>
      <button class="action-btn add-btn" @click="showAddProductModal">
        <i class="fas fa-plus"></i> Nuevo
      </button>
    </div>
  </div>
</div>
      <!-- Resumen rápido -->
      <div class="quick-stats">
        <div class="stats-grid">
          <div class="stat-card total" @click="resetFilters">
            <div class="stat-icon">
              <i class="fas fa-capsules"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.total || 0 }}</h3>
              <p>Total Productos</p>
            </div>
          </div>
          
          <div class="stat-card active" @click="filterByStatus('activo')">
            <div class="stat-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.activos || 0 }}</h3>
              <p>Activos</p>
            </div>
          </div>
          
          <div class="stat-card low-stock" @click="filterLowStock">
            <div class="stat-icon">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.bajoStock || 0 }}</h3>
              <p>Stock Bajo</p>
            </div>
          </div>
          
          <div class="stat-card revenue">
            <div class="stat-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
              <h3>{{ categories.length }}</h3>
              <p>Categorías</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Contenido principal -->
      <div class="content-wrapper">
        <!-- Estados de carga/error -->
        <div v-if="loading" class="loading-state">
          <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
          </div>
          <p>Cargando productos...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <div class="error-content">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>Error al cargar productos</h4>
            <p>{{ error }}</p>
            <button @click="loadProducts" class="btn-primary">
              <i class="fas fa-redo-alt"></i> Reintentar
            </button>
          </div>
        </div>

        <div v-else-if="filteredProducts.length === 0" class="empty-state">
          <div class="empty-content">
            <i class="fas fa-cubes"></i>
            <h4>No hay productos</h4>
            <p v-if="searchQuery || filters.estado || filters.categoria">
              No se encontraron productos con los filtros aplicados.
            </p>
            <p v-else>
              No hay productos registrados en el sistema.
            </p>
            <div class="empty-actions">
              <button @click="resetFilters" class="btn-secondary">
                <i class="fas fa-times"></i> Limpiar filtros
              </button>
              <button @click="showAddProductModal" class="btn-primary">
                <i class="fas fa-plus"></i> Crear primer producto
              </button>
            </div>
          </div>
        </div>

        <!-- Grid de productos -->
        <div v-else class="products-container">
          <div class="products-header">
            <h3>
              <i class="fas fa-list"></i>
              Inventario de Productos ({{ filteredProducts.length }})
            </h3>
            <div class="products-actions">
              <button class="export-btn" @click="exportProducts">
                <i class="fas fa-file-export"></i> Exportar
              </button>
            </div>
          </div>

          <div class="products-grid">
            <div 
              v-for="product in filteredProducts" 
              :key="product.id" 
              class="product-card"
              :class="{ 'inactive': !product.activo, 'low-stock': product.stock < 10 }"
            >
              <!-- Imagen del producto -->
              <div class="product-image-section">
                <div class="product-image">
                  <img 
                    :src="getProductImage(product.imagen)" 
                    :alt="product.nombre"
                    @error="handleImageError"
                    class="product-img"
                  >
                  <div v-if="product.stock < 10" class="stock-warning">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Stock bajo</span>
                  </div>
                  <div v-if="!product.activo" class="inactive-badge">
                    <i class="fas fa-eye-slash"></i>
                    <span>Inactivo</span>
                  </div>
                </div>
                
                <!-- Acciones rápidas -->
                <div class="quick-actions">
                  <button class="quick-btn edit-btn" @click="editProduct(product)" title="Editar">
                    <i class="fas fa-edit"></i>
                  </button>
                  <button 
                    v-if="product.activo"
                    class="quick-btn deactivate-btn" 
                    @click="toggleProductStatus(product.id, false)"
                    title="Desactivar"
                  >
                    <i class="fas fa-eye-slash"></i>
                  </button>
                  <button 
                    v-else
                    class="quick-btn activate-btn" 
                    @click="toggleProductStatus(product.id, true)"
                    title="Activar"
                  >
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="quick-btn delete-btn" @click="confirmDeleteProduct(product)" title="Eliminar">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>

              <!-- Información del producto -->
              <div class="product-content">
                <div class="product-header">
                  <h4 class="product-name">{{ product.nombre }}</h4>
                  <div class="product-price">
                    <span class="price-label">Precio:</span>
                    <span class="price-value">${{ parseFloat(product.precio).toFixed(2) }}</span>
                  </div>
                </div>
                
                <p class="product-description">
                  {{ truncateDescription(product.descripcion) }}
                </p>
                
                <div class="product-details">
                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="fas fa-box"></i>
                      <div class="detail-content">
                        <span class="detail-label">Stock</span>
                        <span class="detail-value" :class="{ 'low': product.stock < 10 }">
                          {{ product.stock }} unidades
                        </span>
                      </div>
                    </div>
                    
                    <div class="detail-item">
                      <i class="fas fa-tags"></i>
                      <div class="detail-content">
                        <span class="detail-label">Categoría</span>
                        <span class="detail-value">{{ getCategoryName(product.categoria_id) }}</span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="detail-row">
                    <div class="detail-item">
                      <i class="fas fa-barcode"></i>
                      <div class="detail-content">
                        <span class="detail-label">Código</span>
                        <span class="detail-value">{{ product.codigo || 'N/A' }}</span>
                      </div>
                    </div>
                    
                    <div class="detail-item">
                      <i class="fas fa-calendar-alt"></i>
                      <div class="detail-content">
                        <span class="detail-label">Actualizado</span>
                        <span class="detail-value">{{ formatDate(product.updated_at) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                
                <!-- Indicadores -->
                <div class="product-indicators">
                  <div class="indicator status-indicator">
                    <span class="indicator-dot" :class="product.activo ? 'active' : 'inactive'"></span>
                    <span class="indicator-text">{{ product.activo ? 'Activo' : 'Inactivo' }}</span>
                  </div>
                  
                  <div v-if="product.stock < 10" class="indicator stock-indicator">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span class="indicator-text">Stock bajo</span>
                  </div>
                  
                  <div v-if="product.precio > 100" class="indicator price-indicator">
                    <i class="fas fa-star"></i>
                    <span class="indicator-text">Premium</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Paginación -->
          <div v-if="filteredProducts.length > 12" class="pagination">
            <div class="pagination-info">
              Mostrando {{ Math.min(filteredProducts.length, 12) }} de {{ filteredProducts.length }} productos
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="page-info">Página {{ currentPage }}</span>
              <button class="pagination-btn" :disabled="currentPage * 12 >= filteredProducts.length">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal para agregar/editar producto -->
    <div v-if="showProductModal" class="modal-overlay" @click.self="closeProductModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>
            <i class="fas" :class="editingProduct ? 'fa-edit' : 'fa-plus'"></i>
            {{ editingProduct ? 'Editar Producto' : 'Nuevo Producto' }}
          </h3>
          <button class="close-btn" @click="closeProductModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveProduct" class="product-form" enctype="multipart/form-data">
            <!-- Sección principal -->
            <div class="form-section">
              <h4><i class="fas fa-info-circle"></i> Información básica</h4>
              <div class="form-grid">
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-tag"></i> Nombre del producto *
                  </label>
                  <input 
                    v-model="productForm.nombre" 
                    type="text" 
                    required
                    placeholder="Ej: Paracetamol 500mg"
                    class="form-input"
                  >
                </div>
                
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-align-left"></i> Descripción *
                  </label>
                  <textarea 
                    v-model="productForm.descripcion" 
                    required
                    placeholder="Describe el producto..."
                    rows="3"
                    class="form-textarea"
                  ></textarea>
                </div>
                
                <div class="form-group">
                  <label class="form-label">
                    <i class="fas fa-dollar-sign"></i> Precio *
                  </label>
                  <div class="input-with-icon">
                    <span class="input-icon">$</span>
                    <input 
                      v-model="productForm.precio" 
                      type="number" 
                      step="0.01"
                      min="0.01"
                      required
                      placeholder="0.00"
                      class="form-input"
                    >
                  </div>
                </div>
                
                <div class="form-group">
                  <label class="form-label">
                    <i class="fas fa-boxes"></i> Stock inicial *
                  </label>
                  <input 
                    v-model="productForm.stock" 
                    type="number" 
                    min="0"
                    required
                    placeholder="0"
                    class="form-input"
                  >
                </div>
                
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-tags"></i> Categoría *
                  </label>
                  <select v-model="productForm.categoria_id" required class="form-select">
                    <option value="">Seleccionar categoría</option>
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.nombre }}
                    </option>
                  </select>
                </div>
              </div>
            </div>
            
            <!-- Sección de imagen -->
            <div class="form-section">
              <h4><i class="fas fa-image"></i> Imagen del producto</h4>
              
              <div class="image-upload-section">
                <!-- Vista previa -->
                <div v-if="imagePreview" class="image-preview-container">
                  <img :src="imagePreview" alt="Vista previa" class="image-preview">
                  <button type="button" class="remove-image-btn" @click="removeImagePreview">
                    <i class="fas fa-times"></i> Quitar
                  </button>
                </div>
                
                <!-- Opciones de imagen -->
                <div class="image-options">
                  <div class="upload-option">
                    <label class="upload-btn">
                      <i class="fas fa-upload"></i>
                      <span>Subir imagen</span>
                      <input 
                        type="file" 
                        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                        @change="handleFileUpload"
                        style="display: none;"
                      >
                    </label>
                    <small class="upload-help">JPEG, PNG, GIF, WEBP (Máx. 2MB)</small>
                    <span v-if="uploadedFileName" class="upload-filename">
                      <i class="fas fa-file-image"></i> {{ uploadedFileName }}
                    </span>
                  </div>
                  
                  <div class="separator">
                    <span class="separator-text">O</span>
                  </div>
                  
                  <div class="url-option">
                    <label class="form-label">
                      <i class="fas fa-link"></i> Usar URL de imagen
                    </label>
                    <div class="url-input-group">
                      <input 
                        v-model="productForm.imagen_url" 
                        type="url" 
                        placeholder="https://ejemplo.com/imagen.jpg"
                        class="form-input"
                        @input="handleUrlInput"
                      >
                      <button 
                        v-if="productForm.imagen_url" 
                        type="button" 
                        class="test-url-btn"
                        @click="testImageUrl"
                      >
                        <i class="fas fa-check"></i>
                      </button>
                    </div>
                    <small class="url-help">
                      Puedes usar un servicio como 
                      <a href="https://imgbb.com/" target="_blank" class="help-link">ImgBB</a> 
                      o 
                      <a href="https://postimages.org/" target="_blank" class="help-link">PostImages</a>
                    </small>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Sección de stock -->
            <div class="form-section">
              <h4><i class="fas fa-warehouse"></i> Gestión de stock</h4>
              <div class="form-grid">
                <div class="form-group">
                  <label class="form-label">
                    <i class="fas fa-exclamation-triangle"></i> Stock mínimo
                  </label>
                  <input 
                    v-model="productForm.stock_minimo" 
                    type="number" 
                    min="0"
                    placeholder="10"
                    class="form-input"
                  >
                  <small class="form-help">Alerta cuando el stock sea menor</small>
                </div>
                
                <div class="form-group">
                  <label class="form-label">
                    <i class="fas fa-boxes"></i> Stock máximo
                  </label>
                  <input 
                    v-model="productForm.stock_maximo" 
                    type="number" 
                    min="0"
                    placeholder="1000"
                    class="form-input"
                  >
                  <small class="form-help">Capacidad máxima de almacén</small>
                </div>
                
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-info-circle"></i> Estado
                  </label>
                  <div class="status-toggle">
                    <button 
                      type="button" 
                      class="toggle-btn active"
                      :class="{ 'selected': productForm.activo }"
                      @click="productForm.activo = true"
                    >
                      <i class="fas fa-check-circle"></i> Activo
                    </button>
                    <button 
                      type="button" 
                      class="toggle-btn inactive"
                      :class="{ 'selected': !productForm.activo }"
                      @click="productForm.activo = false"
                    >
                      <i class="fas fa-times-circle"></i> Inactivo
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Acciones del formulario -->
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="closeProductModal" :disabled="processing">
                Cancelar
              </button>
              <button type="submit" class="submit-btn" :disabled="processing">
                <i class="fas fa-spinner fa-spin" v-if="processing"></i>
                <span v-else>
                  {{ editingProduct ? 'Actualizar Producto' : 'Crear Producto' }}
                </span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal de confirmación de eliminación -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal-content confirm-modal" @click.stop>
        <div class="modal-header">
          <h3>
            <i class="fas fa-exclamation-triangle"></i>
            Confirmar Eliminación
          </h3>
          <button class="close-btn" @click="showDeleteModal = false">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <div class="confirm-content">
            <div class="confirm-icon">
              <i class="fas fa-trash-alt"></i>
            </div>
            <h4>¿Eliminar producto?</h4>
            <p>
              Estás a punto de eliminar <strong>"{{ productToDelete?.nombre }}"</strong> 
              permanentemente.
            </p>
            <div class="confirm-details">
              <p><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
              <p><i class="fas fa-exclamation-circle"></i> Se perderán todos los datos del producto.</p>
            </div>
          </div>
          
          <div class="confirm-actions">
            <button class="cancel-btn" @click="showDeleteModal = false" :disabled="processing">
              Cancelar
            </button>
            <button class="delete-btn" @click="deleteProduct" :disabled="processing">
              <i class="fas fa-trash" v-if="!processing"></i>
              <i class="fas fa-spinner fa-spin" v-else></i>
              Eliminar Permanentemente
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
// Importaciones (mantén las tuyas)
import { adminService } from '../services/adminService'
import { productService } from '../services/productService'

export default {
  name: 'ProductsManagementPage',
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
      currentPage: 1,
      stats: {
        total: 0,
        activos: 0,
        bajoStock: 0
      },
      filters: {
        estado: '',
        categoria: ''
      },
      
      productForm: {
        nombre: '',
        descripcion: '',
        precio: '',
        stock: 0,
        categoria_id: '',
        imagen_url: '',
        stock_minimo: 10,
        stock_maximo: 1000,
        activo: true
      }
    }
  },
  async mounted() {
    await this.loadProducts();
    await this.loadCategories();
  },
  methods: {
    async loadProducts() {
      this.loading = true;
      try {
        const response = await adminService.getAllProducts();
        this.products = response.productos || response.data || response;
        this.filteredProducts = [...this.products];
        this.calculateStats();
      } catch (error) {
        console.error('Error cargando productos:', error);
        // Datos de ejemplo para desarrollo
        this.loadSampleData();
      } finally {
        this.loading = false;
      }
    },
    
    async loadCategories() {
      try {
        const response = await productService.getCategories();
        this.categories = response.categorias || response.data || response || [];
      } catch (error) {
        console.error('Error cargando categorías:', error);
        this.categories = [
          { id: 1, nombre: 'Medicamentos' },
          { id: 2, nombre: 'Cuidado Personal' },
          { id: 3, nombre: 'Suplementos' },
          { id: 4, nombre: 'Primeros Auxilios' }
        ];
      }
    },
    
    loadSampleData() {
      this.products = [
        {
          id: 1,
          nombre: 'Paracetamol 500mg',
          descripcion: 'Analgésico y antipirético de 500mg. Alivia el dolor y reduce la fiebre.',
          precio: '25.50',
          stock: 45,
          categoria_id: 1,
          imagen: 'https://via.placeholder.com/300x200/4CAF50/white?text=Paracetamol',
          codigo: 'MED-001',
          activo: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: 2,
          nombre: 'Ibuprofeno 400mg',
          descripcion: 'Antiinflamatorio no esteroideo para aliviar dolores musculares.',
          precio: '35.75',
          stock: 8,
          categoria_id: 1,
          imagen: 'https://via.placeholder.com/300x200/2196F3/white?text=Ibuprofeno',
          codigo: 'MED-002',
          activo: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: 3,
          nombre: 'Vitaminas C 1000mg',
          descripcion: 'Suplemento de vitamina C para fortalecer el sistema inmunológico.',
          precio: '89.99',
          stock: 120,
          categoria_id: 3,
          imagen: 'https://via.placeholder.com/300x200/FF9800/white?text=Vitamina+C',
          codigo: 'SUP-001',
          activo: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: 4,
          nombre: 'Jabón Antibacterial',
          descripcion: 'Jabón líquido con propiedades antibacterianas para el cuidado diario.',
          precio: '15.50',
          stock: 25,
          categoria_id: 2,
          imagen: 'https://via.placeholder.com/300x200/9C27B0/white?text=Jabón',
          codigo: 'CUI-001',
          activo: false,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: 5,
          nombre: 'Termómetro Digital',
          descripcion: 'Termómetro digital de lectura rápida y precisa.',
          precio: '120.00',
          stock: 15,
          categoria_id: 4,
          imagen: 'https://via.placeholder.com/300x200/FF5722/white?text=Termómetro',
          codigo: 'AUX-001',
          activo: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        },
        {
          id: 6,
          nombre: 'Curitas Antisépticas',
          descripcion: 'Caja de 50 curitas con antiséptico incorporado.',
          precio: '8.99',
          stock: 200,
          categoria_id: 4,
          imagen: 'https://via.placeholder.com/300x200/00BCD4/white?text=Curitas',
          codigo: 'AUX-002',
          activo: true,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString()
        }
      ];
      this.filteredProducts = [...this.products];
      this.calculateStats();
    },
    
    calculateStats() {
      this.stats = {
        total: this.products.length,
        activos: this.products.filter(p => p.activo).length,
        bajoStock: this.products.filter(p => p.stock < 10).length
      };
    },
    
    filterProducts() {
      let filtered = this.products;
      
      // Filtrar por búsqueda
      if (this.searchQuery) {
        const query = this.searchQuery.toLowerCase();
        filtered = filtered.filter(product => 
          product.nombre.toLowerCase().includes(query) ||
          product.descripcion.toLowerCase().includes(query) ||
          this.getCategoryName(product.categoria_id).toLowerCase().includes(query)
        );
      }
      
      // Filtrar por estado
      if (this.filters.estado) {
        if (this.filters.estado === 'activo') {
          filtered = filtered.filter(product => product.activo);
        } else if (this.filters.estado === 'inactivo') {
          filtered = filtered.filter(product => !product.activo);
        }
      }
      
      // Filtrar por categoría
      if (this.filters.categoria) {
        filtered = filtered.filter(product => 
          product.categoria_id === parseInt(this.filters.categoria)
        );
      }
      
      this.filteredProducts = filtered;
    },
    
    resetFilters() {
      this.searchQuery = '';
      this.filters.estado = '';
      this.filters.categoria = '';
      this.filteredProducts = [...this.products];
    },
    
    filterByStatus(status) {
      this.filters.estado = status;
      this.filterProducts();
    },
    
    filterLowStock() {
      this.filteredProducts = this.products.filter(p => p.stock < 10);
    },
    
    getCategoryName(categoryId) {
      const category = this.categories.find(cat => cat.id === categoryId);
      return category ? category.nombre : 'Sin categoría';
    },
    
    getProductImage(imagePath) {
      if (!imagePath) {
        return this.generatePlaceholder('Producto');
      }
      
      // Si es una URL completa
      if (imagePath.startsWith('http')) {
        return imagePath;
      }
      
      // Si es una ruta local (storage)
      if (imagePath.startsWith('/storage/')) {
        return `${window.location.origin}${imagePath}`;
      }
      
      return this.generatePlaceholder('Producto');
    },
    
    generatePlaceholder(text) {
      return `data:image/svg+xml;base64,${btoa(`
        <svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
          <rect width="300" height="200" fill="#f5f5f5"/>
          <text x="150" y="100" text-anchor="middle" fill="#999" font-family="Arial" font-size="14" dy=".3em">${text}</text>
        </svg>
      `)}`;
    },
    
    truncateDescription(text, maxLength = 100) {
      if (!text) return 'Sin descripción';
      return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    },
    
    formatDate(dateString) {
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric'
        });
      } catch {
        return 'Fecha no disponible';
      }
    },
    
    showAddProductModal() {
      this.editingProduct = null;
      this.resetProductForm();
      this.showProductModal = true;
    },
    
    editProduct(product) {
      this.editingProduct = product;
      this.productForm = { 
        ...product,
        imagen_url: product.imagen,
        stock_minimo: 10,
        stock_maximo: 1000
      };
      this.uploadedFileName = '';
      this.selectedFile = null;
      
      if (product.imagen) {
        this.imagePreview = this.getProductImage(product.imagen);
      } else {
        this.imagePreview = null;
      }
      
      this.showProductModal = true;
    },
    
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (file) {
        // Validaciones
        if (file.size > 2 * 1024 * 1024) {
          alert('La imagen es demasiado grande. Máximo 2MB permitido.');
          event.target.value = '';
          return;
        }
        
        const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!validTypes.includes(file.type)) {
          alert('Formato de imagen no válido. Use JPEG, PNG, JPG, GIF o WEBP.');
          event.target.value = '';
          return;
        }
        
        this.selectedFile = file;
        this.uploadedFileName = file.name;
        this.productForm.imagen_url = '';
        
        // Crear vista previa
        const reader = new FileReader();
        reader.onload = (e) => {
          this.imagePreview = e.target.result;
        };
        reader.readAsDataURL(file);
      }
    },
    
    handleUrlInput() {
      if (this.productForm.imagen_url) {
        this.imagePreview = this.productForm.imagen_url;
        this.selectedFile = null;
        this.uploadedFileName = '';
      }
    },
    
    testImageUrl() {
      if (!this.productForm.imagen_url) return;
      
      const img = new Image();
      img.onload = () => {
        alert('✅ URL de imagen válida');
      };
      img.onerror = () => {
        alert('❌ No se pudo cargar la imagen desde la URL proporcionada');
      };
      img.src = this.productForm.imagen_url;
    },
    
    removeImagePreview() {
      this.imagePreview = null;
      this.selectedFile = null;
      this.uploadedFileName = '';
      this.productForm.imagen_url = '';
      
      const fileInput = document.querySelector('input[type="file"]');
      if (fileInput) fileInput.value = '';
    },
    
    resetProductForm() {
      this.productForm = {
        nombre: '',
        descripcion: '',
        precio: '',
        stock: 0,
        categoria_id: '',
        imagen_url: '',
        stock_minimo: 10,
        stock_maximo: 1000,
        activo: true
      };
      this.uploadedFileName = '';
      this.selectedFile = null;
      this.imagePreview = null;
    },
    
    closeProductModal() {
      this.showProductModal = false;
      this.editingProduct = null;
      this.resetProductForm();
    },
    
    async saveProduct() {
      // Validaciones
      if (!this.productForm.nombre || !this.productForm.categoria_id || !this.productForm.precio) {
        alert('❌ Por favor complete todos los campos obligatorios (*)');
        return;
      }
      
      this.processing = true;
      try {
        const formData = new FormData();
        
        // Agregar campos
        formData.append('nombre', this.productForm.nombre.trim());
        formData.append('descripcion', this.productForm.descripcion || '');
        formData.append('precio', parseFloat(this.productForm.precio) || 0);
        formData.append('stock', parseInt(this.productForm.stock) || 0);
        formData.append('categoria_id', parseInt(this.productForm.categoria_id));
        formData.append('activo', this.productForm.activo ? 1 : 0);
        formData.append('stock_minimo', parseInt(this.productForm.stock_minimo) || 10);
        formData.append('stock_maximo', parseInt(this.productForm.stock_maximo) || 1000);
        
        // Agregar imagen
        if (this.productForm.imagen_url && this.productForm.imagen_url.trim() !== '') {
          formData.append('imagen_url', this.productForm.imagen_url.trim());
        } else if (this.selectedFile) {
          formData.append('imagen', this.selectedFile);
        }
        
        if (this.editingProduct) {
          await adminService.updateProduct(this.editingProduct.id, formData);
          alert('✅ Producto actualizado correctamente');
        } else {
          await adminService.createProduct(formData);
          alert('✅ Producto creado correctamente');
        }
        
        await this.loadProducts();
        this.closeProductModal();
      } catch (error) {
        console.error('Error guardando producto:', error);
        alert(`❌ Error: ${error.response?.data?.error || error.message || 'Error desconocido'}`);
      } finally {
        this.processing = false;
      }
    },
    
    confirmDeleteProduct(product) {
      this.productToDelete = product;
      this.showDeleteModal = true;
    },
    
    async deleteProduct() {
      this.processing = true;
      try {
        await adminService.permanentDeleteProduct(this.productToDelete.id);
        alert('✅ Producto eliminado');
        await this.loadProducts();
        this.showDeleteModal = false;
      } catch (error) {
        console.error('Error eliminando producto:', error);
        alert('❌ Error al eliminar producto');
      } finally {
        this.processing = false;
        this.productToDelete = null;
      }
    },
    
    async toggleProductStatus(productId, activate) {
      try {
        if (activate) {
          await adminService.restoreProduct(productId);
          alert('✅ Producto activado');
        } else {
          await adminService.deleteProduct(productId);
          alert('✅ Producto desactivado');
        }
        await this.loadProducts();
      } catch (error) {
        console.error('Error cambiando estado del producto:', error);
        alert('❌ Error al cambiar estado del producto');
      }
    },
    
    handleImageError(event) {
      event.target.src = this.generatePlaceholder('Imagen no disponible');
    },
    
    exportProducts() {
      alert('📤 Función de exportación activada\nSe exportará el inventario de productos.');
    }
  }
}
</script>

<style scoped>
/* ===== ESTRUCTURA DE PÁGINA ===== */
.products-management-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  width: 100%;
}

/* ===== HEADER ===== */
.page-header {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  padding: 1.5rem 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
}

.header-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1400px;
  margin: 0 auto;
  width: 100%;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
  padding-left: 3rem;
}

.back-btn {
  background: rgba(255, 255, 255, 0.2);
  border: 2px solid rgba(255, 255, 255, 0.3);
  color: white;
  width: 40px;
  height: 40px;
  border-radius: 10px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.page-title h1 {
  margin: 0;
  font-size: 1.8rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.subtitle {
  margin: 0.3rem 0 0 0;
  opacity: 0.9;
  font-size: 0.95rem;
}

.header-stats {
  display: flex;
  gap: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.15);
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-weight: 500;
}

/* ===== PANEL DE CONTROL ===== */
.control-panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin: 2rem auto;
  max-width: 1400px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.panel-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1rem;
  align-items: end;
}

@media (max-width: 1200px) {
  .panel-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.search-section {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.search-box {
  position: relative;
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
}

.search-input {
  width: 70%;
  padding: 12px 12px 12px 45px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
}

.search-input:focus {
  outline: none;
  border-color: #059669;
  background: white;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.filters-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.filter-label {
  font-weight: 600;
  color: #475569;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-select {
  padding: 10px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: white;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
}

.filter-select:focus {
  outline: none;
  border-color: #059669;
}

.actions-section {
  display: flex;
  gap: 0.8rem;
  justify-content: flex-end;
}

.action-btn {
  padding: 10px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  font-size: 0.9rem;
  white-space: nowrap;
}

.reset-btn {
  background: #f1f5f9;
  color: #475569;
}

.reset-btn:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.refresh-btn {
  background: #3b82f6;
  color: white;
}

.refresh-btn:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-2px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.add-btn {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
}

.add-btn:hover {
  background: linear-gradient(135deg, #047857, #065f46);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

/* ===== PANEL DE CONTROL MEJORADO ===== */
.control-panel {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin: 1.5rem auto;
  max-width: 1400px;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
}

.panel-grid {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1.5rem;
  flex-wrap: nowrap;
}

/* Sección de búsqueda - Más compacta */
.search-section {
  flex: 2;
  min-width: 300px;
}

.search-box {
  position: relative;
  width: 100%;
}

.search-box i {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 0.9rem;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 40px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  background: #f8fafc;
  height: 42px;
}

.search-input:focus {
  outline: none;
  border-color: #059669;
  background: white;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

/* Sección de filtros en línea */
.filters-section {
  flex: 1.5;
  min-width: 250px;
}

.filter-row {
  display: flex;
  gap: 0.8rem;
  align-items: center;
}

.filter-group {
  flex: 1;
  position: relative;
}

.filter-select.compact {
  width: 100%;
  padding: 8px 12px;
  padding-right: 32px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.85rem;
  background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") no-repeat right 12px center;
  background-size: 12px;
  color: #334155;
  cursor: pointer;
  height: 42px;
  appearance: none;
  transition: all 0.2s ease;
}

.filter-select.compact:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.1);
}

/* Sección de acciones más compacta */
.actions-section {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-shrink: 0;
}

.action-btn {
  padding: 8px 14px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
  font-size: 0.85rem;
  height: 42px;
  min-width: 42px;
}

.action-btn.icon-btn {
  width: 42px;
  padding: 0;
  justify-content: center;
}

.action-btn i {
  font-size: 0.9rem;
}

.reset-btn {
  background: #f1f5f9;
  color: #475569;
  border: 1px solid #e2e8f0;
}

.reset-btn:hover {
  background: #e2e8f0;
  transform: translateY(-1px);
}

.refresh-btn {
  background: #3b82f6;
  color: white;
}

.refresh-btn:hover:not(:disabled) {
  background: #2563eb;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.add-btn {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  padding: 8px 16px;
  white-space: nowrap;
}

.add-btn:hover {
  background: linear-gradient(135deg, #047857, #065f46);
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(5, 150, 105, 0.2);
}

/* RESPONSIVE */
@media (max-width: 1200px) {
  .panel-grid {
    gap: 1rem;
  }
  
  .search-section {
    min-width: 250px;
  }
  
  .filters-section {
    min-width: 220px;
  }
  
  .filter-row {
    flex-direction: column;
    gap: 0.5rem;
  }
}

@media (max-width: 992px) {
  .panel-grid {
    flex-wrap: wrap;
    gap: 1rem;
  }
  
  .search-section {
    flex: 1 100%;
    order: 1;
  }
  
  .filters-section {
    flex: 1;
    order: 2;
    min-width: auto;
  }
  
  .actions-section {
    flex: 1;
    order: 3;
    justify-content: flex-end;
  }
  
  .filter-row {
    flex-direction: row;
  }
}

@media (max-width: 768px) {
  .control-panel {
    padding: 1rem;
  }
  
  .panel-grid {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-section,
  .filters-section,
  .actions-section {
    width: 100%;
  }
  
  .filter-row {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .actions-section {
    justify-content: center;
    margin-top: 0.5rem;
  }
}

@media (max-width: 480px) {
  .action-btn {
    padding: 8px 12px;
    font-size: 0.8rem;
  }
  
  .add-btn {
    flex: 1;
  }
  
  .search-input {
    font-size: 0.85rem;
  }
  
  .filter-select.compact {
    font-size: 0.85rem;
  }
}
/* ===== ESTADÍSTICAS RÁPIDAS ===== */
.quick-stats {
  max-width: 1400px;
  margin: 0 auto 2rem;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
}

.stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.stat-card.total:hover {
  border-color: #059669;
}

.stat-card.active:hover {
  border-color: #10b981;
}

.stat-card.low-stock:hover {
  border-color: #f59e0b;
}

.stat-card.revenue:hover {
  border-color: #8b5cf6;
}

.stat-icon {
  width: 60px;
  height: 60px;
  background: #f1f5f9;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  color: #059669;
}

.stat-card.active .stat-icon {
  background: #d1fae5;
  color: #065f46;
}

.stat-card.low-stock .stat-icon {
  background: #fef3c7;
  color: #92400e;
}

.stat-card.revenue .stat-icon {
  background: #ede9fe;
  color: #5b21b6;
}

.stat-info h3 {
  margin: 0 0 0.3rem 0;
  font-size: 1.8rem;
  color: #1e293b;
  font-weight: 700;
}

.stat-info p {
  margin: 0;
  color: #64748b;
  font-size: 0.95rem;
}

/* ===== CONTENIDO PRINCIPAL ===== */
.content-wrapper {
  max-width: 1400px;
  margin: 0 auto;
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

/* ===== ESTADOS ===== */
.loading-state,
.error-state,
.empty-state {
  padding: 4rem 2rem;
  text-align: center;
}

.loading-spinner {
  margin-bottom: 1.5rem;
}

.loading-spinner i {
  font-size: 3rem;
  color: #059669;
}

.error-content,
.empty-content {
  max-width: 400px;
  margin: 0 auto;
}

.error-content i,
.empty-content i {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

.error-content i {
  color: #ef4444;
}

.empty-content i {
  color: #94a3b8;
}

.error-content h4,
.empty-content h4 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  font-size: 1.5rem;
}

.error-content p,
.empty-content p {
  margin: 0 0 1.5rem 0;
  color: #64748b;
  font-size: 1.1rem;
}

.btn-primary {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #047857, #065f46);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.empty-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1.5rem;
}

/* ===== LISTA DE PRODUCTOS ===== */
.products-container {
  padding: 2rem;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f1f5f9;
}

.products-header h3 {
  margin: 0;
  color: #1e3a8a;
  font-size: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}

.export-btn {
  background: #10b981;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.export-btn:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
}

/* ===== GRID DE PRODUCTOS ===== */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .products-grid {
    grid-template-columns: 1fr;
  }
}

/* ===== TARJETA DE PRODUCTO ===== */
.product-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
}

.product-card:hover {
  border-color: #059669;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  transform: translateY(-3px);
}

.product-card.inactive {
  opacity: 0.7;
  border-color: #94a3b8;
}

.product-card.low-stock {
  border-left: 4px solid #f59e0b;
}

.product-image-section {
  position: relative;
  height: 180px;
  overflow: hidden;
}

.product-image {
  width: 100%;
  height: 100%;
  position: relative;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-img {
  transform: scale(1.05);
}

.stock-warning {
  position: absolute;
  top: 10px;
  left: 10px;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.inactive-badge {
  position: absolute;
  top: 10px;
  right: 10px;
  background: linear-gradient(135deg, #6b7280, #4b5563);
  color: white;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
}

.quick-actions {
  position: absolute;
  bottom: 10px;
  right: 10px;
  display: flex;
  gap: 5px;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.product-card:hover .quick-actions {
  opacity: 1;
}

.quick-btn {
  width: 32px;
  height: 32px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.quick-btn:hover {
  transform: scale(1.1);
}

.edit-btn {
  background: rgba(59, 130, 246, 0.9);
}

.activate-btn {
  background: rgba(16, 185, 129, 0.9);
}

.deactivate-btn {
  background: rgba(245, 158, 11, 0.9);
}

.delete-btn {
  background: rgba(239, 68, 68, 0.9);
}

/* ===== CONTENIDO DE LA TARJETA ===== */
.product-content {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.product-name {
  margin: 0;
  font-size: 1.1rem;
  font-weight: 700;
  color: #1e293b;
  flex: 1;
}

.product-price {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.2rem;
}

.price-label {
  font-size: 0.8rem;
  color: #64748b;
}

.price-value {
  font-size: 1.3rem;
  font-weight: 700;
  color: #059669;
}

.product-description {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.5;
  flex: 1;
}

.product-details {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1rem;
}

.detail-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
  margin-bottom: 1rem;
}

.detail-row:last-child {
  margin-bottom: 0;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.detail-item i {
  color: #059669;
  font-size: 0.9rem;
  width: 20px;
}

.detail-content {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.detail-label {
  font-size: 0.75rem;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.9rem;
  color: #1e293b;
  font-weight: 500;
}

.detail-value.low {
  color: #dc2626;
  font-weight: 600;
}

/* ===== INDICADORES ===== */
.product-indicators {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  margin-top: auto;
}

.indicator {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.status-indicator {
  background: #f1f5f9;
}

.status-indicator .indicator-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.status-indicator .indicator-dot.active {
  background: #10b981;
}

.status-indicator .indicator-dot.inactive {
  background: #6b7280;
}

.indicator-text {
  color: #475569;
}

.stock-indicator {
  background: #fef3c7;
  color: #92400e;
}

.price-indicator {
  background: #e0e7ff;
  color: #3730a3;
}

/* ===== PAGINACIÓN ===== */
.pagination {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 0;
  margin-top: 2rem;
  border-top: 2px solid #f1f5f9;
}

.pagination-info {
  color: #64748b;
  font-size: 0.95rem;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-btn {
  width: 40px;
  height: 40px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  color: #475569;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #059669;
  color: #059669;
  background: #f0fdf4;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-weight: 600;
  color: #1e293b;
}

/* ===== MODALES ===== */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
  overflow-y: auto;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 800px;
  max-height: 90vh;
  display: flex; /* ← AÑADIDO */
  flex-direction: column; /* ← AÑADIDO */
  overflow: hidden; /* ← CAMBIADO de 'auto' a 'hidden' */
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease;
}
.modal-body {
  overflow-y: auto; /* ← AÑADIDO */
  flex-grow: 1; /* ← AÑADIDO */
  max-height: calc(90vh - 140px); /* ← AÑADIDO */
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: sticky;
  top: 0;
  z-index: 1;
}

.modal-header h3 {
  margin: 0;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.close-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.close-btn:hover {
  background: rgba(255, 255, 255, 0.3);
}

.modal-body {
  padding: 2rem;
}

/* ===== FORMULARIO DE PRODUCTO ===== */
.product-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: #f8fafc;
  border-radius: 10px;
  padding: 1.5rem;
  border: 1px solid #e2e8f0;
}

.form-section h4 {
  margin: 0 0 1.5rem 0;
  color: #1e3a8a;
  font-size: 1.2rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding-bottom: 0.8rem;
  border-bottom: 2px solid #e2e8f0;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-label {
  font-weight: 600;
  color: #475569;
  font-size: 0.95rem;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.form-input,
.form-textarea,
.form-select {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
  outline: none;
  border-color: #059669;
  box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-select {
  cursor: pointer;
}

.input-with-icon {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-weight: 600;
}

.input-with-icon .form-input {
  padding-left: 40px;
}

/* ===== SECCIÓN DE IMAGEN ===== */
.image-upload-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.image-preview-container {
  text-align: center;
  position: relative;
}

.image-preview {
  max-width: 100%;
  max-height: 200px;
  border-radius: 10px;
  border: 3px solid #e2e8f0;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.remove-image-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: #ef4444;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 4px;
  transition: all 0.3s ease;
}

.remove-image-btn:hover {
  background: #dc2626;
  transform: translateY(-2px);
}

.image-options {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.upload-option {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.upload-btn {
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  padding: 12px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
  justify-content: center;
}

.upload-btn:hover {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-2px);
}

.upload-help {
  color: #64748b;
  font-size: 0.85rem;
}

.upload-filename {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #059669;
  font-weight: 500;
  padding: 8px 12px;
  background: #f0fdf4;
  border-radius: 6px;
  border: 1px solid #86efac;
}

.separator {
  text-align: center;
  position: relative;
}

.separator::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  height: 1px;
  background: #e2e8f0;
}

.separator-text {
  background: white;
  padding: 0 15px;
  color: #64748b;
  font-weight: 600;
  position: relative;
  z-index: 1;
}

.url-option {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.url-input-group {
  display: flex;
  gap: 0.5rem;
}

.url-input-group .form-input {
  flex: 1;
}

.test-url-btn {
  background: #059669;
  color: white;
  border: none;
  width: 48px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.test-url-btn:hover {
  background: #047857;
}

.help-link {
  color: #3b82f6;
  text-decoration: none;
  font-weight: 500;
}

.help-link:hover {
  text-decoration: underline;
}

/* ===== TOGGLE DE ESTADO ===== */
.status-toggle {
  display: flex;
  gap: 0.5rem;
  background: #f1f5f9;
  padding: 4px;
  border-radius: 8px;
}

.toggle-btn {
  flex: 1;
  padding: 10px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  background: transparent;
  color: #64748b;
}

.toggle-btn.selected {
  background: white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.toggle-btn.active.selected {
  color: #059669;
}

.toggle-btn.inactive.selected {
  color: #ef4444;
}

/* ===== ACCIONES DEL FORMULARIO ===== */
.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding-top: 1.5rem;
  border-top: 2px solid #f1f5f9;
}

.cancel-btn {
  background: #f1f5f9;
  color: #475569;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cancel-btn:hover:not(:disabled) {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.submit-btn {
  background: linear-gradient(135deg, #059669, #047857);
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #047857, #065f46);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(5, 150, 105, 0.2);
}

.cancel-btn:disabled,
.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

/* ===== MODAL DE CONFIRMACIÓN ===== */
.confirm-modal {
  max-width: 500px;
}

.confirm-content {
  text-align: center;
  padding: 1.5rem 0;
}

.confirm-icon {
  font-size: 3rem;
  color: #ef4444;
  margin-bottom: 1.5rem;
}

.confirm-content h4 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  font-size: 1.4rem;
}

.confirm-content p {
  margin: 0 0 1rem 0;
  color: #64748b;
  line-height: 1.6;
}

.confirm-details {
  background: #fef2f2;
  border-radius: 8px;
  padding: 1rem;
  margin: 1.5rem 0;
  text-align: left;
}

.confirm-details p {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #991b1b;
  font-size: 0.95rem;
  margin: 0.5rem 0;
}

.confirm-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.delete-btn {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
}

.delete-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

/* ===== RESPONSIVE ADICIONAL ===== */
@media (max-width: 576px) {
  .page-header {
    padding: 1rem;
  }
  
  .page-main {
    padding: 1rem;
  }
  
  .control-panel,
  .content-wrapper {
    border-radius: 8px;
  }
  
  .products-container {
    padding: 1rem;
  }
  
  .modal-body {
    padding: 1rem;
  }
  
  .form-actions,
  .confirm-actions {
    flex-direction: column;
  }
  
  .cancel-btn,
  .submit-btn,
  .delete-btn {
    width: 100%;
    justify-content: center;
  }
  
  .empty-actions {
    flex-direction: column;
  }
}
</style>