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
        <button class="btn-close" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Selector de tipo de reporte -->
      <div class="reports-type-selector">
        <button 
          :class="['report-type-btn', { active: activeReportType === 'basicos' }]"
          @click="activeReportType = 'basicos'"
        >
          <i class="fas fa-chart-bar"></i>
          Reportes Básicos
        </button>
        <button 
          :class="['report-type-btn', { active: activeReportType === 'avanzados' }]"
          @click="activeReportType = 'avanzados'"
        >
          <i class="fas fa-chart-line"></i>
          Reportes Avanzados
        </button>
      </div>

      <!-- Contenido principal -->
      <div class="reports-content">
        <!-- REPORTES BÁSICOS (TU CÓDIGO ORIGINAL) -->
        <div v-if="activeReportType === 'basicos'" class="basic-reports-section">
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

        <!-- REPORTES AVANZADOS (NUEVA SECCIÓN) -->
        <div v-if="activeReportType === 'avanzados'" class="advanced-reports-section">
          <div class="advanced-reports-content">
            <!-- Selector de reportes avanzados -->
            <div class="advanced-report-selector">
              <button 
                v-for="report in reportTypes" 
                :key="report.id"
                :class="['advanced-report-btn', { active: activeReport === report.id }]"
                @click="selectReport(report.id)"
              >
                <i :class="report.icon"></i>
                {{ report.name }}
              </button>
            </div>

            <!-- Panel de reporte avanzado activo -->
            <div class="advanced-report-panel">
              <!-- Ventas por Período -->
              <div v-if="activeReport === 'ventas'" class="advanced-report-section">
                <div class="advanced-section-header">
                  <h3><i class="fas fa-chart-bar"></i> Ventas por Período</h3>
                  <select v-model="ventasRango" @change="loadVentasPorPeriodo">
                    <option value="diario">Últimos 30 días</option>
                    <option value="semanal">Últimas 12 semanas</option>
                    <option value="mensual">Últimos 12 meses</option>
                  </select>
                </div>
                
                <div v-if="loadingVentas" class="loading">Cargando datos...</div>
                <div v-else-if="ventasData.length" class="chart-container">
                  <div class="data-grid">
                    <div v-for="item in ventasData" :key="item.fecha || item.mes" class="data-item">
                      <div class="period-label">
                        {{ formatPeriodLabel(item) }}
                      </div>
                      <div class="period-stats">
                        <span class="stat">Pedidos: {{ item.total_pedidos }}</span>
                        <span class="stat amount">${{ formatCurrency(item.ingresos_totales) }}</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-data">No hay datos disponibles</div>
              </div>

              <!-- Productos Más Vendidos -->
              <div v-if="activeReport === 'productos'" class="advanced-report-section">
                <div class="advanced-section-header">
                  <h3><i class="fas fa-star"></i> Productos Más Vendidos</h3>
                  <div class="advanced-filters">
                    <select v-model="productosPeriodo" @change="loadProductosMasVendidos">
                      <option value="todo">Todo el tiempo</option>
                      <option value="año">Último año</option>
                      <option value="mes">Último mes</option>
                      <option value="semana">Última semana</option>
                    </select>
                    <select v-model="productosLimite" @change="loadProductosMasVendidos">
                      <option value="5">Top 5</option>
                      <option value="10">Top 10</option>
                      <option value="20">Top 20</option>
                    </select>
                  </div>
                </div>
                
                <div v-if="loadingProductos" class="loading">Cargando productos...</div>
                <div v-else-if="productosData.length" class="products-grid">
                  <div v-for="(producto, index) in productosData" :key="producto.id" class="product-item">
                    <div class="product-rank">#{{ index + 1 }}</div>
                    <div class="product-info">
                      <div class="product-name">{{ producto.nombre }}</div>
                      <div class="product-stats">
                        <span class="sales">Vendidos: {{ producto.total_vendido }}</span>
                        <span class="revenue">Ingresos: ${{ formatCurrency(producto.ingresos_generados) }}</span>
                      </div>
                    </div>
                    <div class="product-price">${{ formatCurrency(producto.precio) }}</div>
                  </div>
                </div>
                <div v-else class="no-data">No hay datos de productos</div>
              </div>

              <!-- Métricas de Usuarios -->
              <div v-if="activeReport === 'usuarios'" class="advanced-report-section">
                <div class="advanced-section-header">
                  <h3><i class="fas fa-users"></i> Métricas de Usuarios</h3>
                </div>
                
                <div v-if="loadingUsuarios" class="loading">Cargando métricas...</div>
                <div v-else-if="usuariosData" class="advanced-metrics-grid">
                  <div class="advanced-metric-card">
                    <div class="advanced-metric-icon">
                      <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="advanced-metric-info">
                      <div class="advanced-metric-value">{{ usuariosData.total_usuarios }}</div>
                      <div class="advanced-metric-label">Total Usuarios</div>
                    </div>
                  </div>
                  
                  <div class="advanced-metric-card">
                    <div class="advanced-metric-icon">
                      <i class="fas fa-user-plus"></i>
                    </div>
                    <div class="advanced-metric-info">
                      <div class="advanced-metric-value">{{ usuariosData.usuarios_nuevos_mes }}</div>
                      <div class="advanced-metric-label">Nuevos este Mes</div>
                    </div>
                  </div>
                  
                  <div class="advanced-metric-card">
                    <div class="advanced-metric-icon">
                      <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="advanced-metric-info">
                      <div class="advanced-metric-value">{{ usuariosData.usuarios_activos }}</div>
                      <div class="advanced-metric-label">Usuarios Activos</div>
                    </div>
                  </div>
                  
                  <div class="advanced-metric-card">
                    <div class="advanced-metric-icon">
                      <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="advanced-metric-info">
                      <div class="advanced-metric-value">{{ usuariosData.tasa_actividad }}%</div>
                      <div class="advanced-metric-label">Tasa de Actividad</div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Estadísticas de Categorías -->
              <div v-if="activeReport === 'categorias'" class="advanced-report-section">
                <div class="advanced-section-header">
                  <h3><i class="fas fa-tags"></i> Estadísticas por Categoría</h3>
                </div>
                
                <div v-if="loadingCategorias" class="loading">Cargando categorías...</div>
                <div v-else-if="categoriasData.length" class="categories-grid">
                  <div v-for="categoria in categoriasData" :key="categoria.id" class="category-item">
                    <div class="category-header">
                      <div class="category-name">{{ categoria.nombre }}</div>
                      <div class="category-revenue">${{ formatCurrency(categoria.ingresos_generados) }}</div>
                    </div>
                    <div class="category-stats">
                      <span class="stat">Productos: {{ categoria.total_productos }}</span>
                      <span class="stat">Vendidos: {{ categoria.total_vendido }}</span>
                    </div>
                    <div class="revenue-bar">
                      <div 
                        class="revenue-fill" 
                        :style="{ width: calculateRevenuePercentage(categoria.ingresos_generados) + '%' }"
                      ></div>
                    </div>
                  </div>
                </div>
                <div v-else class="no-data">No hay datos de categorías</div>
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
import { adminService } from '../services/adminService';

