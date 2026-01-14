<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="order-history" @click.stop>
      <div class="history-header">
        <h2>📦 Mis Pedidos</h2>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>

      <div class="history-content">
        <div v-if="loading" class="loading">
          <p>Cargando historial de pedidos...</p>
        </div>

        <div v-else-if="error" class="error-message">
          {{ error }}
        </div>

        <div v-else-if="orders.length === 0" class="empty-history">
          <p>🛒 Aún no has realizado pedidos</p>
          <p>¡Empieza a comprar en nuestra farmacia!</p>
        </div>

        <div v-else class="orders-list">
          <div class="orders-summary">
            <p>Total de pedidos: <strong>{{ orders.length }}</strong></p>
          </div>

          <div v-for="order in orders" :key="order.id" class="order-card">
            <div class="order-header">
              <div class="order-info">
                <h3>Orden #{{ order.numero_orden }}</h3>
                <span class="order-date">{{ formatDate(order.created_at) }}</span>
              </div>
              <div class="order-status" :class="getStatusClass(order.estado)">
                {{ getStatusText(order.estado) }}
              </div>
            </div>

            <div class="order-details">
              <div class="order-items">
                <div v-for="item in order.items || []" :key="item.id" class="order-item">
                  <span class="item-name">{{ item.producto?.nombre || 'Producto' }}</span>
                  <span class="item-quantity">x{{ item.cantidad }}</span>
                  <span class="item-price">${{ parseFloat(item.precio_unitario).toFixed(2) }}</span>
                </div>
              </div>

              <div class="order-total">
                <strong>Total: ${{ parseFloat(order.total).toFixed(2) }}</strong>
              </div>

              <div class="order-shipping">
                <p><strong>Envío:</strong> {{ order.direccion_envio }}, {{ order.ciudad_envio }}</p>
                <p><strong>Teléfono:</strong> {{ order.telefono_contacto }}</p>
                <p><strong>Método de pago:</strong> {{ getPaymentMethodText(order.metodo_pago) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { orderService } from '../services/orderService'
import { authService } from '../services/authService'

export default {
  name: 'OrderHistory',
  data() {
    return {
      orders: [],
      loading: false,
      error: ''
    }
  },
  async mounted() {
    // ✅ Verificar primero si hay usuario autenticado
    if (!authService.isAuthenticated()) {
      this.error = 'Por favor inicia sesión para ver tu historial de pedidos'
      this.loading = false
      return
    }
    
    await this.loadOrders()
  },
  methods: {
    async loadOrders() {
      this.loading = true
      this.error = ''
      
      try {
        console.log('📦 Cargando historial de pedidos...')
        const response = await orderService.getOrders()
        console.log('✅ Pedidos cargados:', response)
        
        this.orders = response.pedidos || response.data || response
        
        // Si no hay pedidos, no es un error
        if (this.orders.length === 0) {
          this.error = ''
        }
      } catch (error) {
        console.error('❌ Error cargando pedidos:', error)
        
        // ✅ MEJOR MANEJO DE ERRORES
        if (error.response?.status === 401) {
          this.error = 'Por favor inicia sesión para ver tu historial de pedidos'
        } else if (error.response?.status === 404 || error.message?.includes('No autenticado')) {
          this.error = 'Por favor inicia sesión para ver tu historial de pedidos'
        } else {
          this.error = 'Error temporal al cargar el historial. Intenta nuevamente.'
        }
      } finally {
        this.loading = false
      }
    },

    formatDate(dateString) {
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    },

    getStatusClass(status) {
      const statusClasses = {
        pendiente: 'status-pending',
        confirmado: 'status-confirmed',
        enviado: 'status-shipped',
        entregado: 'status-delivered',
        cancelado: 'status-cancelled'
      }
      return statusClasses[status] || 'status-pending'
    },

    getStatusText(status) {
      const statusTexts = {
        pendiente: 'Pendiente',
        confirmado: 'Confirmado',
        enviado: 'Enviado',
        entregado: 'Entregado',
        cancelado: 'Cancelado'
      }
      return statusTexts[status] || 'Pendiente'
    },

    getPaymentMethodText(method) {
      const methodTexts = {
        movil: 'Pago Móvil',
        efectivo: 'Efectivo',
        tarjeta: 'Tarjeta'
      }
      return methodTexts[method] || method
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
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.order-history {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  max-width: 800px;
  width: 90%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.history-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.history-header h2 {
  margin: 0;
  font-size: 1.3rem;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 2rem;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.history-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.loading, .empty-history {
  text-align: center;
  padding: 3rem 1rem;
  color: #718096;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  padding: 1rem;
  border-radius: 8px;
  text-align: center;
  margin: 1rem 0;
}

.orders-summary {
  background: #f7fafc;
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1.5rem;
  text-align: center;
}

.order-card {
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  margin-bottom: 1.5rem;
  overflow: hidden;
}

.order-header {
  background: #f7fafc;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.order-info h3 {
  margin: 0 0 0.25rem 0;
  color: #2d3748;
}

.order-date {
  color: #718096;
  font-size: 0.9rem;
}

.order-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.8rem;
}

.status-pending {
  background: #fffaf0;
  color: #dd6b20;
}

.status-confirmed {
  background: #f0fff4;
  color: #38a169;
}

.status-shipped {
  background: #ebf8ff;
  color: #3182ce;
}

.status-delivered {
  background: #f0fff4;
  color: #38a169;
}

.status-cancelled {
  background: #fed7d7;
  color: #e53e3e;
}

.order-details {
  padding: 1.5rem;
}

.order-items {
  margin-bottom: 1rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f7fafc;
}

.order-item:last-child {
  border-bottom: none;
}

.item-name {
  flex: 1;
}

.item-quantity {
  margin: 0 1rem;
  color: #718096;
}

.item-price {
  font-weight: 600;
  color: #2c5aa0;
}

.order-total {
  text-align: right;
  font-size: 1.1rem;
  font-weight: bold;
  padding: 1rem 0;
  border-top: 2px solid #e2e8f0;
  color: #2d3748;
}

.order-shipping {
  background: #f7fafc;
  padding: 1rem;
  border-radius: 8px;
  margin-top: 1rem;
}

.order-shipping p {
  margin: 0.25rem 0;
  color: #4a5568;
}

@media (max-width: 768px) {
  .order-history {
    width: 95%;
    margin: 1rem;
  }
  
  .order-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .order-status {
    align-self: flex-start;
  }
}
</style>