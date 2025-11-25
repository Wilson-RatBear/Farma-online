<template>
  <div class="modal-overlay" @click="$router.push('/admin')">
    <div class="management-container" @click.stop>
      <!-- Header simple y centrado -->
      <div class="management-header">
        <button class="back-button" @click="$router.push('/admin')">
          <i class="fas fa-arrow-left"></i> Volver
        </button>
        <h2 class="title">
          <i class="fas fa-clipboard-list"></i> Gestión de Pedidos
        </h2>
        <button class="close-button" @click="$router.push('/')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <div class="management-content">
        <!-- Filtros (igual que antes) -->
        <div class="filters-section">
          <div class="search-container">
            <i class="fas fa-search"></i>
            <input 
              v-model="filters.search" 
              type="text" 
              placeholder="Buscar pedidos..."
              class="search-input"
            >
          </div>
          
          <select v-model="filters.estado" class="filter-select">
            <option value="">Todos los estados</option>
            <option value="pendiente">Pendientes</option>
            <option value="confirmado">Confirmados</option>
            <option value="enviado">Enviados</option>
            <option value="entregado">Entregados</option>
            <option value="cancelado">Cancelados</option>
          </select>

          <button class="refresh-btn" @click="loadOrders" :disabled="loading" title="Actualizar lista">
            <i class="fas fa-sync-alt" :class="{ 'fa-spin': loading }"></i>
          </button>
        </div>

        <!-- Contenido (igual que antes) -->
        <div v-if="loading" class="loading-state">
          <i class="fas fa-spinner fa-spin"></i>
          <p>Cargando pedidos...</p>
        </div>

        <div v-else-if="error" class="error-state">
          <i class="fas fa-exclamation-triangle"></i>
          <p>{{ error }}</p>
          <button @click="loadOrders" class="btn-retry">Reintentar</button>
        </div>

        <div v-else-if="filteredOrders.length === 0" class="empty-state">
          <i class="fas fa-box-open"></i>
          <p>No se encontraron pedidos</p>
          <button @click="resetFilters" class="btn-retry">Limpiar filtros</button>
        </div>

        <div v-else class="orders-list">
          <div 
            v-for="order in filteredOrders" 
            :key="order.id" 
            class="order-card"
            :class="'status-' + order.estado"
          >
            <!-- Mantener todo el contenido original de order-card -->
            <div class="order-header">
              <div class="order-info">
                <h3>Pedido #{{ order.numero_orden }}</h3>
                <p class="order-date">{{ formatDate(order.created_at) }}</p>
                <p class="order-user" v-if="order.usuario">
                  Cliente: {{ order.usuario.name }}
                </p>
              </div>
              <div class="order-status">
                <span class="status-badge" :class="order.estado">
                  {{ getStatusText(order.estado) }}
                </span>
                <span class="order-total">${{ order.total }}</span>
              </div>
            </div>

            <div class="order-details">
              <div class="order-items">
                <div 
                  v-for="item in order.items" 
                  :key="item.id" 
                  class="order-item"
                >
                  <span class="item-name">{{ item.producto.nombre }}</span>
                  <span class="item-quantity">x{{ item.cantidad }}</span>
                  <span class="item-price">${{ item.precio_unitario }}</span>
                </div>
              </div>
              
              <div class="order-shipping">
                <p><strong>Envío:</strong> {{ order.direccion_envio }}, {{ order.ciudad_envio }}</p>
                <p><strong>Contacto:</strong> {{ order.telefono_contacto }}</p>
                <p><strong>Método de pago:</strong> {{ order.metodo_pago }}</p>
              </div>
            </div>

            <div class="order-actions">
              <select 
                v-model="order.estado" 
                @change="updateOrderStatus(order)"
                class="status-select"
              >
                <option value="pendiente">Pendiente</option>
                <option value="confirmado">Confirmado</option>
                <option value="enviado">Enviado</option>
                <option value="entregado">Entregado</option>
                <option value="cancelado">Cancelado</option>
              </select>
              
              <button 
                class="btn-details"
                @click="viewOrderDetails(order)"
              >
                Ver Detalles
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
import { useRouter } from 'vue-router'

