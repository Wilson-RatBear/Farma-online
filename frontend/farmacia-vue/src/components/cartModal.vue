<template>
  <div class="cart-wrapper">
    <!-- Icono del carrito más pequeño -->
    <button class="cart-icon" @click="toggleCart">
      🛒
      <span v-if="totalItems > 0" class="cart-badge">{{ totalItems }}</span>
    </button>

    <!-- Panel del carrito -->
    <div class="cart-panel" :class="{ 'cart-open': carritoAbierto }">
      <div class="cart-header">
        <h3>Tu Carrito de Compras</h3>
        <button class="close-cart" @click="cerrarCarrito">×</button>
      </div>

      <div class="cart-content">
        <!-- Carrito vacío -->
        <div v-if="carrito.length === 0" class="cart-empty">
          <p> Tu carrito está vacío</p>
          <p>Agrega algunos productos</p>
        </div>

        <!-- Lista de productos en el carrito -->
        <div v-else class="cart-items">
          <div 
            v-for="item in carrito" 
            :key="item.id" 
            class="cart-item"
          >
            <div class="item-image">
              {{ item.emoji }}
            </div>
            
            <div class="item-details">
              <h4 class="item-name">{{ item.nombre }}</h4>
              <p class="item-price">${{ item.precio }}</p>
              
              <div class="item-controls">
                <button 
                  class="quantity-btn" 
                  @click="decrementarCantidad(item.id)"
                  :disabled="item.cantidad <= 1"
                >
                  -
                </button>
                <span class="quantity">{{ item.cantidad }}</span>
                <button 
                  class="quantity-btn" 
                  @click="incrementarCantidad(item.id)"
                >
                  +
                </button>
              </div>
            </div>

            <div class="item-total">
              ${{ (item.precio * item.cantidad).toFixed(2) }}
            </div>

            <button 
              class="remove-btn" 
              @click="eliminarDelCarrito(item.id)"
              title="Eliminar producto"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>

      <!-- Footer del carrito -->
      <div v-if="carrito.length > 0" class="cart-footer">
        <div class="cart-total">
          <strong>Total: ${{ totalCarrito.toFixed(2) }}</strong>
        </div>
        <button class="checkout-btn" @click="procederPago">
          Proceder al Pago
        </button>
      </div>
    </div>

    <!-- Overlay para cerrar carrito -->
    <div 
      v-if="carritoAbierto" 
      class="cart-overlay" 
      @click="cerrarCarrito"
    ></div>
  </div>
</template>

<script>
export default {
  name: 'CartComponent',
  props: {
    carrito: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      carritoAbierto: false
    }
  },
  computed: {
    totalItems() {
      return this.carrito.reduce((total, item) => total + item.cantidad, 0)
    },
    totalCarrito() {
      return this.carrito.reduce((total, item) => total + (item.precio * item.cantidad), 0)
    }
  },
  methods: {
    toggleCart() {
      this.carritoAbierto = !this.carritoAbierto
    },
    cerrarCarrito() {
      this.carritoAbierto = false
    },
    incrementarCantidad(productId) {
      this.$emit('actualizar-cantidad', { productId, type: 'increment' })
    },
    decrementarCantidad(productId) {
      this.$emit('actualizar-cantidad', { productId, type: 'decrement' })
    },
    eliminarDelCarrito(productId) {
      this.$emit('eliminar-del-carrito', productId)
    },
    procederPago() {
      if (this.carrito.length > 0) {
        alert(`¡Gracias por tu compra! Total: $${this.totalCarrito.toFixed(2)}`)
        this.$emit('vaciar-carrito')
        this.cerrarCarrito()
      }
    }
  }
}
</script>

<style scoped>
.cart-wrapper {
  position: relative;
}

.cart-icon {
  background: none;
  border: none;
  font-size: 1.4rem; /* Más pequeño */
  cursor: pointer;
  position: relative;
  padding: 0.4rem;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.cart-icon:hover {
  background-color: rgba(255, 255, 255, 0.1);
  transform: scale(1.05);
}

.cart-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background: #e53e3e;
  color: white;
  border-radius: 50%;
  width: 18px;
  height: 18px;
  font-size: 0.7rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
}

/* Panel del carrito */
.cart-panel {
  position: fixed;
  top: 0;
  right: -400px;
  width: 400px;
  height: 100vh;
  background: white;
  box-shadow: -2px 0 10px rgba(0,0,0,0.1);
  transition: right 0.3s ease;
  z-index: 1002;
  display: flex;
  flex-direction: column;
}

.cart-open {
  right: 0;
}

.cart-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-header h3 {
  margin: 0;
  font-size: 1.3rem;
}

.close-cart {
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

.cart-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.cart-empty {
  text-align: center;
  padding: 3rem 1rem;
  color: #718096;
}

.cart-empty p {
  margin: 0.5rem 0;
}

/* Items del carrito */
.cart-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  gap: 1rem;
}

.item-image {
  font-size: 1.8rem;
  width: 40px;
  text-align: center;
}

.item-details {
  flex: 1;
}

.item-name {
  margin: 0 0 0.5rem 0;
  font-size: 0.9rem;
  color: #2d3748;
}

.item-price {
  margin: 0 0 0.5rem 0;
  color: #718096;
  font-size: 0.8rem;
}

.item-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quantity-btn {
  background: #e2e8f0;
  border: none;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
  transition: all 0.3s ease;
}

.quantity-btn:hover:not(:disabled) {
  background: #cbd5e0;
}

.quantity-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  font-weight: bold;
  min-width: 25px;
  text-align: center;
  font-size: 0.9rem;
}

.item-total {
  font-weight: bold;
  color: #2c5aa0;
  font-size: 1rem;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.4rem;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.remove-btn:hover {
  background: #fed7d7;
}

/* Footer del carrito */
.cart-footer {
  padding: 1.5rem;
  border-top: 2px solid #e2e8f0;
  background: #f7fafc;
}

.cart-total {
  text-align: center;
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: #2d3748;
}

.checkout-btn {
  width: 100%;
  background: linear-gradient(135deg, #48bb78, #38a169);
  color: white;
  border: none;
  padding: 0.8rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
}

.checkout-btn:hover {
  background: linear-gradient(135deg, #38a169, #2f855a);
  transform: translateY(-2px);
}

/* Overlay */
.cart-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.5);
  z-index: 1001;
}

/* Responsive */
@media (max-width: 480px) {
  .cart-panel {
    width: 100%;
    right: -100%;
  }
  
  .cart-icon {
    font-size: 1.3rem;
  }
}
</style>