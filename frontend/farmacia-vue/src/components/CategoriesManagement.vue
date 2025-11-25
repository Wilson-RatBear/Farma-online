<template>
  <div class="modal-overlay" @click="$router.push('/admin')">
    <div class="modal-container" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <button class="btn-back" @click="$router.push('/admin')">
          <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="modal-title">
          <i class="fas fa-tags"></i> Gestión de Categorías
        </h2>
        <button class="btn-close" @click="$router.push('/')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="modal-body">
        <!-- Barra de acciones -->
        <div class="action-bar">
          <div class="search-box">
            <i class="fas fa-search"></i>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Buscar categorías..."
              class="search-input"
            >
          </div>
          <button class="btn-new" @click="showCreateModal = true">
            <i class="fas fa-plus"></i> Nueva Categoría
          </button>
        </div>

        <!-- Estados -->
        <div v-if="loading" class="state-loading">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando categorías...</p>
        </div>

        <div v-else-if="error" class="state-error">
          <i class="fas fa-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <button @click="loadCategories" class="btn-retry">Reintentar</button>
        </div>

        <!-- Lista de categorías -->
        <div v-else class="categories-grid">
          <div 
            v-for="category in filteredCategories" 
            :key="category.id"
            class="category-item"
          >
            <div class="category-content">
              <div class="category-header">
                <h3 class="category-name">{{ category.nombre }}</h3>
                <div class="category-badge">
                  {{ category.total_productos || 0 }} productos
                </div>
              </div>
              
              <div class="category-slug">
                <i class="fas fa-link"></i>
                {{ category.slug }}
              </div>

              <div v-if="category.descripcion" class="category-description">
                {{ category.descripcion }}
              </div>

              <!-- Botón para ver productos -->
              <div v-if="category.total_productos > 0" class="products-section">
                <button 
                  class="btn-view-products"
                  @click="toggleProducts(category.id)"
                >
                  <i class="fas" :class="expandedCategories.includes(category.id) ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                  Ver productos ({{ category.total_productos }})
                </button>
                
                <div v-if="expandedCategories.includes(category.id)" class="products-list">
                  <div 
                    v-for="product in category.productos" 
                    :key="product.id"
                    class="product-item"
                  >
                    <span class="product-name">{{ product.nombre }}</span>
                    <span class="product-price">${{ product.precio }}</span>
                    <span class="product-status" :class="product.activo ? 'active' : 'inactive'">
                      {{ product.activo ? 'Activo' : 'Inactivo' }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <div class="category-actions">
              <button 
                class="btn-action btn-edit"
                @click="editCategory(category)"
                title="Editar categoría"
              >
                <i class="fas fa-edit"></i>
              </button>

              <button 
                v-if="category.total_productos > 0"
                class="btn-action btn-view"
                @click="toggleProducts(category.id)"
                :title="expandedCategories.includes(category.id) ? 'Ocultar productos' : 'Ver productos'"
              >
                <i class="fas" :class="expandedCategories.includes(category.id) ? 'fa-eye-slash' : 'fa-eye'"></i>
              </button>

              <button 
                class="btn-action btn-delete"
                @click="confirmDelete(category)"
                :disabled="category.total_productos > 0"
                :title="category.total_productos > 0 ? 'No se puede eliminar: tiene productos' : 'Eliminar categoría'"
              >
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>

          <!-- Estado vacío -->
          <div v-if="!loading && filteredCategories.length === 0" class="state-empty">
            <i class="fas fa-tags"></i>
            <p>No se encontraron categorías</p>
            <button @click="searchQuery = ''" class="btn-retry">Limpiar búsqueda</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal para crear/editar categoría -->
    <div v-if="showCreateModal || editingCategory" class="modal-overlay inner" @click="closeModal">
      <div class="modal-container" @click.stop style="max-width: 450px;">
        <div class="modal-header">
          <h2 class="modal-title">
            <i class="fas" :class="editingCategory ? 'fa-edit' : 'fa-plus'"></i>
            {{ editingCategory ? 'Editar Categoría' : 'Nueva Categoría' }}
          </h2>
          <button class="btn-close" @click="closeModal">
            <i class="fas fa-times"></i>
          </button>
        </div>

        <div class="modal-body">
          <form @submit.prevent="saveCategory" class="category-form">
            <div class="form-group">
              <label>Nombre *</label>
              <input
                v-model="categoryForm.nombre"
                type="text"
                required
                placeholder="Ej: Medicamentos, Vitaminas..."
              >
            </div>

            <div class="form-group">
              <label>Descripción</label>
              <textarea
                v-model="categoryForm.descripcion"
                placeholder="Descripción opcional..."
                rows="3"
              ></textarea>
            </div>

            <div class="form-actions">
              <button type="button" class="btn-cancel" @click="closeModal">
                Cancelar
              </button>
              <button 
                type="submit" 
                class="btn-save"
                :disabled="!categoryForm.nombre || saving"
              >
                <i class="fas fa-spinner fa-spin" v-if="saving"></i>
                {{ editingCategory ? 'Actualizar' : 'Crear' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/adminService';
import { useRouter } from 'vue-router'

export default {
  name: 'CategoriesManagement',
  setup() {
    const router = useRouter()
    return { router }
  },
  data() {
    return {
      categories: [],
      loading: false,
      error: null,
      searchQuery: '',
      expandedCategories: [],
      showCreateModal: false,
      editingCategory: null,
      saving: false,
      categoryForm: {
        nombre: '',
        descripcion: '',
        slug: ''
      }
    };
  },
  computed: {
    filteredCategories() {
      if (!this.searchQuery) return this.categories;
      
      const query = this.searchQuery.toLowerCase();
      return this.categories.filter(category => 
        category.nombre.toLowerCase().includes(query) ||
        (category.descripcion && category.descripcion.toLowerCase().includes(query)) ||
        category.slug.toLowerCase().includes(query)
      );
    }
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
        if (response.success) {
          this.categories = response.categorias;
        } else {
          this.error = response.message || 'Error al cargar las categorías';
        }
      } catch (error) {
        this.error = error.message || 'Error de conexión al cargar categorías';
        console.error('Error loading categories:', error);
      } finally {
        this.loading = false;
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

    editCategory(category) {
      this.editingCategory = category;
      this.categoryForm = {
        nombre: category.nombre,
        descripcion: category.descripcion || '',
        slug: category.slug || ''
      };
    },

    async saveCategory() {
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
        } else {
          this.error = response.message || 'Error al guardar la categoría';
        }
      } catch (error) {
        this.error = error.message || 'Error de conexión al guardar categoría';
        console.error('Error saving category:', error);
      } finally {
        this.saving = false;
      }
    },

    async confirmDelete(category) {
      if (category.total_productos > 0) {
        alert(`No se puede eliminar la categoría "${category.nombre}" porque tiene ${category.total_productos} productos asociados. Reasigna los productos primero.`);
        return;
      }

      if (confirm(`¿Estás seguro de que quieres eliminar la categoría "${category.nombre}"? Esta acción no se puede deshacer.`)) {
        try {
          const response = await adminService.deleteCategory(category.id);
          
          if (response.success) {
            await this.loadCategories();
          } else {
            alert(response.message || 'Error al eliminar la categoría');
          }
        } catch (error) {
          alert(error.message || 'Error de conexión al eliminar categoría');
          console.error('Error deleting category:', error);
        }
      }
    },

    closeModal() {
      this.showCreateModal = false;
      this.editingCategory = null;
      this.categoryForm = {
        nombre: '',
        descripcion: '',
        slug: ''
      };
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
}

.modal-container {
  background: white;
  border-radius: 12px;
  width: 550px;
  max-height: 700px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  box-shadow: 0 15px 35px rgba(0,0,0,0.3);
  animation: modalAppear 0.3s ease-out;
}

@keyframes modalAppear {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-10px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.modal-header {
  background: linear-gradient(135deg, #1e88e5 0%, #0d47a1 100%);
  color: white;
  padding: 18px 24px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.btn-back, .btn-close {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-1px);
}

.modal-title {
  margin: 0;
  font-size: 1.3rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 10px;
  color: white; /* Asegura que sea blanco */
  text-shadow: 0 2px 4px rgba(0,0,0,0.3); /* Sombra más pronunciada */
  letter-spacing: 0.5px; /* Un poco más de espacio entre letras */
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 24px;
  background: #f8fafc;
}

.action-bar {
  display: flex;
  gap: 12px;
  margin-bottom: 20px;
  align-items: center;
}

.search-box {
  position: relative;
  flex: 1;
}

.search-box i {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
  font-size: 14px;
}

.search-input {
  width: 100%;
  padding: 10px 12px 10px 36px;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  font-size: 14px;
  background: white;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.btn-new {
  background: linear-gradient(135deg, #27ae60, #219a52);
  color: white;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  font-weight: 600;
  white-space: nowrap;
  transition: all 0.2s ease;
}

.btn-new:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
}

.categories-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.category-item {
  background: white;
  border-radius: 10px;
  padding: 18px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  border-left: 4px solid #1e88e5;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 16px;
  transition: all 0.2s ease;
}

.category-item:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0,0,0,0.12);
}

.category-content {
  flex: 1;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 8px;
}

.category-name {
  margin: 0;
  color: #1e293b;
  font-size: 16px;
  font-weight: 700;
  line-height: 1.3;
}

.category-badge {
  background: #dbeafe;
  color: #1d4ed8;
  padding: 4px 10px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 600;
  white-space: nowrap;
}

.category-slug {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13px;
  color: #64748b;
  margin-bottom: 10px;
}

.category-slug i {
  color: #1e88e5;
}

.category-description {
  color: #475569;
  font-size: 14px;
  line-height: 1.4;
  font-style: italic;
  margin-bottom: 12px;
}

.products-section {
  margin-top: 12px;
}

.btn-view-products {
  background: #f1f5f9;
  border: 1px solid #e2e8f0;
  color: #475569;
  padding: 8px 12px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 13px;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
  width: 100%;
  justify-content: center;
}

.btn-view-products:hover {
  background: #e2e8f0;
  border-color: #cbd5e1;
}

.products-list {
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  gap: 8px;
  max-height: 200px;
  overflow-y: auto;
  padding: 8px;
  background: #f8fafc;
  border-radius: 6px;
  border: 1px solid #e2e8f0;
}

.product-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 10px;
  background: white;
  border-radius: 4px;
  border: 1px solid #f1f5f9;
  font-size: 12px;
}

.product-name {
  font-weight: 600;
  color: #374151;
  flex: 1;
}

.product-price {
  color: #27ae60;
  font-weight: 600;
  margin: 0 10px;
  font-size: 11px;
}

.product-status {
  padding: 3px 6px;
  border-radius: 3px;
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
}

.product-status.active {
  background: #d1fae5;
  color: #065f46;
}

.product-status.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.category-actions {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.btn-action {
  padding: 8px 10px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  color: white;
  font-size: 12px;
  transition: all 0.2s ease;
  min-width: 36px;
}

.btn-action:hover {
  transform: scale(1.1);
}

.btn-edit { 
  background: #3498db; 
}

.btn-view { 
  background: #9b59b6; 
}

.btn-delete { 
  background: #e74c3c; 
}

.btn-delete:disabled { 
  background: #cbd5e1; 
  color: #94a3b8;
  cursor: not-allowed;
  transform: none !important;
}

.state-loading, .state-error, .state-empty {
  text-align: center;
  padding: 50px 20px;
  color: #64748b;
}

.state-loading i, .state-error i, .state-empty i {
  font-size: 2.5rem;
  margin-bottom: 16px;
  opacity: 0.7;
}

.state-error i {
  color: #e74c3c;
}

.state-empty i {
  color: #94a3b8;
}

.btn-retry {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 12px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.btn-retry:hover {
  background: #2980b9;
}

.category-form {
  display: flex;
  flex-direction: column;
  gap: 18px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.form-group label {
  font-weight: 600;
  color: #374151;
  font-size: 14px;
}

.form-group input,
.form-group textarea {
  padding: 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 10px;
}

.btn-cancel {
  background: #6b7280;
  color: white;
  border: none;
  padding: 10px 18px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s ease;
}

.btn-cancel:hover {
  background: #4b5563;
}

.btn-save {
  background: linear-gradient(135deg, #1e88e5, #1565c0);
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.2s ease;
}

.btn-save:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
}

.btn-save:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

@media (max-width: 640px) {
  .modal-container {
    width: 95%;
    max-height: 85vh;
  }
  
  .action-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .category-item {
    flex-direction: column;
    gap: 12px;
  }
  
  .category-actions {
    flex-direction: row;
    align-self: flex-end;
  }
  
  .form-actions {
    flex-direction: column;
  }
}
</style>