<template>
  <div class="categories-management-page">
    <!-- Header fijo -->
    <header class="page-header">
      <div class="header-content">
           <button class="back-btn" @click="$router.push('/admin')">
            <i class="fas fa-arrow-left"></i>
          </button>
        <div class="header-left">
       
          <div class="page-title">
            <h1>
              <i class="fas fa-tags"></i>
              Gestión de Categorías
            </h1>
            <p class="subtitle">Organiza y administra las categorías de productos</p>
          </div>
        </div>
        
        <div class="header-right">
          <div class="header-stats">
            <span class="stat-item">
              <i class="fas fa-tag"></i>
              {{ stats.total || 0 }} Categorías
            </span>
            <span class="stat-item">
              <i class="fas fa-cube"></i>
              {{ stats.totalProductos || 0 }} Productos
            </span>
          </div>
        </div>
      </div>
    </header>

    <main class="page-main">
      <!-- Panel de control -->
      <div class="control-panel">
        <div class="panel-grid">
          <!-- Búsqueda -->
          <div class="search-section">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input 
                v-model="searchQuery"
                type="text" 
                placeholder="Buscar categorías por nombre o descripción..."
                class="search-input"
                @input="filterCategories"
              >
            </div>
            <div v-if="searchQuery" class="search-helper">
              <i class="fas fa-info-circle"></i>
              Buscando: "{{ searchQuery }}"
            </div>
          </div>
          
          <!-- Acciones -->
          <div class="actions-section">
            <button class="action-btn reset-btn" @click="resetFilters">
              <i class="fas fa-times"></i> Limpiar
            </button>
            <button class="action-btn refresh-btn" @click="loadCategories" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Actualizar
            </button>
            <button class="action-btn add-btn" @click="showCreateModal = true">
              <i class="fas fa-plus"></i> Nueva Categoría
            </button>
          </div>
        </div>
      </div>

      <!-- Estadísticas rápidas -->
      <div class="quick-stats">
        <div class="stats-grid">
          <div class="stat-card total" @click="resetFilters">
            <div class="stat-icon">
              <i class="fas fa-tags"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.total || 0 }}</h3>
              <p>Total Categorías</p>
            </div>
          </div>
          
          <div class="stat-card active" @click="filterByProductCount('high')">
            <div class="stat-icon">
              <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.populares || 0 }}</h3>
              <p>Categorías Populares</p>
            </div>
          </div>
          
          <div class="stat-card low" @click="filterByProductCount('low')">
            <div class="stat-icon">
              <i class="fas fa-exclamation-circle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.vacias || 0 }}</h3>
              <p>Categorías Vacías</p>
            </div>
          </div>
          
          <div class="stat-card avg">
            <div class="stat-icon">
              <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.promedioProductos || 0 }}</h3>
              <p>Promedio por categoría</p>
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
          <p>Cargando categorías...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <div class="error-content">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>Error al cargar categorías</h4>
            <p>{{ error }}</p>
            <button @click="loadCategories" class="btn-primary">
              <i class="fas fa-redo-alt"></i> Reintentar
            </button>
          </div>
        </div>

        <div v-else-if="filteredCategories.length === 0" class="empty-state">
          <div class="empty-content">
            <i class="fas fa-tags"></i>
            <h4>No hay categorías</h4>
            <p v-if="searchQuery">
              No se encontraron categorías con la búsqueda: "{{ searchQuery }}"
            </p>
            <p v-else>
              No hay categorías registradas en el sistema.
            </p>
            <div class="empty-actions">
              <button @click="resetFilters" class="btn-secondary">
                <i class="fas fa-times"></i> Limpiar filtros
              </button>
              <button @click="showCreateModal = true" class="btn-primary">
                <i class="fas fa-plus"></i> Crear primera categoría
              </button>
            </div>
          </div>
        </div>

        <!-- Grid de categorías -->
        <div v-else class="categories-container">
          <div class="categories-header">
            <h3>
              <i class="fas fa-list"></i>
              Lista de Categorías ({{ filteredCategories.length }})
            </h3>
            <div class="categories-actions">
              <button class="export-btn" @click="exportCategories">
                <i class="fas fa-file-export"></i> Exportar
              </button>
            </div>
          </div>

          <div class="categories-grid">
            <div 
              v-for="category in filteredCategories" 
              :key="category.id"
              class="category-card"
              :class="{ 
                'popular': category.total_productos > 10,
                'empty': category.total_productos === 0
              }"
            >
              <!-- Cabecera de la categoría -->
              <div class="category-header">
                <div class="category-title-section">
                  <div class="category-icon">
                    <i class="fas fa-folder"></i>
                  </div>
                  <div class="category-title">
                    <h4 class="category-name">{{ category.nombre }}</h4>
                    <div class="category-slug">
                      <i class="fas fa-link"></i>
                      /{{ category.slug }}
                    </div>
                  </div>
                </div>
                
                <div class="category-stats">
                  <div class="product-count">
                    <span class="count-icon">
                      <i class="fas fa-cube"></i>
                    </span>
                    <span class="count-number">{{ category.total_productos || 0 }}</span>
                    <span class="count-label">productos</span>
                  </div>
                  <div class="category-status" :class="{ 'empty': category.total_productos === 0 }">
                    <span class="status-dot"></span>
                    <span class="status-text">
                      {{ category.total_productos > 0 ? 'Con productos' : 'Vacía' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Descripción -->
              <div v-if="category.descripcion" class="category-description">
                <p>{{ category.descripcion }}</p>
              </div>
              <div v-else class="category-no-description">
                <i class="fas fa-info-circle"></i>
                <span>Sin descripción</span>
              </div>

              <!-- Productos de la categoría -->
              <div v-if="category.total_productos > 0" class="category-products">
                <div class="products-header">
                  <button 
                    class="toggle-products-btn"
                    @click="toggleProducts(category.id)"
                  >
                    <i class="fas" :class="expandedCategories.includes(category.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    <span>Productos en esta categoría ({{ category.total_productos }})</span>
                  </button>
                  <span class="products-preview">
                    {{ getProductPreview(category) }}
                  </span>
                </div>
                
                <div v-if="expandedCategories.includes(category.id)" class="products-list">
                  <div class="products-list-header">
                    <span>Producto</span>
                    <span>Precio</span>
                    <span>Stock</span>
                    <span>Estado</span>
                  </div>
                  <div 
                    v-for="product in category.productos" 
                    :key="product.id"
                    class="product-item"
                  >
                    <div class="product-info">
                      <span class="product-name">{{ product.nombre }}</span>
                      <span class="product-sku">{{ product.codigo || 'Sin código' }}</span>
                    </div>
                    <span class="product-price">${{ parseFloat(product.precio).toFixed(2) }}</span>
                    <span class="product-stock" :class="{ 'low': product.stock < 10 }">
                      {{ product.stock }}
                    </span>
                    <span class="product-status" :class="product.activo ? 'active' : 'inactive'">
                      <i class="fas" :class="product.activo ? 'fa-check-circle' : 'fa-times-circle'"></i>
                      {{ product.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Acciones -->
              <div class="category-actions">
                <div class="action-buttons">
                  <button 
                    class="action-btn edit-btn"
                    @click="editCategory(category)"
                    title="Editar categoría"
                  >
                    <i class="fas fa-edit"></i>
                    <span class="btn-text">Editar</span>
                  </button>

                  <button 
                    v-if="category.total_productos > 0"
                    class="action-btn view-btn"
                    @click="toggleProducts(category.id)"
                    :title="expandedCategories.includes(category.id) ? 'Ocultar productos' : 'Ver productos'"
                  >
                    <i class="fas" :class="expandedCategories.includes(category.id) ? 'fa-eye-slash' : 'fa-eye'"></i>
                    <span class="btn-text">
                      {{ expandedCategories.includes(category.id) ? 'Ocultar' : 'Ver' }}
                    </span>
                  </button>

                  <button 
                    class="action-btn delete-btn"
                    @click="confirmDelete(category)"
                    :disabled="category.total_productos > 0"
                    :title="category.total_productos > 0 ? 'No se puede eliminar: tiene productos' : 'Eliminar categoría'"
                  >
                    <i class="fas fa-trash"></i>
                    <span class="btn-text">Eliminar</span>
                  </button>
                </div>
                
                <div v-if="category.total_productos > 0" class="category-usage">
                  <i class="fas fa-info-circle"></i>
                  <span>Usada en {{ category.total_productos }} productos</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Paginación -->
          <div v-if="filteredCategories.length > 12" class="pagination">
            <div class="pagination-info">
              Mostrando {{ Math.min(filteredCategories.length, 12) }} de {{ filteredCategories.length }} categorías
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="page-info">Página {{ currentPage }}</span>
              <button class="pagination-btn" :disabled="currentPage * 12 >= filteredCategories.length">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal para crear/editar categoría -->
    <div v-if="showCreateModal || editingCategory" class="modal-overlay" @click.self="closeModal">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>
            <i class="fas" :class="editingCategory ? 'fa-edit' : 'fa-plus'"></i>
            {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
          </h3>
          <button class="close-btn" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>
        
        <div class="modal-body">
          <form @submit.prevent="saveCategory" class="category-form">
            <div class="form-section">
              <h4><i class="fas fa-info-circle"></i> Información de la categoría</h4>
              
              <div class="form-grid">
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-tag"></i> Nombre de la categoría *
                  </label>
                  <input
                    v-model="categoryForm.nombre"
                    type="text"
                    required
                    placeholder="Ej: Medicamentos, Suplementos, Cuidado Personal..."
                    class="form-input"
                    @input="generateSlug"
                  >
                  <small class="form-help">Este nombre aparecerá en la tienda</small>
                </div>
                
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-link"></i> Slug (URL)
                  </label>
                  <div class="slug-input-group">
                    <span class="slug-prefix">/categorias/</span>
                    <input
                      v-model="categoryForm.slug"
                      type="text"
                      placeholder="medicamentos"
                      class="form-input slug-input"
                    >
                  </div>
                  <small class="form-help">URL amigable para la categoría</small>
                </div>
                
                <div class="form-group full-width">
                  <label class="form-label">
                    <i class="fas fa-align-left"></i> Descripción
                  </label>
                  <textarea
                    v-model="categoryForm.descripcion"
                    placeholder="Describe esta categoría para los clientes..."
                    rows="4"
                    class="form-textarea"
                  ></textarea>
                  <small class="form-help">Opcional - Aparecerá en la página de la categoría</small>
                </div>
              </div>
            </div>
            
            <!-- Sección de icono (opcional) -->
            <div class="form-section">
              <h4><i class="fas fa-image"></i> Icono de la categoría</h4>
              <div class="icon-selection">
                <div class="icon-options">
                  <button 
                    v-for="icon in iconOptions" 
                    :key="icon"
                    type="button"
                    class="icon-option"
                    :class="{ 'selected': categoryForm.icono === icon }"
                    @click="categoryForm.icono = icon"
                  >
                    <i :class="icon"></i>
                  </button>
                </div>
                <small class="form-help">
                  Selecciona un icono que represente la categoría
                </small>
              </div>
            </div>
            
            <!-- Acciones del formulario -->
            <div class="form-actions">
              <button type="button" class="cancel-btn" @click="closeModal" :disabled="saving">
                Cancelar
              </button>
              <button 
                type="submit" 
                class="submit-btn"
                :disabled="!categoryForm.nombre || saving"
              >
                <i class="fas fa-spinner fa-spin" v-if="saving"></i>
                <span v-else>
                  {{ editingCategory ? 'Actualizar Categoría' : 'Crear Categoría' }}
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
            <h4>¿Eliminar categoría?</h4>
            
            <div v-if="categoryToDelete?.total_productos > 0" class="delete-warning">
              <div class="warning-header">
                <i class="fas fa-exclamation-circle"></i>
                <span>¡Precaución!</span>
              </div>
              <p>
                La categoría <strong>"{{ categoryToDelete?.nombre }}"</strong> tiene 
                <strong>{{ categoryToDelete?.total_productos }}</strong> productos asociados.
              </p>
              <p class="warning-text">
                No puedes eliminar categorías que contengan productos. Primero reasigna 
                los productos a otra categoría o elimínalos.
              </p>
            </div>
            
            <div v-else>
              <p>
                Estás a punto de eliminar la categoría 
                <strong>"{{ categoryToDelete?.nombre }}"</strong> permanentemente.
              </p>
              <div class="confirm-details">
                <p><i class="fas fa-info-circle"></i> Esta acción no se puede deshacer.</p>
                <p><i class="fas fa-exclamation-circle"></i> La categoría será eliminada del sistema.</p>
              </div>
            </div>
          </div>
          
          <div class="confirm-actions">
            <button class="cancel-btn" @click="showDeleteModal = false" :disabled="saving">
              Cancelar
            </button>
            <button 
              v-if="!categoryToDelete?.total_productos"
              class="delete-btn" 
              @click="deleteCategory"
              :disabled="saving"
            >
              <i class="fas fa-trash" v-if="!saving"></i>
              <i class="fas fa-spinner fa-spin" v-else></i>
              Eliminar Permanentemente
            </button>
            <button 
              v-else
              class="close-btn-secondary"
              @click="showDeleteModal = false"
            >
              Entendido
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/adminService';

export default {
  name: 'CategoriesManagementPage',
  data() {
    return {
      categories: [],
      filteredCategories: [],
      loading: false,
      error: null,
      searchQuery: '',
      expandedCategories: [],
      showCreateModal: false,
      editingCategory: null,
      showDeleteModal: false,
      categoryToDelete: null,
      saving: false,
      currentPage: 1,
      stats: {
        total: 0,
        totalProductos: 0,
        populares: 0,
        vacias: 0,
        promedioProductos: 0
      },
      categoryForm: {
        nombre: '',
        descripcion: '',
        slug: '',
        icono: 'fas fa-tag'
      },
      iconOptions: [
        'fas fa-tag',
        'fas fa-capsules',
        'fas fa-heart',
        'fas fa-stethoscope',
        'fas fa-thermometer',
        'fas fa-pills',
        'fas fa-syringe',
        'fas fa-band-aid',
        'fas fa-eye-dropper',
        'fas fa-prescription-bottle',
        'fas fa-spray-can',
        'fas fa-soap',
        'fas fa-hand-sparkles',
        'fas fa-weight',
        'fas fa-dumbbell',
        'fas fa-apple-alt',
        'fas fa-baby',
        'fas fa-user-md'
      ]
    };
  },
  async mounted() {
    await this.loadCategories();
  },
  methods: {
    async loadCategories() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await adminService.getAllCategories();
        
        if (response.success && response.categorias) {
          this.categories = response.categorias;
          this.filteredCategories = [...this.categories];
          this.calculateStats();
        } else {
          // Datos de ejemplo para desarrollo
          this.loadSampleData();
        }
      } catch (error) {
        console.error('Error cargando categorías:', error);
        this.error = 'Error al cargar las categorías';
        // Datos de ejemplo para desarrollo
        this.loadSampleData();
      } finally {
        this.loading = false;
      }
    },
    
    loadSampleData() {
      this.categories = [
        {
          id: 1,
          nombre: 'Medicamentos',
          descripcion: 'Medicamentos de venta libre y recetados para diferentes tratamientos.',
          slug: 'medicamentos',
          total_productos: 15,
          productos: [
            { id: 1, nombre: 'Paracetamol 500mg', precio: 25.50, stock: 45, activo: true, codigo: 'MED-001' },
            { id: 2, nombre: 'Ibuprofeno 400mg', precio: 35.75, stock: 28, activo: true, codigo: 'MED-002' },
            { id: 3, nombre: 'Amoxicilina 500mg', precio: 89.90, stock: 12, activo: true, codigo: 'MED-003' }
          ]
        },
        {
          id: 2,
          nombre: 'Cuidado Personal',
          descripcion: 'Productos para el cuidado diario e higiene personal.',
          slug: 'cuidado-personal',
          total_productos: 8,
          productos: [
            { id: 4, nombre: 'Jabón Antibacterial', precio: 15.50, stock: 120, activo: true, codigo: 'CUI-001' },
            { id: 5, nombre: 'Shampoo Anticaída', precio: 45.00, stock: 36, activo: true, codigo: 'CUI-002' }
          ]
        },
        {
          id: 3,
          nombre: 'Suplementos Alimenticios',
          descripcion: 'Vitaminas y suplementos para complementar la nutrición.',
          slug: 'suplementos',
          total_productos: 6,
          productos: [
            { id: 6, nombre: 'Vitaminas C 1000mg', precio: 89.99, stock: 24, activo: true, codigo: 'SUP-001' },
            { id: 7, nombre: 'Omega 3', precio: 120.00, stock: 18, activo: true, codigo: 'SUP-002' }
          ]
        },
        {
          id: 4,
          nombre: 'Primeros Auxilios',
          descripcion: 'Material e instrumentos para primeros auxilios básicos.',
          slug: 'primeros-auxilios',
          total_productos: 4,
          productos: [
            { id: 8, nombre: 'Curitas Antisépticas', precio: 8.99, stock: 200, activo: true, codigo: 'AUX-001' },
            { id: 9, nombre: 'Gasas Esterilizadas', precio: 25.00, stock: 45, activo: true, codigo: 'AUX-002' }
          ]
        },
        {
          id: 5,
          nombre: 'Maternidad y Bebés',
          descripcion: 'Productos especializados para madres y bebés.',
          slug: 'maternidad-bebes',
          total_productos: 0,
          productos: []
        },
        {
          id: 6,
          nombre: 'Equipo Médico',
          descripcion: 'Instrumentos y equipos médicos para el hogar.',
          slug: 'equipo-medico',
          total_productos: 3,
          productos: [
            { id: 10, nombre: 'Termómetro Digital', precio: 120.00, stock: 15, activo: true, codigo: 'EQU-001' },
            { id: 11, nombre: 'Tensiómetro Digital', precio: 450.00, stock: 8, activo: true, codigo: 'EQU-002' }
          ]
        }
      ];
      this.filteredCategories = [...this.categories];
      this.calculateStats();
    },
    
    calculateStats() {
      const totalProductos = this.categories.reduce((sum, cat) => sum + (cat.total_productos || 0), 0);
      const vacias = this.categories.filter(cat => !cat.total_productos || cat.total_productos === 0).length;
      const populares = this.categories.filter(cat => cat.total_productos > 10).length;
      
      this.stats = {
        total: this.categories.length,
        totalProductos: totalProductos,
        populares: populares,
        vacias: vacias,
        promedioProductos: totalProductos > 0 ? Math.round(totalProductos / this.categories.length) : 0
      };
    },
    
    filterCategories() {
      if (!this.searchQuery) {
        this.filteredCategories = [...this.categories];
        return;
      }
      
      const query = this.searchQuery.toLowerCase();
      this.filteredCategories = this.categories.filter(category => 
        category.nombre.toLowerCase().includes(query) ||
        (category.descripcion && category.descripcion.toLowerCase().includes(query)) ||
        category.slug.toLowerCase().includes(query)
      );
    },
    
    resetFilters() {
      this.searchQuery = '';
      this.filteredCategories = [...this.categories];
    },
    
    filterByProductCount(type) {
      if (type === 'high') {
        this.filteredCategories = this.categories.filter(cat => cat.total_productos > 10);
      } else if (type === 'low') {
        this.filteredCategories = this.categories.filter(cat => !cat.total_productos || cat.total_productos === 0);
      }
    },
    
    toggleProducts(categoryId) {
      const index = this.expandedCategories.indexOf(categoryId);
      if (index > -1) {
        this.expandedCategories.splice(index, 1);
      } else {
        this.expandedCategories.push(categoryId);
      }
    },
    
    getProductPreview(category) {
      if (!category.productos || category.productos.length === 0) return '';
      
      const productNames = category.productos.slice(0, 2).map(p => p.nombre);
      let preview = productNames.join(', ');
      
      if (category.productos.length > 2) {
        preview += ` y ${category.productos.length - 2} más`;
      }
      
      return preview;
    },
    
    editCategory(category) {
      this.editingCategory = category;
      this.categoryForm = {
        nombre: category.nombre,
        descripcion: category.descripcion || '',
        slug: category.slug || '',
        icono: category.icono || 'fas fa-tag'
      };
      this.showCreateModal = true;
    },
    
    generateSlug() {
      if (!this.categoryForm.nombre) return;
      
      this.categoryForm.slug = this.categoryForm.nombre
        .toLowerCase()
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Elimina acentos
        .replace(/[^a-z0-9]+/g, '-') // Reemplaza espacios y caracteres especiales
        .replace(/^-+|-+$/g, ''); // Elimina guiones al inicio y final
    },
    
    async saveCategory() {
      if (!this.categoryForm.nombre) {
        alert('Por favor ingresa un nombre para la categoría');
        return;
      }
      
      this.saving = true;
      
      try {
        let response;
        
        if (this.editingCategory) {
          response = await adminService.updateCategory(this.editingCategory.id, this.categoryForm);
        } else {
          response = await adminService.createCategory(this.categoryForm);
        }

        if (response.success) {
          await this.loadCategories();
          this.closeModal();
          alert(`✅ Categoría ${this.editingCategory ? 'actualizada' : 'creada'} correctamente`);
        } else {
          this.error = response.message || 'Error al guardar la categoría';
          alert(this.error);
        }
      } catch (error) {
        console.error('Error saving category:', error);
        alert(`❌ Error: ${error.message || 'Error de conexión al guardar categoría'}`);
      } finally {
        this.saving = false;
      }
    },
    
    confirmDelete(category) {
      this.categoryToDelete = category;
      this.showDeleteModal = true;
    },
    
    async deleteCategory() {
      if (!this.categoryToDelete || this.categoryToDelete.total_productos > 0) return;
      
      this.saving = true;
      
      try {
        const response = await adminService.deleteCategory(this.categoryToDelete.id);
        
        if (response.success) {
          await this.loadCategories();
          alert('✅ Categoría eliminada correctamente');
        } else {
          alert(response.message || 'Error al eliminar la categoría');
        }
      } catch (error) {
        console.error('Error deleting category:', error);
        alert(`❌ Error: ${error.message || 'Error de conexión al eliminar categoría'}`);
      } finally {
        this.saving = false;
        this.showDeleteModal = false;
        this.categoryToDelete = null;
      }
    },
    
    closeModal() {
      this.showCreateModal = false;
      this.editingCategory = null;
      this.categoryForm = {
        nombre: '',
        descripcion: '',
        slug: '',
        icono: 'fas fa-tag'
      };
    },
    
    exportCategories() {
      alert('📤 Función de exportación activada\nSe exportará la lista de categorías.');
    }
  }
};
</script>

<style scoped>
/* ===== ESTRUCTURA DE PÁGINA ===== */
.categories-management-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
  width: 100%;
}

/* ===== HEADER ===== */
.page-header {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  padding: 1.5rem 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
  position: sticky;
  top: 0;
  z-index: 100;
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
  border-radius: 16px;
  padding: 1.75rem;
  margin: 1rem auto;
  max-width: 1400px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  position: sticky;
  top: 90px;
  z-index: 50;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.95);
}

.panel-grid {
  display: grid;
  grid-template-columns: 1.5fr auto;
  gap: 1.5rem;
  align-items: end;
}

/* BÚSQUEDA */
.search-section {
  grid-column: 1 / 2;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.search-box {
  position: relative;
  background: #f8fafc;
  border-radius: 12px;
  padding: 0.5rem;
  border: 2px solid transparent;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.search-box:focus-within {
  border-color: #8b5cf6;
  background: white;
  box-shadow: 0 4px 20px rgba(139, 92, 246, 0.15);
  transform: translateY(-1px);
}

.search-box i {
  position: absolute;
  left: 20px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 1.1rem;
  z-index: 1;
}

.search-input {
  width: 100%;
  padding: 14px 20px 14px 50px;
  border: none;
  border-radius: 10px;
  font-size: 1rem;
  font-weight: 500;
  background: transparent;
  color: #1e293b;
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
}

.search-input::placeholder {
  color: #94a3b8;
  font-weight: normal;
}

.search-helper {
  font-size: 0.85rem;
  color: #64748b;
  padding-left: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-helper i {
  color: #8b5cf6;
}

/* ACCIONES */
.actions-section {
  grid-column: 2 / 3;
  display: flex;
  gap: 0.75rem;
  align-items: center;
  justify-content: flex-end;
}

.action-btn {
  padding: 12px 18px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.95rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  min-width: fit-content;
  white-space: nowrap;
  height: 48px;
}

.reset-btn {
  background: #f1f5f9;
  color: #475569;
  border: 2px solid transparent;
}

.reset-btn:hover {
  background: #e2e8f0;
  color: #1e293b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.refresh-btn {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border: 2px solid transparent;
  width: 48px;
  padding: 0;
  border-radius: 12px;
}

.refresh-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-2px) rotate(15deg);
  box-shadow: 0 6px 20px rgba(37, 99, 235, 0.3);
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.add-btn {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  color: white;
  border: 2px solid transparent;
  padding: 12px 20px;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
}

.add-btn:hover {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  transform: translateY(-2px);
  box-shadow: 0 6px 25px rgba(139, 92, 246, 0.3);
  border-color: rgba(255, 255, 255, 0.2);
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
  border-color: #8b5cf6;
}

.stat-card.active:hover {
  border-color: #10b981;
}

.stat-card.low:hover {
  border-color: #f59e0b;
}

.stat-card.avg:hover {
  border-color: #3b82f6;
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
  color: #8b5cf6;
}

.stat-card.active .stat-icon {
  background: #d1fae5;
  color: #065f46;
}

.stat-card.low .stat-icon {
  background: #fef3c7;
  color: #92400e;
}

.stat-card.avg .stat-icon {
  background: #dbeafe;
  color: #1d4ed8;
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
  color: #8b5cf6;
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
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
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

/* ===== LISTA DE CATEGORÍAS ===== */
.categories-container {
  padding: 2rem;
}

.categories-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f1f5f9;
}

.categories-header h3 {
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

/* ===== GRID DE CATEGORÍAS ===== */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .categories-grid {
    grid-template-columns: 1fr;
  }
}

/* ===== TARJETA DE CATEGORÍA ===== */
.category-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-card:hover {
  border-color: #c7d2fe;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  transform: translateY(-3px);
}

.category-card.popular {
  border-left: 4px solid #8b5cf6;
}

.category-card.empty {
  border-left: 4px solid #94a3b8;
  opacity: 0.8;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.category-title-section {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  flex: 1;
}

.category-icon {
  width: 48px;
  height: 48px;
  background: #f1f5f9;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #8b5cf6;
}

.category-title {
  flex: 1;
}

.category-name {
  margin: 0 0 0.3rem 0;
  font-size: 1.2rem;
  font-weight: 700;
  color: #1e293b;
}

.category-slug {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.9rem;
}

.category-slug i {
  color: #8b5cf6;
}

.category-stats {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  gap: 0.5rem;
}

.product-count {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #f1f5f9;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
}

.count-icon {
  color: #8b5cf6;
}

.count-number {
  font-weight: 700;
  color: #1e293b;
}

.count-label {
  color: #64748b;
  font-size: 0.85rem;
}

.category-status {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #10b981;
}

.status-dot.empty {
  background: #94a3b8;
}

.status-text {
  color: #64748b;
}

/* Descripción */
.category-description {
  color: #475569;
  font-size: 0.95rem;
  line-height: 1.5;
  padding: 0.8rem;
  background: #f8fafc;
  border-radius: 8px;
  border-left: 3px solid #8b5cf6;
}

.category-no-description {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #94a3b8;
  font-size: 0.9rem;
  padding: 0.5rem;
  background: #f8fafc;
  border-radius: 6px;
}

/* Productos */
.category-products {
  border-top: 2px solid #f1f5f9;
  padding-top: 1rem;
}

.products-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.toggle-products-btn {
  background: transparent;
  border: none;
  color: #8b5cf6;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.toggle-products-btn:hover {
  color: #7c3aed;
}

.products-preview {
  color: #64748b;
  font-size: 0.85rem;
  font-style: italic;
}

.products-list {
  background: #f8fafc;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
}

.products-list-header {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 0.8rem 1rem;
  background: #e2e8f0;
  font-weight: 600;
  font-size: 0.85rem;
  color: #475569;
}

.product-item {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr 1fr;
  gap: 1rem;
  padding: 0.8rem 1rem;
  border-bottom: 1px solid #e2e8f0;
  align-items: center;
  font-size: 0.9rem;
}

.product-item:last-child {
  border-bottom: none;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.2rem;
}

.product-name {
  font-weight: 500;
  color: #1e293b;
}

.product-sku {
  font-size: 0.8rem;
  color: #94a3b8;
}

.product-price {
  color: #059669;
  font-weight: 600;
}

.product-stock {
  font-weight: 600;
}

.product-stock.low {
  color: #dc2626;
}

.product-status {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  font-size: 0.85rem;
  font-weight: 500;
}

.product-status.active {
  color: #059669;
}

.product-status.inactive {
  color: #dc2626;
}

/* Acciones */
.category-actions {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #f1f5f9;
}

.action-buttons {
  display: flex;
  gap: 0.8rem;
}

.action-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  font-size: 0.9rem;
}

.edit-btn {
  background: #3b82f6;
  color: white;
}

.edit-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
}

.view-btn {
  background: #8b5cf6;
  color: white;
}

.view-btn:hover {
  background: #7c3aed;
  transform: translateY(-2px);
}

.delete-btn {
  background: #ef4444;
  color: white;
}

.delete-btn:hover:not(:disabled) {
  background: #dc2626;
  transform: translateY(-2px);
}

.delete-btn:disabled {
  background: #cbd5e1;
  color: #94a3b8;
  cursor: not-allowed;
  opacity: 0.6;
  transform: none !important;
}

.btn-text {
  display: inline;
}

@media (max-width: 576px) {
  .btn-text {
    display: none;
  }
  
  .action-btn {
    padding: 8px;
    width: 40px;
    justify-content: center;
  }
}

.category-usage {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
  font-size: 0.85rem;
  justify-content: center;
  padding: 0.5rem;
  background: #f1f5f9;
  border-radius: 6px;
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
  border-color: #8b5cf6;
  color: #8b5cf6;
  background: #f5f3ff;
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
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease;
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
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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
  transform: rotate(90deg);
}

.modal-body {
  padding: 2rem;
}

/* ===== FORMULARIO DE CATEGORÍA ===== */
.category-form {
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
  grid-template-columns: 1fr;
  gap: 1.5rem;
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
.form-textarea {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: white;
}

.form-input:focus,
.form-textarea:focus {
  outline: none;
  border-color: #8b5cf6;
  box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-help {
  color: #64748b;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: block;
}

/* Slug input */
.slug-input-group {
  display: flex;
  align-items: center;
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
}

.slug-prefix {
  padding: 12px 16px;
  background: #f1f5f9;
  color: #64748b;
  font-weight: 500;
  white-space: nowrap;
}

.slug-input {
  flex: 1;
  border: none;
  border-left: 1px solid #e2e8f0;
  border-radius: 0;
  padding-left: 12px;
}

.slug-input:focus {
  box-shadow: none;
}

/* Icon selection */
.icon-selection {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.icon-options {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(48px, 1fr));
  gap: 0.8rem;
}

.icon-option {
  width: 48px;
  height: 48px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
  color: #64748b;
  transition: all 0.3s ease;
}

.icon-option:hover {
  border-color: #8b5cf6;
  color: #8b5cf6;
  transform: translateY(-2px);
}

.icon-option.selected {
  background: #8b5cf6;
  border-color: #8b5cf6;
  color: white;
  transform: scale(1.1);
}

/* Form actions */
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
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
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
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.2);
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

.delete-warning {
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 8px;
  padding: 1.5rem;
  margin: 1.5rem 0;
  text-align: left;
}

.warning-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #92400e;
  font-weight: 600;
  margin-bottom: 1rem;
}

.warning-text {
  color: #92400e;
  font-size: 0.95rem;
  line-height: 1.5;
}

.confirm-details {
  background: #f8fafc;
  border-radius: 8px;
  padding: 1rem;
  margin: 1.5rem 0;
  text-align: left;
}

.confirm-details p {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #64748b;
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

.close-btn-secondary {
  background: #6b7280;
  color: white;
  border: none;
  padding: 12px 28px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.close-btn-secondary:hover {
  background: #4b5563;
  transform: translateY(-2px);
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
  
  .categories-container {
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
  .delete-btn,
  .close-btn-secondary {
    width: 100%;
    justify-content: center;
  }
  
  .empty-actions {
    flex-direction: column;
  }
  
  .products-list-header,
  .product-item {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }
}
</style>