<template>
  <div class="advanced-reports-page">
    <!-- Header del panel administrativo -->
    <div class="admin-page-header">
      <button class="back-btn" @click="$router.push('/admin')">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
      </button>
      <h1><i class="fas fa-chart-line"></i> Reportes Avanzados</h1>
    </div>

    <!-- Contenido principal -->
    <div class="reports-content">
      <!-- Selector de reportes -->
      <div class="report-selector">
        <button 
          v-for="report in reportTypes" 
          :key="report.id"
          :class="['report-btn', { active: activeReport === report.id }]"
          @click="selectReport(report.id)"
        >
          <i :class="report.icon"></i>
          {{ report.name }}
        </button>
      </div>

      <!-- Panel de reporte activo -->
      <div class="report-panel">
        <!-- Ventas por Período -->
        <div v-if="activeReport === 'ventas'" class="report-section">
          <div class="section-header">
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
        <div v-if="activeReport === 'productos'" class="report-section">
          <div class="section-header">
            <h3><i class="fas fa-star"></i> Productos Más Vendidos</h3>
            <div class="filters">
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
        <div v-if="activeReport === 'usuarios'" class="report-section">
          <div class="section-header">
            <h3><i class="fas fa-users"></i> Métricas de Usuarios</h3>
          </div>
          
          <div v-if="loadingUsuarios" class="loading">Cargando métricas...</div>
          <div v-else-if="usuariosData" class="metrics-grid">
            <div class="metric-card">
              <div class="metric-icon">
                <i class="fas fa-user-friends"></i>
              </div>
              <div class="metric-info">
                <div class="metric-value">{{ usuariosData.total_usuarios }}</div>
                <div class="metric-label">Total Usuarios</div>
              </div>
            </div>
            
            <div class="metric-card">
              <div class="metric-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <div class="metric-info">
                <div class="metric-value">{{ usuariosData.usuarios_nuevos_mes }}</div>
                <div class="metric-label">Nuevos este Mes</div>
              </div>
            </div>
            
            <div class="metric-card">
              <div class="metric-icon">
                <i class="fas fa-shopping-cart"></i>
              </div>
              <div class="metric-info">
                <div class="metric-value">{{ usuariosData.usuarios_activos }}</div>
                <div class="metric-label">Usuarios Activos</div>
              </div>
            </div>
            
            <div class="metric-card">
              <div class="metric-icon">
                <i class="fas fa-chart-pie"></i>
              </div>
              <div class="metric-info">
                <div class="metric-value">{{ usuariosData.tasa_actividad }}%</div>
                <div class="metric-label">Tasa de Actividad</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Estadísticas de Categorías -->
        <div v-if="activeReport === 'categorias'" class="report-section">
          <div class="section-header">
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
</template>

<script>
import { adminService } from '../services/adminService';

export default {
  name: 'AdvancedReports',
  data() {
    return {
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
    this.loadInitialData();
  },
  
  methods: {
    selectReport(reportId) {
      this.activeReport = reportId;
      this.loadReportData(reportId);
    },
    
    async loadInitialData() {
      await this.loadVentasPorPeriodo();
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
    
    formatCurrency(amount) {
      return parseFloat(amount || 0).toFixed(2);
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
.advanced-reports-page {
  padding: 20px;
  background: white;
  border-radius: 8px;
  margin: 20px;
  min-height: calc(100vh - 100px);
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.admin-page-header {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 30px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e9ecef;
}

.back-btn {
  background: #1e88e5;
  color: white;
  border: none;
  padding: 10px 15px;
  border-radius: 6px;
  cursor: pointer;
  transition: background 0.3s;
}

.back-btn:hover {
  background: #0d47a1;
}

.admin-page-header h1 {
  margin: 0;
  color: #333;
  font-size: 1.8rem;
}

.reports-content {
  display: flex;
  flex: 1;
  gap: 20px;
  min-height: 600px;
}

.report-selector {
  width: 250px;
  background: #f8f9fa;
  border-right: 1px solid #e9ecef;
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
  flex-shrink: 0;
  border-radius: 8px;
}

.report-btn {
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
}

.report-btn:hover {
  border-color: #1e88e5;
  transform: translateX(5px);
}

.report-btn.active {
  background: #1e88e5;
  color: white;
  border-color: #1e88e5;
}

.report-panel {
  flex: 1;
  padding: 20px;
  background: #f8f9fa;
  border-radius: 8px;
  overflow-y: auto;
}

.report-section {
  animation: fadeIn 0.3s ease-in;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 15px;
  border-bottom: 2px solid #e9ecef;
}

.section-header h3 {
  margin: 0;
  color: #333;
  display: flex;
  align-items: center;
  gap: 10px;
}

.filters {
  display: flex;
  gap: 10px;
}

select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 6px;
  background: white;
}

.data-grid, .products-grid, .categories-grid {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.data-item, .product-item, .category-item {
  background: white;
  padding: 15px;
  border-radius: 8px;
  border-left: 4px solid #1e88e5;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.period-label {
  font-weight: bold;
  margin-bottom: 8px;
  color: #333;
}

.period-stats {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.stat {
  font-size: 0.9rem;
  color: #666;
}

.amount {
  font-weight: bold;
  color: #0d47a1;
}

.product-item {
  display: flex;
  align-items: center;
  gap: 15px;
}

.product-rank {
  background: #1e88e5;
  color: white;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  flex-shrink: 0;
}

.product-info {
  flex: 1;
}

.product-name {
  font-weight: bold;
  margin-bottom: 5px;
}

.product-stats {
  display: flex;
  gap: 15px;
  font-size: 0.85rem;
  color: #666;
}

.sales {
  color: #28a745;
}

.revenue {
  color: #0d47a1;
}

.product-price {
  font-weight: bold;
  color: #333;
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.metric-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  transition: transform 0.3s;
}

.metric-card:hover {
  transform: translateY(-5px);
}

.metric-icon {
  font-size: 2rem;
  color: #1e88e5;
  margin-bottom: 10px;
}

.metric-value {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
  margin-bottom: 5px;
}

.metric-label {
  color: #666;
  font-size: 0.9rem;
}

.category-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 10px;
}

.category-name {
  font-weight: bold;
  color: #333;
}

.category-revenue {
  font-weight: bold;
  color: #0d47a1;
}

.category-stats {
  display: flex;
  gap: 15px;
  margin-bottom: 10px;
  font-size: 0.85rem;
  color: #666;
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
  padding: 40px;
  color: #666;
  font-style: italic;
}

.loading {
  color: #1e88e5;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
  .reports-content {
    flex-direction: column;
  }
  
  .report-selector {
    width: 100%;
    flex-direction: row;
    overflow-x: auto;
  }
  
  .report-btn {
    white-space: nowrap;
    flex: 1;
    justify-content: center;
  }
  
  .metrics-grid {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    gap: 10px;
    align-items: flex-start;
  }
  
  .filters {
    width: 100%;
    justify-content: space-between;
  }
}
</style>