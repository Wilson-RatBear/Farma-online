<template>
  <div class="modal-overlay" @click="$router.push('/admin')">
    <div class="reports-dashboard" @click.stop>
      <!-- Header unificado con botones dentro -->
      <div class="admin-header">
        <button class="btn-back" @click="$router.push('/admin')">
          <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="admin-title">
          <i class="fas fa-chart-bar"></i> Reportes y Estadísticas
        </h2>
        <button class="btn-close" @click="$router.push('/')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido principal -->
      <div class="reports-content">
        <!-- Estado de carga -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando métricas...</p>
        </div>

        <!-- Estado de error -->
        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <p>Error al cargar reportes: {{ error }}</p>
          <button @click="loadMetrics" class="btn-retry">Reintentar</button>
        </div>

        <!-- Métricas principales -->
        <div v-else class="metrics-grid">
          <!-- Tarjeta de Ingresos -->
          <div class="metric-card income">
            <div class="metric-icon">
              <i class="fas fa-dollar-sign"></i>
            </div>
            <div class="metric-info">
              <h3>${{ formatCurrency(metrics.ingresos_totales) }}</h3>
              <p>Ingresos Totales</p>
              <div class="metric-sub">
                <span class="sub-value">${{ formatCurrency(metrics.ingresos_mensuales) }} este mes</span>
              </div>
            </div>
          </div>

          <!-- Tarjeta de Pedidos -->
          <div class="metric-card orders">
            <div class="metric-icon">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="metric-info">
              <h3>{{ metrics.pedidos_totales }}</h3>
              <p>Total Pedidos</p>
              <div class="metric-sub">
                <span class="sub-value">{{ metrics.pedidos_mensuales }} este mes</span>
              </div>
            </div>
          </div>

          <!-- Tarjeta de Usuarios -->
          <div class="metric-card users">
            <div class="metric-icon">
              <i class="fas fa-users"></i>
            </div>
            <div class="metric-info">
              <h3>{{ metrics.usuarios_totales }}</h3>
              <p>Usuarios Registrados</p>
            </div>
          </div>

          <!-- Tarjeta de Productos -->
          <div class="metric-card products">
            <div class="metric-icon">
              <i class="fas fa-cubes"></i>
            </div>
            <div class="metric-info">
              <h3>{{ metrics.productos_totales }}</h3>
              <p>Productos Activos</p>
              <div class="metric-sub" v-if="metrics.productos_stock_bajo > 0">
                <span class="stock-warning">{{ metrics.productos_stock_bajo }} con stock bajo</span>
              </div>
            </div>
          </div>

          <!-- Métricas del día -->
          <div class="daily-metrics">
            <h4><i class="fas fa-calendar-day"></i> Hoy</h4>
            <div class="daily-grid">
              <div class="daily-item">
                <span class="daily-value">{{ metrics.pedidos_hoy }}</span>
                <span class="daily-label">Pedidos</span>
              </div>
              <div class="daily-item">
                <span class="daily-value">${{ formatCurrency(metrics.ingresos_hoy) }}</span>
                <span class="daily-label">Ingresos</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import reportService from '../services/reportService';

export default {
  name: 'ReportsDashboard',
  data() {
    return {
      loading: true,
      error: null,
      metrics: {
        ingresos_totales: 0,
        pedidos_totales: 0,
        usuarios_totales: 0,
        productos_totales: 0,
        ingresos_mensuales: 0,
        pedidos_mensuales: 0,
        pedidos_hoy: 0,
        ingresos_hoy: 0,
        productos_stock_bajo: 0
      }
    };
  },
  mounted() {
    this.loadMetrics();
  },
  methods: {
    async loadMetrics() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await reportService.getGeneralMetrics();
        if (response.success) {
          this.metrics = response.data;
        } else {
          throw new Error(response.message || 'Error al cargar métricas');
        }
      } catch (error) {
        console.error('Error loading metrics:', error);
        this.error = error.message || 'Error de conexión';
      } finally {
        this.loading = false;
      }
    },
    
    formatCurrency(value) {
      return parseFloat(value || 0).toFixed(2);
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
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.reports-dashboard {
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: relative;  
  z-index: 1001;       
}

.reports-dashboard {
  width: 90%;
  max-width: 1000px;
  max-height: 90vh;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.admin-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-shrink: 0;
}

.admin-title {
  margin: 0;
  font-size: 1.4rem;
  font-weight: 600;
}

.admin-title i {
  margin-right: 10px;
}

.btn-back, .btn-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255,255,255,0.3);
}

.reports-content {
  flex: 1;
  padding: 30px;
  overflow-y: auto;
}

.loading-state, .error-state {
  text-align: center;
  padding: 60px 20px;
  color: #666;
}

.loading-state i {
  font-size: 2rem;
  margin-bottom: 15px;
}

.error-state i {
  font-size: 2rem;
  color: #e74c3c;
  margin-bottom: 15px;
}

.btn-retry {
  background: #3498db;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  margin-top: 15px;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.metric-card {
  background: white;
  border-radius: 10px;
  padding: 25px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  border-left: 4px solid #3498db;
  display: flex;
  align-items: center;
  transition: transform 0.3s;
}

.metric-card:hover {
  transform: translateY(-5px);
}

.metric-card.income { border-left-color: #27ae60; }
.metric-card.orders { border-left-color: #e74c3c; }
.metric-card.users { border-left-color: #9b59b6; }
.metric-card.products { border-left-color: #f39c12; }

.metric-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 20px;
  color: white;
  font-size: 1.5rem;
}

.metric-card.income .metric-icon { background: linear-gradient(135deg, #27ae60, #2ecc71); }
.metric-card.orders .metric-icon { background: linear-gradient(135deg, #e74c3c, #c0392b); }
.metric-card.users .metric-icon { background: linear-gradient(135deg, #9b59b6, #8e44ad); }
.metric-card.products .metric-icon { background: linear-gradient(135deg, #f39c12, #e67e22); }

.metric-info h3 {
  margin: 0 0 5px 0;
  font-size: 1.8rem;
  font-weight: 700;
  color: #2c3e50;
}

.metric-info p {
  margin: 0 0 8px 0;
  color: #7f8c8d;
  font-weight: 500;
}

.metric-sub {
  font-size: 0.85rem;
}

.sub-value {
  color: #27ae60;
  font-weight: 600;
}

.stock-warning {
  color: #e74c3c;
  font-weight: 600;
}

.daily-metrics {
  grid-column: 1 / -1;
  background: #f8f9fa;
  border-radius: 10px;
  padding: 20px;
  margin-top: 10px;
}

.daily-metrics h4 {
  margin: 0 0 15px 0;
  color: #2c3e50;
  font-size: 1.1rem;
}

.daily-metrics h4 i {
  margin-right: 8px;
  color: #3498db;
}

.daily-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 15px;
}

.daily-item {
  text-align: center;
  padding: 15px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.daily-value {
  display: block;
  font-size: 1.5rem;
  font-weight: 700;
  color: #2c3e50;
  margin-bottom: 5px;
}

.daily-label {
  color: #7f8c8d;
  font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
  .reports-dashboard {
    width: 95%;
    height: 95vh;
  }
  
  .admin-header {
    padding: 15px;
  }
  
  .admin-title {
    font-size: 1.1rem;
  }
  
  .reports-content {
    padding: 20px;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
  
  .metric-card {
    padding: 20px;
  }
}
</style>