export default {
  name: 'OrdersManagement',
  setup() {
    const router = useRouter()
    return { router }
  },
  data() {
    return {
      pedidos: [],
      loading: false,
      error: '',
      filters: {
        search: '',
        estado: ''
      }
    }
  },
  computed: {
    filteredOrders() {
      let filtered = this.pedidos

      if (this.filters.estado) {
        filtered = filtered.filter(pedido => pedido.estado === this.filters.estado)
      }

      if (this.filters.search) {
        const searchLower = this.filters.search.toLowerCase()
        filtered = filtered.filter(pedido => 
          pedido.numero_orden.toLowerCase().includes(searchLower) ||
          pedido.usuario.name.toLowerCase().includes(searchLower) ||
          pedido.usuario.email.toLowerCase().includes(searchLower)
        )
      }

      return filtered
    }
  },
  async mounted() {
    await this.loadOrders()
  },
  methods: {
    async loadOrders() {
      this.loading = true
      this.error = ''
      
      try {
        const response = await adminService.getAllOrders()
        this.pedidos = response.pedidos.map(pedido => ({
          ...pedido,
          nuevoEstado: ''
        }))
      } catch (error) {
        console.error('Error cargando pedidos:', error)
        this.error = error.message || 'Error al cargar los pedidos'
      } finally {
        this.loading = false
      }
    },

    async updateOrderStatus(pedido) {
      if (!pedido.nuevoEstado) return

      try {
        await adminService.updateOrderStatus(pedido.id, pedido.nuevoEstado)
        pedido.estado = pedido.nuevoEstado
        pedido.nuevoEstado = ''

        this.$notify({
          title: 'Éxito',
          message: `Pedido #${pedido.numero_orden} actualizado a: ${this.getStatusText(pedido.estado)}`,
          type: 'success'
        })
      } catch (error) {
        console.error('Error actualizando estado:', error)
        this.$notify({
          title: 'Error',
          message: 'No se pudo actualizar el estado del pedido',
          type: 'error'
        })
        pedido.nuevoEstado = ''
      }
    },

    getStatusText(estado) {
      const statusMap = {
        pendiente: 'Pendiente',
        confirmado: 'Confirmado', 
        enviado: 'Enviado',
        entregado: 'Entregado',
        cancelado: 'Cancelado'
      }
      return statusMap[estado] || estado
    },

    formatDate(dateString) {
      return new Date(dateString).toLocaleDateString('es-VE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    resetFilters() {
      this.filters.search = ''
      this.filters.estado = ''
    }
  }
}
</script>

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
  padding: 8px 10px 8px 30px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 14px;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 14px;
  background: white;
  min-width: 140px;
}

.refresh-btn {
  background: #1e88e5;
  color: white;
  border: none;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

/* ESTADOS */
.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.loading-state i, .error-state i, .empty-state i {
  font-size: 2rem;
  margin-bottom: 10px;
}

.error-state i {
  color: #e74c3c;
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
}

/* LISTA DE PEDIDOS (MANTENER ESTILOS ORIGINALES) */
.orders-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.order-card {
  background: white;
  border-radius: 8px;
  padding: 15px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  border-left: 4px solid #6c757d;
}

.order-card.status-pendiente { border-left-color: #ffc107; }
.order-card.status-confirmado { border-left-color: #17a2b8; }
.order-card.status-enviado { border-left-color: #1e88e5; }
.order-card.status-entregado { border-left-color: #28a745; }
.order-card.status-cancelado { border-left-color: #dc3545; }

.order-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 12px;
}

.order-info h3 {
  margin: 0 0 5px 0;
  color: #2c3e50;
  font-size: 1.1rem;
}

.order-date, .order-user {
  margin: 2px 0;
  color: #7f8c8d;
  font-size: 0.85rem;
}

.order-status {
  text-align: right;
}

.status-badge {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.pendiente {
  background: #fff3cd;
  color: #856404;
}

.status-badge.confirmado {
  background: #d1ecf1;
  color: #0c5460;
}

.status-badge.enviado {
  background: #d1e7ff;
  color: #004085;
}

.status-badge.entregado {
  background: #d4edda;
  color: #155724;
}

.status-badge.cancelado {
  background: #f8d7da;
  color: #721c24;
}

.order-total {
  display: block;
  margin-top: 5px;
  font-size: 1.1rem;
  font-weight: 700;
  color: #27ae60;
}

.order-details {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 15px;
  margin-bottom: 12px;
}

.order-items, .order-shipping {
  background: #f8f9fa;
  padding: 12px;
  border-radius: 5px;
}

.order-item {
  display: flex;
  justify-content: space-between;
  padding: 4px 0;
  border-bottom: 1px solid #e9ecef;
  font-size: 0.85rem;
}

.order-item:last-child {
  border-bottom: none;
}

.order-shipping p {
  margin: 4px 0;
  font-size: 0.85rem;
}

.order-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.status-select {
  padding: 6px 10px;
  border: 1px solid #ddd;
  border-radius: 4px;
  background: white;
  font-size: 0.85rem;
}

.btn-details {
  background: #3498db;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.85rem;
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
  
  .order-header {
    flex-direction: column;
    gap: 8px;
  }
  
  .order-details {
    grid-template-columns: 1fr;
    gap: 10px;
  }
  
  .order-actions {
    flex-direction: column;
  }
  
  .status-select, .btn-details {
    width: 100%;
  }
}
</style>