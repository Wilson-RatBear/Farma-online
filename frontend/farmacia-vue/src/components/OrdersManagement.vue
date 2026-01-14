<template>
  <div class="orders-management-page">
    <!-- Header fijo -->
    <header class="page-header">
      <div class="header-content">
        <button class="back-btn" @click="$router.push('/admin')">
            <i class="fas fa-arrow-left"></i>
          </button>
        <div class="header-left">
          <div class="page-title">
            <h1>
              Gestión de Pedidos
            </h1>
            <p class="subtitle">Administra y actualiza el estado de los pedidos</p>
          </div>
        </div>
        
        <div class="header-right">
          <div class="header-stats">
            <span class="stat-item">
              <i class="fas fa-clock"></i>
              {{ stats.pendientes || 0 }} Pendientes
            </span>
            <span class="stat-item">
              <i class="fas fa-shipping-fast"></i>
              {{ stats.enviados || 0 }} Enviados
            </span>
          </div>
        </div>
      </div>
    </header>

    <main class="page-main">
      <!-- Panel de filtros -->
      <div class="filters-panel">
        <div class="filters-grid">
          <div class="filter-group">
            <div class="search-box">
              <i class="fas fa-search"></i>
              <input 
                v-model="filters.search" 
                type="text" 
                placeholder="Buscar por número, cliente o dirección..."
                class="search-input"
                @input="applyFilters"
              >
            </div>
          </div>
          
          <div class="filter-group">
            <label class="filter-label">
              <i class="fas fa-filter"></i> Estado:
            </label>
            <select v-model="filters.estado" @change="applyFilters" class="filter-select">
              <option value="">Todos los estados</option>
              <option value="pendiente">🟡 Pendientes</option>
              <option value="confirmado">🔵 Confirmados</option>
              <option value="enviado">🚚 Enviados</option>
              <option value="entregado">✅ Entregados</option>
              <option value="cancelado">❌ Cancelados</option>
            </select>
          </div>
          
          <div class="filter-group">
            <button class="filter-btn reset-btn" @click="resetFilters">
              <i class="fas fa-times"></i> Limpiar
            </button>
            <button class="filter-btn refresh-btn" @click="loadOrders" :disabled="loading">
              <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
              Actualizar
            </button>
          </div>
        </div>
      </div>

      <!-- Resumen rápido -->
      <div class="quick-stats">
        <div class="stats-grid">
          <div class="stat-card total">
            <div class="stat-icon">
              <i class="fas fa-shopping-bag"></i>
            </div>
            <div class="stat-info">
              <h3>{{ totalOrders }}</h3>
              <p>Total Pedidos</p>
            </div>
          </div>
          
          <div class="stat-card pending" @click="filterByStatus('pendiente')">
            <div class="stat-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.pendientes || 0 }}</h3>
              <p>Pendientes</p>
            </div>
          </div>
          
          <div class="stat-card confirmed" @click="filterByStatus('confirmado')">
            <div class="stat-icon">
              <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.confirmados || 0 }}</h3>
              <p>Confirmados</p>
            </div>
          </div>
          
          <div class="stat-card revenue">
            <div class="stat-icon">
              <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-info">
              <h3>${{ totalRevenue }}</h3>
              <p>Ingresos</p>
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
          <p>Cargando pedidos...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <div class="error-content">
            <i class="fas fa-exclamation-triangle"></i>
            <h4>Error al cargar pedidos</h4>
            <p>{{ error }}</p>
            <button @click="loadOrders" class="btn-primary">
              <i class="fas fa-redo-alt"></i> Reintentar
            </button>
          </div>
        </div>

        <div v-else-if="filteredOrders.length === 0" class="empty-state">
          <div class="empty-content">
            <i class="fas fa-box-open"></i>
            <h4>No hay pedidos</h4>
            <p v-if="filters.search || filters.estado">
              No se encontraron pedidos con los filtros aplicados.
            </p>
            <p v-else>
              No hay pedidos registrados en el sistema.
            </p>
            <button @click="resetFilters" class="btn-primary">
              <i class="fas fa-times"></i> Limpiar filtros
            </button>
          </div>
        </div>

        <!-- Lista de pedidos -->
        <div v-else class="orders-container">
          <div class="orders-header">
            <h3>
              <i class="fas fa-list"></i>
              Lista de Pedidos ({{ filteredOrders.length }})
            </h3>
            <div class="orders-actions">
              <button class="action-btn export-btn" @click="exportOrders">
                <i class="fas fa-file-export"></i> Exportar
              </button>
            </div>
          </div>

          <div class="orders-grid">
            <div 
              v-for="order in filteredOrders" 
              :key="order.id" 
              class="order-card"
              :class="'status-' + order.estado"
            >
              <!-- Cabecera del pedido -->
              <div class="order-card-header">
                <div class="order-info">
                  <div class="order-number">
                    <span class="order-id">#{{ order.numero_orden }}</span>
                    <span class="order-date">{{ formatDate(order.created_at) }}</span>
                  </div>
                  <div class="customer-info">
                    <div class="customer-name">
                      <i class="fas fa-user"></i>
                      {{ order.usuario?.name || 'Cliente' }}
                    </div>
                    <div class="customer-contact">
                      <i class="fas fa-phone"></i>
                      {{ order.telefono_contacto || 'Sin teléfono' }}
                    </div>
                  </div>
                </div>
                
                <div class="order-status-section">
                  <div class="status-display">
                    <span class="status-label">Estado:</span>
                    <span class="status-badge" :class="order.estado">
                      {{ getStatusText(order.estado) }}
                    </span>
                  </div>
                  <div class="order-total">
                    <span class="total-label">Total:</span>
                    <span class="total-amount">${{ order.total }}</span>
                  </div>
                </div>
              </div>

              <!-- Detalles del pedido -->
              <div class="order-card-body">
                <div class="order-details-grid">
                  <!-- Productos -->
                  <div class="detail-section products-section">
                    <h4><i class="fas fa-capsules"></i> Productos</h4>
                    <div class="products-list">
                      <div 
                        v-for="item in order.items" 
                        :key="item.id" 
                        class="product-item"
                      >
                        <div class="product-info">
                          <span class="product-name">{{ item.producto.nombre }}</span>
                          <span class="product-sku">SKU: {{ item.producto.codigo || 'N/A' }}</span>
                        </div>
                        <div class="product-quantity">
                          <span class="quantity">x{{ item.cantidad }}</span>
                          <span class="price">${{ item.precio_unitario }} c/u</span>
                        </div>
                        <div class="product-subtotal">
                          ${{ (item.cantidad * item.precio_unitario).toFixed(2) }}
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Información de envío -->
                  <div class="detail-section shipping-section">
                    <h4><i class="fas fa-truck"></i> Envío</h4>
                    <div class="shipping-info">
                      <p class="shipping-address">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ order.direccion_envio }}, {{ order.ciudad_envio }}
                      </p>
                      <p class="shipping-method">
                        <i class="fas fa-credit-card"></i>
                        <strong>Pago:</strong> {{ order.metodo_pago }}
                      </p>
                      <p class="shipping-notes" v-if="order.notas">
                        <i class="fas fa-sticky-note"></i>
                        <strong>Notas:</strong> {{ order.notas }}
                      </p>
                    </div>
                  </div>

                  <!-- Gestión de estado mejorada -->
                  <div class="detail-section status-section">
                    <h4><i class="fas fa-exchange-alt"></i> Cambiar Estado</h4>
                    <div class="status-controls">
                      <div class="current-status-info">
                        <p>
                          <strong>Estado actual:</strong> 
                          <span class="current-status-badge" :class="order.estado">
                            {{ getStatusText(order.estado) }}
                          </span>
                        </p>
                        <p class="notification-info">
                          <i class="fas fa-envelope"></i>
                          El cliente recibirá una notificación
                        </p>
                      </div>
                      
                      <div class="status-buttons-grid">
                        <!-- Botones de acción rápida -->
                        <div class="quick-actions">
                          <button 
                            v-if="order.estado === 'pendiente'"
                            @click="updateOrderStatus(order, 'confirmado')"
                            class="status-action-btn confirm-action"
                            :disabled="loading"
                          >
                            <i class="fas fa-check"></i>
                            Confirmar Pedido
                          </button>
                          
                          <button 
                            v-if="order.estado === 'confirmado'"
                            @click="updateOrderStatus(order, 'enviado')"
                            class="status-action-btn ship-action"
                            :disabled="loading"
                          >
                            <i class="fas fa-shipping-fast"></i>
                            Marcar como Enviado
                          </button>
                          
                          <button 
                            v-if="order.estado === 'enviado'"
                            @click="updateOrderStatus(order, 'entregado')"
                            class="status-action-btn deliver-action"
                            :disabled="loading"
                          >
                            <i class="fas fa-box-open"></i>
                            Marcar como Entregado
                          </button>
                          
                          <button 
                            @click="updateOrderStatus(order, 'cancelado')"
                            class="status-action-btn cancel-action"
                            :disabled="loading || order.estado === 'cancelado'"
                          >
                            <i class="fas fa-times"></i>
                            Cancelar Pedido
                          </button>
                        </div>
                        
                        <!-- Selector de estado -->
                        <div class="status-selector">
                          <label class="selector-label">
                            <i class="fas fa-cogs"></i> Cambiar manualmente:
                          </label>
                          <select 
                            v-model="order.estado_temp" 
                            @change="updateOrderStatus(order, order.estado_temp)"
                            class="status-select"
                            :disabled="loading"
                          >
                            <option value="pendiente">🟡 Pendiente</option>
                            <option value="confirmado">🔵 Confirmado</option>
                            <option value="enviado">🚚 Enviado</option>
                            <option value="entregado">✅ Entregado</option>
                            <option value="cancelado">❌ Cancelado</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Acciones del pedido -->
              <div class="order-card-footer">
                <div class="order-actions">
                  <button 
                    class="action-btn details-btn"
                    @click="viewOrderDetails(order)"
                  >
                    <i class="fas fa-eye"></i> Ver Detalles Completos
                  </button>
                  <button 
                    class="action-btn print-btn"
                    @click="printOrder(order)"
                  >
                    <i class="fas fa-print"></i> Imprimir
                  </button>
                  <button 
                    class="action-btn copy-btn"
                    @click="copyOrderNumber(order)"
                  >
                    <i class="fas fa-copy"></i> Copiar Número
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Paginación (si hay muchos pedidos) -->
          <div v-if="filteredOrders.length > 10" class="pagination">
            <div class="pagination-info">
              Mostrando {{ Math.min(filteredOrders.length, 10) }} de {{ filteredOrders.length }} pedidos
            </div>
            <div class="pagination-controls">
              <button class="pagination-btn" :disabled="currentPage === 1">
                <i class="fas fa-chevron-left"></i>
              </button>
              <span class="page-info">Página {{ currentPage }}</span>
              <button class="pagination-btn" :disabled="currentPage * 10 >= filteredOrders.length">
                <i class="fas fa-chevron-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'OrdersManagementPage',
  data() {
    return {
      orders: [],
      filteredOrders: [],
      loading: false,
      error: null,
      filters: {
        search: '',
        estado: ''
      },
      stats: {
        pendientes: 0,
        confirmados: 0,
        enviados: 0,
        entregados: 0,
        cancelados: 0
      },
      currentPage: 1
    }
  },
  computed: {
    totalOrders() {
      return this.orders.length;
    },
    totalRevenue() {
      return this.orders.reduce((sum, order) => sum + parseFloat(order.total || 0), 0).toFixed(2);
    }
  },
  async mounted() {
    await this.loadOrders();
  },
  methods: {
    async loadOrders() {
      try {
        this.loading = true;
        this.error = null;
        
        const token = localStorage.getItem('auth_token');
        const response = await axios.get('http://localhost:8000/api/admin/pedidos', {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        });
        
        if (response.data.success && response.data.pedidos) {
          this.orders = response.data.pedidos.map(order => ({
            ...order,
            estado_temp: order.estado
          }));
          this.filteredOrders = [...this.orders];
          this.calculateStats();
        } else {
          throw new Error('Estructura de respuesta incorrecta');
        }
        
      } catch (error) {
        console.error('Error:', error);
        this.error = 'Error al cargar los pedidos. Por favor, intenta nuevamente.';
        // Datos de ejemplo para desarrollo
        this.loadSampleData();
      } finally {
        this.loading = false;
      }
    },

    loadSampleData() {
      this.orders = [
        {
          id: 1,
          numero_orden: 'ORD-2024-001',
          usuario: { name: 'Juan Pérez', email: 'juan@email.com' },
          total: '125.50',
          estado: 'pendiente',
          estado_temp: 'pendiente',
          created_at: new Date().toISOString(),
          telefono_contacto: '555-1234',
          direccion_envio: 'Calle Principal 123',
          ciudad_envio: 'Ciudad de México',
          metodo_pago: 'Tarjeta de crédito',
          notas: 'Entregar por la tarde',
          items: [
            { id: 1, cantidad: 2, precio_unitario: 25.50, producto: { nombre: 'Paracetamol 500mg', codigo: 'PCT-001' } },
            { id: 2, cantidad: 1, precio_unitario: 35.75, producto: { nombre: 'Ibuprofeno 400mg', codigo: 'IBU-001' } },
            { id: 3, cantidad: 1, precio_unitario: 39.75, producto: { nombre: 'Jarabe para la tos', codigo: 'JAR-001' } }
          ]
        },
        {
          id: 2,
          numero_orden: 'ORD-2024-002',
          usuario: { name: 'María García', email: 'maria@email.com' },
          total: '89.99',
          estado: 'confirmado',
          estado_temp: 'confirmado',
          created_at: new Date(Date.now() - 86400000).toISOString(),
          telefono_contacto: '555-5678',
          direccion_envio: 'Av. Libertad 456',
          ciudad_envio: 'Guadalajara',
          metodo_pago: 'PayPal',
          items: [
            { id: 4, cantidad: 1, precio_unitario: 89.99, producto: { nombre: 'Vitaminas C', codigo: 'VIT-001' } }
          ]
        },
        {
          id: 3,
          numero_orden: 'ORD-2024-003',
          usuario: { name: 'Carlos López', email: 'carlos@email.com' },
          total: '210.75',
          estado: 'enviado',
          estado_temp: 'enviado',
          created_at: new Date(Date.now() - 172800000).toISOString(),
          telefono_contacto: '555-9012',
          direccion_envio: 'Plaza Central 789',
          ciudad_envio: 'Monterrey',
          metodo_pago: 'Transferencia bancaria',
          items: [
            { id: 5, cantidad: 3, precio_unitario: 45.25, producto: { nombre: 'Jabón antibacterial', codigo: 'JAB-001' } },
            { id: 6, cantidad: 1, precio_unitario: 75.00, producto: { nombre: 'Termómetro digital', codigo: 'TER-001' } }
          ]
        }
      ];
      this.filteredOrders = [...this.orders];
      this.calculateStats();
    },

    calculateStats() {
      this.stats = {
        pendientes: this.orders.filter(o => o.estado === 'pendiente').length,
        confirmados: this.orders.filter(o => o.estado === 'confirmado').length,
        enviados: this.orders.filter(o => o.estado === 'enviado').length,
        entregados: this.orders.filter(o => o.estado === 'entregado').length,
        cancelados: this.orders.filter(o => o.estado === 'cancelado').length
      };
    },

    applyFilters() {
      let filtered = this.orders;

      if (this.filters.search) {
        const searchLower = this.filters.search.toLowerCase();
        filtered = filtered.filter(order => 
          order.numero_orden.toLowerCase().includes(searchLower) ||
          (order.usuario?.name?.toLowerCase().includes(searchLower)) ||
          order.direccion_envio.toLowerCase().includes(searchLower) ||
          order.ciudad_envio.toLowerCase().includes(searchLower)
        );
      }

      if (this.filters.estado) {
        filtered = filtered.filter(order => order.estado === this.filters.estado);
      }

      this.filteredOrders = filtered;
    },

    resetFilters() {
      this.filters.search = '';
      this.filters.estado = '';
      this.filteredOrders = [...this.orders];
    },

    filterByStatus(status) {
      this.filters.estado = status;
      this.applyFilters();
    },

    async updateOrderStatus(order, nuevoEstado) {
      try {
        this.loading = true;
        
        // Simulación para desarrollo
        if (process.env.NODE_ENV === 'development') {
          await new Promise(resolve => setTimeout(resolve, 500));
          
          const oldStatus = order.estado;
          order.estado = nuevoEstado;
          order.estado_temp = nuevoEstado;
          
          this.calculateStats();
          this.applyFilters();
          
          alert(`✅ Estado actualizado\nPedido #${order.numero_orden}\nDe: ${this.getStatusText(oldStatus)}\nA: ${this.getStatusText(nuevoEstado)}\n\nEl cliente ha sido notificado.`);
        } else {
          // Código real para producción
          const token = localStorage.getItem('auth_token');
          const response = await axios.post(
            `http://localhost:8000/api/pedidos/${order.id}/estado`,
            { estado: nuevoEstado },
            {
              headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
              }
            }
          );

          if (response.data.success) {
            order.estado = nuevoEstado;
            order.estado_temp = nuevoEstado;
            this.calculateStats();
            this.applyFilters();
            
            alert(`✅ Estado actualizado\nPedido #${order.numero_orden} actualizado a "${this.getStatusText(nuevoEstado)}"\nNotificación enviada al cliente.`);
          }
        }
      } catch (error) {
        console.error('Error actualizando estado:', error);
        alert(`❌ Error: ${error.response?.data?.message || 'No se pudo actualizar el estado del pedido'}`);
      } finally {
        this.loading = false;
      }
    },

    getStatusText(estado) {
      const estados = {
        'pendiente': 'Pendiente',
        'confirmado': 'Confirmado', 
        'enviado': 'Enviado',
        'entregado': 'Entregado',
        'cancelado': 'Cancelado'
      };
      return estados[estado] || estado;
    },

    formatDate(dateString) {
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        });
      } catch {
        return 'Fecha no disponible';
      }
    },

    viewOrderDetails(order) {
      const detalles = `
📦 PEDIDO #${order.numero_orden}
━━━━━━━━━━━━━━━━━━━━━━
👤 CLIENTE: ${order.usuario?.name || 'N/A'}
📧 EMAIL: ${order.usuario?.email || 'N/A'}
📞 TELÉFONO: ${order.telefono_contacto || 'N/A'}

📍 DIRECCIÓN: 
${order.direccion_envio || 'N/A'}
${order.ciudad_envio || ''}

💰 TOTAL: $${order.total}
💳 MÉTODO DE PAGO: ${order.metodo_pago}
📝 NOTAS: ${order.notas || 'Ninguna'}

📦 PRODUCTOS:
${order.items.map(item => `  • ${item.producto.nombre} x${item.cantidad} - $${item.precio_unitario} c/u`).join('\n')}

━━━━━━━━━━━━━━━━━━━━━━
🔄 ESTADO ACTUAL: ${this.getStatusText(order.estado)}
📅 FECHA: ${this.formatDate(order.created_at)}
      `;
      
      alert(detalles);
    },

    exportOrders() {
      alert('📤 Función de exportación activada\nSe exportarán los pedidos filtrados.');
    },

    printOrder(order) {
      alert(`🖨️ Imprimiendo pedido #${order.numero_orden}`);
    },

    copyOrderNumber(order) {
      navigator.clipboard.writeText(order.numero_orden);
      alert(`📋 Número de pedido copiado: ${order.numero_orden}`);
    }
  }
}
</script>