export default {
  name: 'ReportsDashboard',
  data() {
    return {
      // DATOS EXISTENTES (NO CAMBIAR)
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
      },
      
      // Selector de tipo de reporte
      activeReportType: 'basicos',
      
      // DATOS NUEVOS PARA REPORTES AVANZADOS
      activeReport: 'ventas',
      reportTypes: [
        { id: 'ventas', name: 'Ventas', icon: 'fas fa-chart-bar' },
        { id: 'productos', name: 'Productos', icon: 'fas fa-star' },
        { id: 'usuarios', name: 'Usuarios', icon: 'fas fa-users' },
        { id: 'categorias', name: 'Categorías', icon: 'fas fa-tags' }
      ],
      
      // Ventas por período
      ventasRango: 'mensual',
      ventasData: [],
      loadingVentas: false,
      
      // Productos más vendidos
      productosPeriodo: 'todo',
      productosLimite: '10',
      productosData: [],
      loadingProductos: false,
      
      // Métricas de usuarios
      usuariosData: null,
      loadingUsuarios: false,
      
      // Estadísticas de categorías
      categoriasData: [],
      loadingCategorias: false
    };
  },
  mounted() {
    this.loadMetrics(); // Cargar reportes básicos
    this.loadVentasPorPeriodo(); // Cargar datos iniciales de reportes avanzados
  },
  methods: {
    // MÉTODOS EXISTENTES (NO CAMBIAR)
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
    },
    
    // MÉTODOS NUEVOS PARA REPORTES AVANZADOS
    selectReport(reportId) {
      this.activeReport = reportId;
      this.loadReportData(reportId);
    },
    
    async loadReportData(reportId) {
      switch(reportId) {
        case 'ventas':
          await this.loadVentasPorPeriodo();
          break;
        case 'productos':
          await this.loadProductosMasVendidos();
          break;
        case 'usuarios':
          await this.loadMetricasUsuarios();
          break;
        case 'categorias':
          await this.loadEstadisticasCategorias();
          break;
      }
    },
    
    async loadVentasPorPeriodo() {
      this.loadingVentas = true;
      try {
        const response = await adminService.getVentasPorPeriodo(this.ventasRango);
        if (response.success) {
          this.ventasData = response.data;
        }
      } catch (error) {
        console.error('Error cargando ventas:', error);
        this.ventasData = [];
      } finally {
        this.loadingVentas = false;
      }
    },
    
    async loadProductosMasVendidos() {
      this.loadingProductos = true;
      try {
        const response = await adminService.getProductosMasVendidos(
          parseInt(this.productosLimite),
          this.productosPeriodo
        );
        if (response.success) {
          this.productosData = response.data;
        }
      } catch (error) {
        console.error('Error cargando productos:', error);
        this.productosData = [];
      } finally {
        this.loadingProductos = false;
      }
    },
    
    async loadMetricasUsuarios() {
      this.loadingUsuarios = true;
      try {
        const response = await adminService.getMetricasUsuarios();
        if (response.success) {
          this.usuariosData = response.data;
        }
      } catch (error) {
        console.error('Error cargando métricas de usuarios:', error);
        this.usuariosData = null;
      } finally {
        this.loadingUsuarios = false;
      }
    },
    
    async loadEstadisticasCategorias() {
      this.loadingCategorias = true;
      try {
        const response = await adminService.getEstadisticasCategorias();
        if (response.success) {
          this.categoriasData = response.data;
        }
      } catch (error) {
        console.error('Error cargando estadísticas de categorías:', error);
        this.categoriasData = [];
      } finally {
        this.loadingCategorias = false;
      }
    },
    
    formatPeriodLabel(item) {
      if (this.ventasRango === 'diario') {
        return new Date(item.fecha).toLocaleDateString('es-ES');
      } else if (this.ventasRango === 'semanal') {
        return `Semana ${item.semana}, ${item.año}`;
      } else {
        const monthNames = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return `${monthNames[item.mes - 1]} ${item.año}`;
      }
    },
    
    calculateRevenuePercentage(ingresos) {
      if (!this.categoriasData.length) return 0;
      const maxIngresos = Math.max(...this.categoriasData.map(cat => parseFloat(cat.ingresos_generados || 0)));
      return maxIngresos > 0 ? (parseFloat(ingresos || 0) / maxIngresos) * 100 : 0;
    }
  }
};
</script>

