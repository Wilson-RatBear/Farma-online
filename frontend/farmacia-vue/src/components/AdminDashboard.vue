<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="admin-dashboard" @click.stop>
      <div class="admin-header">
        <h1>📊 Panel Administrativo</h1>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>

      <div class="dashboard-content">
        <!-- Estadísticas -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-info">
              <h3>{{ stats.total_usuarios || 0 }}</h3>
              <p>Total Usuarios</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
              <h3>{{ stats.total_productos || 0 }}</h3>
              <p>Total Productos</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">🛒</div>
            <div class="stat-info">
              <h3>{{ stats.total_pedidos || 0 }}</h3>
              <p>Total Pedidos</p>
            </div>
          </div>

          <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-info">
              <h3>${{ (parseFloat(stats.ingresos_totales) || 0).toFixed(2) }}</h3>
              <p>Ingresos Totales</p>
            </div>
          </div>
        </div>

        <!-- Estados de Pedidos -->
        <div class="status-grid">
          <div class="status-card pending">
            <h4>⏳ Pendientes</h4>
            <p class="status-count">{{ stats.pedidos_pendientes || 0 }}</p>
          </div>

          <div class="status-card confirmed">
            <h4>✅ Confirmados</h4>
            <p class="status-count">{{ stats.pedidos_confirmados || 0 }}</p>
          </div>

          <div class="status-card shipped">
            <h4>🚚 Enviados</h4>
            <p class="status-count">{{ stats.pedidos_enviados || 0 }}</p>
          </div>

          <div class="status-card delivered">
            <h4>🎉 Entregados</h4>
            <p class="status-count">{{ stats.pedidos_entregados || 0 }}</p>
          </div>
        </div>

       <!-- Acciones Rápidas -->
<div class="actions-grid">
  <!-- Botón 1 - MODIFICADO -->
  <button class="action-btn" @click="$router.push('/admin/orders')">
    <div class="action-icon">📋</div>
    <span>Gestionar Pedidos</span>
  </button>
  
  <!-- Botón 2 - MODIFICADO -->
  <button class="action-btn" @click="$router.push('/admin/products')">
    <div class="action-icon">🛍️</div>
    <span>Gestionar Productos</span>
  </button>

  <!-- Botón 3 - MODIFICADO -->
  <button class="action-btn" @click="$router.push('/admin/categories')">
    <div class="action-icon">🏷️</div>
    <span>Gestionar Categorías</span>
  </button>
  
  <!-- Botón 4 - MODIFICADO -->
  <button class="action-btn" @click="$router.push('/admin/users')">
    <div class="action-icon">👥</div>
    <span>Gestionar Usuarios</span>
  </button>
  
  <!-- Botón 5 - MODIFICADO -->
  <button class="action-btn" @click="$router.push('/admin/reports')">
    <div class="action-icon">📈</div>
    <span>Reportes y Estadísticas</span>
  </button>
</div>
</div>


      <!-- Mensajes de estado -->
      <div v-if="loading" class="loading-message">
        <p>Cargando estadísticas...</p>
      </div>

      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/adminService'