<style scoped>
/* ===== ESTRUCTURA DE PÁGINA ===== */
.orders-management-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  width: 100%;
}

/* ===== HEADER ===== */
.page-header {
  background: linear-gradient(135deg, #1e3a8a, #1e88e5);
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

/* ===== CONTENIDO PRINCIPAL ===== */
.page-main {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

/* ===== PANEL DE FILTROS ===== */
.filters-panel {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
}

.filters-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1.5rem;
  align-items: end;
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
  width: 100%;
  padding: 12px 20px 12px 45px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.filter-select {
  padding: 12px 15px;
  border: 2px solid #e2e8f0;
  border-radius: 10px;
  font-size: 0.95rem;
  background: white;
  color: #334155;
  cursor: pointer;
  transition: all 0.3s ease;
  font-weight: 500;
}

.filter-select:focus {
  outline: none;
  border-color: #3b82f6;
}

.filter-btn {
  padding: 12px 20px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
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
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
}

.refresh-btn:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.refresh-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* ===== ESTADÍSTICAS RÁPIDAS ===== */
.quick-stats {
  margin-bottom: 2rem;
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
  border-color: #c7d2fe;
}

.stat-card.pending:hover {
  border-color: #fbbf24;
}

.stat-card.confirmed:hover {
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
  color: #3b82f6;
}

.stat-card.pending .stat-icon {
  background: #fef3c7;
  color: #d97706;
}

.stat-card.confirmed .stat-icon {
  background: #dbeafe;
  color: #1d4ed8;
}

.stat-card.revenue .stat-icon {
  background: #d1fae5;
  color: #065f46;
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
  color: #3b82f6;
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
  background: linear-gradient(135deg, #3b82f6, #2563eb);
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
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

/* ===== LISTA DE PEDIDOS ===== */
.orders-container {
  padding: 2rem;
}

.orders-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #f1f5f9;
}

.orders-header h3 {
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

.orders-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

/* ===== TARJETA DE PEDIDO ===== */
.order-card {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
}

.order-card:hover {
  border-color: #c7d2fe;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  transform: translateY(-3px);
}

.order-card.status-pendiente {
  border-left: 4px solid #f59e0b;
}

.order-card.status-confirmado {
  border-left: 4px solid #3b82f6;
}

.order-card.status-enviado {
  border-left: 4px solid #8b5cf6;
}

.order-card.status-entregado {
  border-left: 4px solid #10b981;
}

.order-card.status-cancelado {
  border-left: 4px solid #ef4444;
}

.order-card-header {
  background: #f8fafc;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 2px solid #e2e8f0;
}

.order-info {
  flex: 1;
}

.order-number {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 0.8rem;
}

.order-id {
  font-size: 1.3rem;
  font-weight: 700;
  color: #1e293b;
  font-family: 'Courier New', monospace;
  background: #e2e8f0;
  padding: 0.3rem 0.8rem;
  border-radius: 6px;
}

.order-date {
  color: #64748b;
  font-size: 0.9rem;
}

.customer-info {
  display: flex;
  gap: 1.5rem;
}

.customer-name,
.customer-contact {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #475569;
  font-weight: 500;
}

.order-status-section {
  text-align: right;
}

.status-display {
  margin-bottom: 0.8rem;
}

.status-label {
  display: block;
  color: #64748b;
  font-size: 0.85rem;
  margin-bottom: 0.3rem;
}

.status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.pendiente {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}

.status-badge.confirmado {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #60a5fa;
}

.status-badge.enviado {
  background: #ede9fe;
  color: #5b21b6;
  border: 1px solid #a78bfa;
}

.status-badge.entregado {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #34d399;
}

.status-badge.cancelado {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #fca5a5;
}

.order-total {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  gap: 0.8rem;
}

.total-label {
  color: #64748b;
  font-size: 0.9rem;
}

.total-amount {
  font-size: 1.5rem;
  font-weight: 700;
  color: #059669;
}

/* ===== CUERPO DE LA TARJETA ===== */
.order-card-body {
  padding: 1.5rem;
}

.order-details-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 2rem;
}

.detail-section {
  background: #f8fafc;
  padding: 1.5rem;
  border-radius: 10px;
  border: 1px solid #e2e8f0;
}

.detail-section h4 {
  margin: 0 0 1.2rem 0;
  color: #1e3a8a;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  padding-bottom: 0.8rem;
  border-bottom: 2px solid #e2e8f0;
}

.products-section {
  grid-row: span 2;
}

.products-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.product-item {
  display: grid;
  grid-template-columns: 2fr 1fr auto;
  gap: 1rem;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e2e8f0;
  align-items: center;
}

.product-info {
  display: flex;
  flex-direction: column;
  gap: 0.3rem;
}

.product-name {
  font-weight: 600;
  color: #1e293b;
}

.product-sku {
  font-size: 0.8rem;
  color: #94a3b8;
}

.product-quantity {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.3rem;
}

.quantity {
  font-weight: 600;
  color: #475569;
}

.price {
  font-size: 0.85rem;
  color: #64748b;
}

.product-subtotal {
  font-weight: 700;
  color: #059669;
  font-size: 1.1rem;
  min-width: 80px;
  text-align: right;
}

.shipping-info {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.shipping-address,
.shipping-method,
.shipping-notes {
  display: flex;
  align-items: flex-start;
  gap: 0.8rem;
  color: #475569;
}

.shipping-address i {
  color: #ef4444;
  margin-top: 0.2rem;
}

.shipping-method i {
  color: #10b981;
}

.shipping-notes i {
  color: #f59e0b;
}

/* ===== SECCIÓN DE ESTADO ===== */
.status-section {
  grid-column: 1 / -1;
}

.status-controls {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.current-status-info {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  background: white;
  border-radius: 8px;
  border: 2px solid #e2e8f0;
}

.current-status-badge {
  padding: 6px 16px;
  border-radius: 20px;
  font-weight: 600;
  margin-left: 0.8rem;
}

.current-status-badge.pendiente {
  background: #fef3c7;
  color: #92400e;
}

.current-status-badge.confirmado {
  background: #dbeafe;
  color: #1e40af;
}

.current-status-badge.enviado {
  background: #ede9fe;
  color: #5b21b6;
}

.current-status-badge.entregado {
  background: #d1fae5;
  color: #065f46;
}

.current-status-badge.cancelado {
  background: #fee2e2;
  color: #991b1b;
}

.notification-info {
  color: #64748b;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-buttons-grid {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 1.5rem;
}

.quick-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
}

.status-action-btn {
  padding: 12px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
  color: white;
}

.status-action-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.status-action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.confirm-action {
  background: linear-gradient(135deg, #10b981, #059669);
}

.confirm-action:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669, #047857);
}

.ship-action {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.ship-action:hover:not(:disabled) {
  background: linear-gradient(135deg, #7c3aed, #6d28d9);
}

.deliver-action {
  background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.deliver-action:hover:not(:disabled) {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
}

.cancel-action {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.cancel-action:hover:not(:disabled) {
  background: linear-gradient(135deg, #dc2626, #b91c1c);
}

.status-selector {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
}

.selector-label {
  font-weight: 600;
  color: #475569;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.status-select {
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  background: white;
  color: #334155;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.status-select:focus {
  outline: none;
  border-color: #3b82f6;
}

.status-select:hover:not(:disabled) {
  border-color: #94a3b8;
}

/* ===== PIE DE TARJETA ===== */
.order-card-footer {
  padding: 1.5rem;
  border-top: 2px solid #f1f5f9;
  background: #f8fafc;
}

.order-actions {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

.action-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.details-btn {
  background: #3b82f6;
  color: white;
}

.details-btn:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
}

.print-btn {
  background: #f3f4f6;
  color: #374151;
  border: 1px solid #d1d5db;
}

.print-btn:hover {
  background: #e5e7eb;
  transform: translateY(-2px);
}

.copy-btn {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fcd34d;
}

.copy-btn:hover {
  background: #fde68a;
  transform: translateY(-2px);
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
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-weight: 600;
  color: #1e293b;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
  .filters-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .order-details-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .status-buttons-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 992px) {
  .page-main {
    padding: 1.5rem;
  }
  
  .header-content {
    flex-direction: column;
    align-items: stretch;
    gap: 1.5rem;
  }
  
  .header-stats {
    justify-content: center;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 768px) {
  .page-header {
    padding: 1rem;
  }
  
  .orders-container {
    padding: 1rem;
  }
  
  .order-card-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .order-status-section {
    text-align: left;
  }
  
  .order-total {
    justify-content: flex-start;
  }
  
  .customer-info {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .product-item {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .quick-actions {
    flex-direction: column;
  }
  
  .status-action-btn {
    width: 100%;
    justify-content: center;
  }
  
  .order-actions {
    flex-direction: column;
  }
  
  .action-btn {
    width: 100%;
    justify-content: center;
  }
  
  .pagination {
    flex-direction: column;
    gap: 1rem;
  }
}

@media (max-width: 576px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .current-status-info {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
}
</style>