<style scoped>
/* TODOS TUS ESTILOS ORIGINALES SE MANTIENEN IGUAL */
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

/* SELECTOR DE TIPO DE REPORTE */
.reports-type-selector {
  display: flex;
  gap: 10px;
  margin: 0;
  background: #f8f9fa;
  padding: 15px 20px;
  border-bottom: 1px solid #e9ecef;
}

.report-type-btn {
  flex: 1;
  padding: 12px 20px;
  border: 2px solid #e9ecef;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 10px;
  justify-content: center;
  transition: all 0.3s;
  font-size: 0.9rem;
  font-weight: 500;
}

.report-type-btn:hover {
  border-color: #1e88e5;
  transform: translateY(-2px);
}

.report-type-btn.active {
  background: #1e88e5;
  color: white;
  border-color: #1e88e5;
}

.reports-content {
  flex: 1;
  padding: 0;
  overflow-y: auto;
}

/* ESTILOS EXISTENTES (NO CAMBIAR) */
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
  padding: 30px;
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

/* ESTILOS NUEVOS PARA REPORTES AVANZADOS */
.advanced-reports-section {
  height: 100%;
}

.advanced-reports-content {
  display: flex;
  height: 100%;
  min-height: 500px;
}

.advanced-report-selector {
  width: 250px;
  background: #f8f9fa;
  border-right: 1px solid #e9ecef;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex-shrink: 0;
}