export default {
  name: 'AdminDashboard',
  data() {
    return {
      stats: {},
      loading: false,
      error: ''
    }
  },
  async mounted() {
    await this.loadDashboardStats()
  },
  methods: {
    showOrdersManagement() {
      console.log('🎯 AdminDashboard: Emitiendo show-orders-management')
      this.$emit('show-orders-management')
      console.log('✅ Evento emitido')
    },
    
    showProductsManagement() {
      console.log('🎯 AdminDashboard: Emitiendo manage-products')
      this.$emit('manage-products')
      console.log('✅ Evento manage-products emitido')
    },
     showCategoriesManagement() {
    console.log('🔘 Botón categorías clickeado');
    this.$emit('manage-categories');
  },
    
    showUsersManagement() {
      console.log('🎯 AdminDashboard: Emitiendo manage-users')
      this.$emit('manage-users')
      console.log('✅ Evento manage-users emitido')
    },
    
    showReportsDashboard() {
      console.log('🎯 AdminDashboard: Emitiendo show-reports')
      this.$emit('show-reports')
      console.log('✅ Evento show-reports emitido')
    },
    
    closeModal() {
      this.$emit('close')
    },
    
    async loadDashboardStats() {
      this.loading = true
      this.error = ''
      
      try {
        console.log('📊 Cargando estadísticas administrativas...')
        const response = await adminService.getDashboardStats()
        console.log('✅ Estadísticas cargadas:', response)
        
        this.stats = response.stats || {}
      } catch (error) {
        console.error('❌ Error cargando estadísticas:', error)
        this.error = error.message || 'Error al cargar el dashboard administrativo'
        
        if (error.message?.includes('permisos') || error.response?.status === 403) {
          this.error = 'No tienes permisos de administrador para acceder a esta sección'
        }
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 10px;
}

.admin-dashboard {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  max-width: 900px;
  width: 95%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
  /* ✅ TÉCNICA DE CENTRADO ABSOLUTO */
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.admin-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.admin-header h1 {
  margin: 0;
  font-size: 1.3rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: white;
  padding: 5px;
}

.dashboard-content {
  flex: 1;
  overflow-y: auto; /* ✅ MANTENER auto */
  padding: 1rem;
  min-height: 500px;
}

/* ✅ ESTADÍSTICAS MÁS COMPACTAS */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: #f7fafc;
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.stat-icon {
  font-size: 1.5rem;
}

.stat-info h3 {
  margin: 0;
  font-size: 1.3rem;
  color: #2d3748;
}

.stat-info p {
  margin: 0.1rem 0 0 0;
  color: #718096;
  font-size: 0.8rem;
}

/* ✅ ESTADOS MÁS COMPACTOS */
.status-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

.status-card {
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  color: white;
  font-size: 0.9rem;
}

.status-card.pending { background: linear-gradient(135deg, #1c6fcf, #60a5fa); }
.status-card.confirmed { background: linear-gradient(135deg, #1062c5, #3b82f6); }
.status-card.shipped { background: linear-gradient(135deg, #3b82f6, #2563eb); }
.status-card.delivered { background: linear-gradient(135deg, #2563eb, #1d4ed8); }

.status-card h4 {
  margin: 0 0 0.3rem 0;
  font-size: 0.8rem;
}

.status-count {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
}

/* ✅ ACCIONES MÁS COMPACTAS */
.quick-actions {
  background: #f7fafc;
  padding: 1rem;
  border-radius: 8px;
}

.quick-actions h3 {
  margin: 0 0 0.8rem 0;
  color: #2d3748;
  font-size: 1.1rem;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.8rem;
}

.action-btn {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.8rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  min-height: 60px;
  justify-content: center;
  text-align: center;
  font-size: 0.9rem;
}

.action-btn:hover {
  border-color: #2c5aa0;
  transform: translateY(-1px);
}

.action-icon {
  font-size: 1.2rem;
}

.loading-message, .error-message {
  text-align: center;
  padding: 1rem;
  font-size: 0.9rem;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  border-radius: 6px;
  margin: 0.5rem;
}

/* ✅ RESPONSIVE */
@media (max-width: 768px) {
  .admin-dashboard {
    max-height: 90vh;
  }
  
  .stats-grid,
  .status-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .actions-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .admin-dashboard {
    max-height: 95vh;
  }
  
  .stats-grid,
  .status-grid,
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .admin-header {
    padding: 0.8rem 1rem;
  }
  
  .admin-header h1 {
    font-size: 1.1rem;
  }
  .actions-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 0.8rem;
  /* ✅ AGREGAR ESTO TEMPORALMENTE: */
  border: 2px solid red;
  background: yellow;
  min-height: 200px;
}

.action-btn {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  padding: 0.8rem;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
  min-height: 60px;
  justify-content: center;
  text-align: center;
  font-size: 0.9rem;
  /* ✅ AGREGAR ESTO TEMPORALMENTE: */
  border: 2px solid blue;
}
}
</style>