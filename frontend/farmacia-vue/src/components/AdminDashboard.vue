<template>
  <div class="admin-dashboard-page">
    <div class="page-header">
      <h1><i class="fas fa-tachometer-alt"></i> Panel Administrativo</h1>
      <div class="last-update">
        <small>Última actualización: {{ lastUpdate }}</small>
      </div>
    </div>

    <div class="dashboard-content">
      <!-- Estadísticas -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <h3>{{ stats.total_usuarios || 0 }}</h3>
            <p>Total Usuarios</p>
            <span class="trend positive" v-if="stats.usuarios_nuevos">+{{ stats.usuarios_nuevos }} este mes</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-capsules"></i></div>
          <div class="stat-info">
            <h3>{{ stats.total_productos || 0 }}</h3>
            <p>Total Productos</p>
            <span class="trend" :class="stats.stock_bajo ? 'negative' : 'positive'">
              {{ stats.stock_bajo || 0 }} con stock bajo
            </span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
          <div class="stat-info">
            <h3>{{ stats.total_pedidos || 0 }}</h3>
            <p>Total Pedidos</p>
            <span class="trend positive">+{{ stats.pedidos_hoy || 0 }} hoy</span>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
          <div class="stat-info">
            <h3>${{ (parseFloat(stats.ingresos_totales) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }}</h3>
            <p>Ingresos Totales</p>
            <span class="trend positive">${{ (parseFloat(stats.ingresos_mes) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2 }) }} este mes</span>
          </div>
        </div>
      </div>

      <!-- Sección de Estados de Pedidos y Acciones Rápidas -->
      <div class="dashboard-main-section">
        <!-- Estados de Pedidos -->
        <div class="status-section">
          <h3><i class="fas fa-clipboard-list"></i> Estados de Pedidos</h3>
          <div class="status-grid">
            <div class="status-card pending" @click="$router.push('/admin/orders?status=pending')">
              <div class="status-header">
                <i class="fas fa-clock"></i>
                <h4>Pendientes</h4>
              </div>
              <p class="status-count">{{ stats.pedidos_pendientes || 0 }}</p>
              <span class="status-subtitle">Requieren atención</span>
            </div>

            <div class="status-card confirmed" @click="$router.push('/admin/orders?status=confirmed')">
              <div class="status-header">
                <i class="fas fa-check-circle"></i>
                <h4>Confirmados</h4>
              </div>
              <p class="status-count">{{ stats.pedidos_confirmados || 0 }}</p>
              <span class="status-subtitle">Listos para procesar</span>
            </div>

            <div class="status-card shipped" @click="$router.push('/admin/orders?status=shipped')">
              <div class="status-header">
                <i class="fas fa-shipping-fast"></i>
                <h4>Enviados</h4>
              </div>
              <p class="status-count">{{ stats.pedidos_enviados || 0 }}</p>
              <span class="status-subtitle">En camino</span>
            </div>

            <div class="status-card delivered" @click="$router.push('/admin/orders?status=delivered')">
              <div class="status-header">
                <i class="fas fa-box-open"></i>
                <h4>Entregados</h4>
              </div>
              <p class="status-count">{{ stats.pedidos_entregados || 0 }}</p>
              <span class="status-subtitle">Completados</span>
            </div>
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="actions-section">
          <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
          <div class="actions-grid">
            <button class="action-btn" @click="$router.push('/admin/orders')">
              <div class="action-icon"><i class="fas fa-clipboard-list"></i></div>
              <div class="action-content">
                <span class="action-title">Gestionar Pedidos</span>
                <small>Ver y procesar todos los pedidos</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>
            
            <button class="action-btn" @click="$router.push('/admin/products')">
              <div class="action-icon"><i class="fas fa-capsules"></i></div>
              <div class="action-content">
                <span class="action-title">Gestionar Productos</span>
                <small>Agregar, editar o eliminar</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>

            <button class="action-btn" @click="$router.push('/admin/categories')">
              <div class="action-icon"><i class="fas fa-tags"></i></div>
              <div class="action-content">
                <span class="action-title">Gestionar Categorías</span>
                <small>Organizar productos</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>
            
            <button class="action-btn" @click="$router.push('/admin/users')">
              <div class="action-icon"><i class="fas fa-user-md"></i></div>
              <div class="action-content">
                <span class="action-title">Gestionar Usuarios</span>
                <small>Clientes y administradores</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>
            
            <button class="action-btn" @click="$router.push('/admin/reports')">
              <div class="action-icon"><i class="fas fa-chart-line"></i></div>
              <div class="action-content">
                <span class="action-title">Reportes Detallados</span>
                <small>Análisis y estadísticas</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>

            <button class="action-btn highlight" @click="loadDashboardStats">
              <div class="action-icon"><i class="fas fa-sync-alt"></i></div>
              <div class="action-content">
                <span class="action-title">Actualizar Datos</span>
                <small>Cargar información más reciente</small>
              </div>
              <i class="fas fa-chevron-right action-arrow"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Últimos Pedidos (si existen) -->
      <div v-if="stats.ultimos_pedidos && stats.ultimos_pedidos.length > 0" class="recent-orders">
        <h3><i class="fas fa-history"></i> Pedidos Recientes</h3>
        <div class="orders-table">
          <div v-for="pedido in stats.ultimos_pedidos" :key="pedido.id" class="order-item">
            <div class="order-info">
              <span class="order-id">#{{ pedido.id }}</span>
              <span class="order-customer">{{ pedido.cliente }}</span>
              <span class="order-total">${{ pedido.total.toFixed(2) }}</span>
            </div>
            <div class="order-status" :class="pedido.estado">
              {{ pedido.estado }}
            </div>
            <span class="order-time">{{ pedido.fecha }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Mensajes de estado -->
    <div v-if="loading" class="loading-overlay">
      <div class="loading-spinner">
        <i class="fas fa-spinner fa-spin"></i>
        <p>Cargando estadísticas...</p>
      </div>
    </div>

    <div v-if="error" class="error-message">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="loadDashboardStats" class="retry-btn">Reintentar</button>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/AdminService'

export default {
  name: 'DashboardAdministrativo',
  data() {
    return {
      stats: {},
      loading: false,
      error: '',
      lastUpdate: ''
    }
  },
  async mounted() {
    await this.loadDashboardStats()
  },
  methods: {
    async loadDashboardStats() {
      this.loading = true
      this.error = ''
      
      try {
        console.log('📊 Cargando estadísticas administrativas...')
        const response = await adminService.getDashboardStats()
        console.log('✅ Estadísticas cargadas:', response)
        
        this.stats = response.stats || {}
        this.lastUpdate = new Date().toLocaleTimeString('es-MX', {
          hour: '2-digit',
          minute: '2-digit'
        })
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
/* ===== CONTENEDOR PRINCIPAL ===== */
.admin-dashboard-page {
  padding: 30px;
  min-height: calc(100vh - 60px);
  background: #f8fafc;
  width: 100%;
  max-width: 100%;
  overflow-x: hidden;
}

/* ===== ENCABEZADO ===== */
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
  background: white;
  padding: 20px 25px;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.page-header h1 {
  color: #1e3a8a;
  margin: 0;
  font-size: 1.8rem;
  display: flex;
  align-items: center;
  gap: 15px;
  font-weight: 600;
}

.last-update {
  color: #64748b;
  font-size: 0.9rem;
  background: #f1f5f9;
  padding: 8px 15px;
  border-radius: 6px;
}

.last-update i {
  margin-right: 5px;
  color: #3b82f6;
}

/* ===== GRID DE ESTADÍSTICAS ===== */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 24px;
  margin-bottom: 30px;
  width: 100%;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  display: flex;
  align-items: center;
  gap: 20px;
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
  width: 100%;
}

.stat-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-color: #c7d2fe;
}

.stat-icon {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.stat-info {
  flex: 1;
  min-width: 0;
}

.stat-info h3 {
  margin: 0 0 8px 0;
  font-size: 2.2rem;
  color: #1e293b;
  font-weight: 700;
  line-height: 1;
}

.stat-info p {
  margin: 0 0 10px 0;
  color: #64748b;
  font-weight: 500;
  font-size: 0.95rem;
}

.trend {
  font-size: 0.85rem;
  padding: 4px 10px;
  border-radius: 20px;
  display: inline-block;
  font-weight: 500;
}

.trend.positive {
  background: #dcfce7;
  color: #166534;
}

.trend.negative {
  background: #fee2e2;
  color: #991b1b;
}

/* ===== SECCIÓN PRINCIPAL ===== */
.dashboard-main-section {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 30px;
  margin-bottom: 40px;
  width: 100%;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.section-header h3 {
  color: #1e3a8a;
  margin: 0;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  gap: 12px;
  font-weight: 600;
}

.view-all-btn,
.refresh-btn {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.view-all-btn:hover,
.refresh-btn:hover {
  background: #dbeafe;
  transform: translateY(-1px);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.refresh-btn i {
  transition: transform 0.3s ease;
}

/* ===== ESTADOS DE PEDIDOS ===== */
.status-section {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
}

.status-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-top: 10px;
}

.status-card {
  padding: 20px;
  border-radius: 10px;
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-align: left;
  width: 100%;
}

.status-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.status-header {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 15px;
}

.status-card h4 {
  margin: 0;
  font-size: 1rem;
  font-weight: 600;
}

.status-count {
  margin: 0 0 5px 0;
  font-size: 2.4rem;
  font-weight: 800;
  line-height: 1;
}

.status-subtitle {
  font-size: 0.85rem;
  opacity: 0.95;
  font-weight: 500;
}

/* Colores de estados mejorados */
.pending {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  box-shadow: 0 4px 15px rgba(245, 158, 11, 0.2);
}

.confirmed {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 4px 15px rgba(16, 185, 129, 0.2);
}

.shipped {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}

.delivered {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.2);
}

/* ===== ACCIONES RÁPIDAS ===== */
.actions-section {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
}

.actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 18px;
  margin-top: 10px;
}

.action-btn {
  background: white;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  padding: 22px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 18px;
  text-align: left;
  transition: all 0.3s ease;
  width: 100%;
}

.action-btn:hover {
  border-color: #3b82f6;
  background: #f8fafc;
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.1);
}

.action-btn.highlight {
  border-color: #10b981;
  background: #f0fdf4;
}

.action-btn.highlight:hover {
  border-color: #059669;
  background: #dcfce7;
}

.action-icon {
  width: 56px;
  height: 56px;
  background: #f1f5f9;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  color: #3b82f6;
  flex-shrink: 0;
}

.action-btn.highlight .action-icon {
  background: #d1fae5;
  color: #10b981;
}

.action-content {
  flex: 1;
  min-width: 0;
}

.action-title {
  display: block;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 5px;
  font-size: 1.05rem;
}

.action-btn small {
  color: #64748b;
  font-size: 0.85rem;
  line-height: 1.4;
}

.action-arrow {
  color: #94a3b8;
  font-size: 0.9rem;
  transition: transform 0.3s ease;
}

.action-btn:hover .action-arrow {
  transform: translateX(4px);
  color: #3b82f6;
}

/* ===== PEDIDOS RECIENTES ===== */
.recent-section {
  background: white;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  border: 1px solid #e2e8f0;
  width: 100%;
  overflow: hidden;
}

.orders-table {
  overflow-x: auto;
}

.orders-table table {
  width: 100%;
  border-collapse: collapse;
  min-width: 1000px;
}

.orders-table th {
  background: #f8fafc;
  color: #475569;
  font-weight: 600;
  font-size: 0.9rem;
  text-align: left;
  padding: 16px 20px;
  border-bottom: 2px solid #e2e8f0;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.orders-table td {
  padding: 18px 20px;
  border-bottom: 1px solid #e2e8f0;
  color: #334155;
  font-size: 0.95rem;
}

.orders-table tbody tr:hover {
  background: #f8fafc;
}

.order-id {
  font-weight: 600;
  color: #1e293b;
}

.order-customer {
  font-weight: 500;
}

.order-products {
  color: #64748b;
}

.order-total {
  font-weight: 700;
  color: #1e3a8a;
}

.status-badge {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  display: inline-block;
}

.status-badge.pendiente { background: #fef3c7; color: #92400e; }
.status-badge.confirmado { background: #d1fae5; color: #065f46; }
.status-badge.enviado { background: #dbeafe; color: #1e40af; }
.status-badge.entregado { background: #ede9fe; color: #5b21b6; }

.action-btn-sm {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
  padding: 6px 14px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: all 0.3s ease;
}

.action-btn-sm:hover {
  background: #dbeafe;
  transform: translateY(-1px);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.empty-state i {
  font-size: 3rem;
  color: #cbd5e1;
  margin-bottom: 15px;
}

.empty-state p {
  margin: 0;
  font-size: 1.1rem;
}

/* ===== MENSAJES DE ESTADO ===== */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0; /* Ancho del sidebar */
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.95);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  backdrop-filter: blur(2px);
}

.loading-content {
  text-align: center;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 4px solid #e2e8f0;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-content p {
  margin: 0;
  color: #3b82f6;
  font-weight: 500;
  font-size: 1.1rem;
}

.error-message {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  padding: 25px;
  margin: 20px 0;
  display: flex;
  align-items: center;
  gap: 20px;
  color: #dc2626;
  width: 100%;
}

.error-message i {
  font-size: 1.8rem;
  color: #ef4444;
  flex-shrink: 0;
}

.error-content {
  flex: 1;
}

.error-content p {
  margin: 0 0 15px 0;
}

.error-content strong {
  color: #991b1b;
}

.retry-btn {
  background: #ef4444;
  color: white;
  border: none;
  padding: 10px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.retry-btn:hover {
  background: #dc2626;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1400px) {
  .dashboard-main-section {
    grid-template-columns: 1fr;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 1200px) {
  .admin-dashboard-page {
    padding: 25px;
  }
}

@media (max-width: 992px) {
  .admin-dashboard-page {
    padding: 20px;
  }
  
  .page-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 15px;
  }
  
  .last-update {
    align-self: flex-end;
  }
}

@media (max-width: 768px) {
  .admin-dashboard-page {
    padding: 16px;
  }
  
  .stats-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }
  
  .status-grid {
    grid-template-columns: 1fr;
  }
  
  .actions-grid {
    grid-template-columns: 1fr;
  }
  
  .stat-card,
  .status-section,
  .actions-section,
  .recent-section {
    padding: 20px;
  }
  
  .page-header h1 {
    font-size: 1.6rem;
  }
  
  .loading-overlay {
    left: 0;
  }
}

@media (max-width: 576px) {
  .admin-dashboard-page {
    padding: 12px;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
  
  .view-all-btn,
  .refresh-btn {
    align-self: flex-start;
  }
  
  .stat-card {
    flex-direction: column;
    text-align: center;
    gap: 15px;
  }
  
  .stat-info {
    text-align: center;
  }
}
</style>