.advanced-report-btn {
  background: white;
  border: 2px solid #e9ecef;
  padding: 15px;
  border-radius: 8px;
  cursor: pointer;
  text-align: left;
  display: flex;
  align-items: center;
  gap: 10px;
  transition: all 0.3s;
  font-weight: 500;
}

.advanced-report-btn:hover {
  border-color: #1e88e5;
  transform: translateX(5px);
}

.advanced-report-btn.active {
  background: #1e88e5;
  color: white;
  border-color: #1e88e5;
}

.advanced-report-panel {
  flex: 1;
  padding: 25px;
  background: #f8f9fa;
  overflow-y: auto;
}

.advanced-report-section {
  animation: fadeIn 0.3s ease-in;
}

.advanced-section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 25px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e9ecef;
}

.advanced-section-header h3 {
  margin: 0;
  color: #2c3e50;
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 1.3rem;
}

.advanced-filters {
  display: flex;
  gap: 10px;
}

select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
  font-size: 0.9rem;
}

.data-grid, .products-grid, .categories-grid {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.data-item, .product-item, .category-item {
  background: white;
  padding: 18px;
  border-radius: 8px;
  border-left: 4px solid #1e88e5;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s;
}

.data-item:hover, .product-item:hover, .category-item:hover {
  transform: translateY(-2px);
}

.period-label {
  font-weight: bold;
  margin-bottom: 8px;
  color: #2c3e50;
  font-size: 1rem;
}

.period-stats {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat {
  font-size: 0.9rem;
  color: #7f8c8d;
}

.amount {
  font-weight: bold;
  color: #27ae60;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-rank {
  background: #1e88e5;
  color: white;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  flex-shrink: 0;
  font-size: 0.9rem;
}

.product-info {
  flex: 1;
}

.product-name {
  font-weight: bold;
  margin-bottom: 6px;
  color: #2c3e50;
  font-size: 1rem;
}

.product-stats {
  display: flex;
  gap: 15px;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.sales {
  color: #27ae60;
  font-weight: 600;
}

.revenue {
  color: #1e88e5;
  font-weight: 600;
}

.product-price {
  font-weight: bold;
  color: #2c3e50;
  font-size: 1rem;
}

.advanced-metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.advanced-metric-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 25px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.advanced-metric-card:hover {
  transform: translateY(-5px);
}

.advanced-metric-icon {
  font-size: 2rem;
  color: #1e88e5;
  margin-bottom: 12px;
}

.advanced-metric-value {
  font-size: 2rem;
  font-weight: bold;
  color: #2c3e50;
  margin-bottom: 8px;
}

.advanced-metric-label {
  color: #7f8c8d;
  font-size: 0.9rem;
  font-weight: 500;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.category-name {
  font-weight: bold;
  color: #2c3e50;
  font-size: 1rem;
}

.category-revenue {
  font-weight: bold;
  color: #27ae60;
  font-size: 1rem;
}

.category-stats {
  display: flex;
  gap: 15px;
  margin-bottom: 12px;
  font-size: 0.85rem;
  color: #7f8c8d;
}

.revenue-bar {
  height: 6px;
  background: #e9ecef;
  border-radius: 3px;
  overflow: hidden;
}

.revenue-fill {
  height: 100%;
  background: linear-gradient(90deg, #1e88e5, #0d47a1);
  transition: width 0.5s ease;
}

.loading, .no-data {
  text-align: center;
  padding: 60px 20px;
  color: #7f8c8d;
  font-style: italic;
  font-size: 1.1rem;
}

.loading {
  color: #1e88e5;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
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
  
  .reports-type-selector {
    flex-direction: column;
    padding: 10px 15px;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
    padding: 20px;
  }
  
  .metric-card {
    padding: 20px;
  }
  
  .advanced-reports-content {
    flex-direction: column;
  }
  
  .advanced-report-selector {
    width: 100%;
    flex-direction: row;
    overflow-x: auto;
    padding: 15px;
  }
  
  .advanced-report-btn {
    white-space: nowrap;
    flex: 1;
    justify-content: center;
    min-width: 120px;
  }
  
  .advanced-report-panel {
    padding: 20px;
  }
  
  .advanced-section-header {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }
  
  .advanced-filters {
    width: 100%;
    justify-content: space-between;
  }
  
  .advanced-metrics-grid {
    grid-template-columns: 1fr;
  }
}
</style> 