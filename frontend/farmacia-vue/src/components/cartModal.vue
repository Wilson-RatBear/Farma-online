<template>
  <div class="modal-overlay" v-if="show" @click="$emit('close')">
    <div class="cart-modal" @click.stop>
      <div class="cart-header">
        <h2>Tu Carrito de Compras</h2>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>

      <div class="cart-content">
        <div v-if="items.length === 0" class="empty-cart">
          <p>🛒 Tu carrito está vacío</p>
          <p>Agrega algunos productos</p>
        </div>

        <div v-else class="cart-items">
          <div v-for="item in items" :key="item.id" class="cart-item">
            <img :src="item.image" :alt="item.name" class="item-image">
            
            <div class="item-details">
              <h4 class="item-name">{{ item.name }}</h4>
              <p class="item-price">${{ item.price }}</p>
              
              <div class="quantity-controls">
                <button 
                  class="qty-btn" 
                  @click="$emit('decrease-quantity', item)"
                  :disabled="item.quantity <= 1"
                >
                  -
                </button>
                <span class="quantity">{{ item.quantity }}</span>
                <button class="qty-btn" @click="$emit('increase-quantity', item)">
                  +
                </button>
              </div>
            </div>

            <div class="item-total">
              ${{ (item.price * item.quantity).toFixed(2) }}
            </div>

            <button 
              class="remove-btn" 
              @click="$emit('remove-from-cart', item)"
              title="Eliminar producto"
            >
              🗑️
            </button>
          </div>
        </div>
      </div>

      <div v-if="items.length > 0" class="cart-footer">
        <div class="total-section">
          <strong>Total: ${{ total }}</strong>
        </div>
        <button class="checkout-btn" @click="$emit('start-checkout')">
          Proceder al Pago
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CartModal',
  props: {
    show: Boolean,
    items: Array,
    total: String
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

.cart-modal {
  width: 90%;
  max-width: 500px;
  max-height: 80vh;
  background: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden;
}

.cart-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.cart-header h2 {
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

.cart-content {
  flex: 1;
  overflow-y: auto;
  padding: 1rem;
}

.empty-cart {
  text-align: center;
  padding: 3rem 1rem;
  color: #718096;
}

.empty-cart p {
  margin: 0.5rem 0;
}

.cart-item {
  display: flex;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e2e8f0;
  gap: 1rem;
}

.item-image {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
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

.quantity-controls {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn {
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
}

.qty-btn:hover:not(:disabled) {
  background: #cbd5e0;
}

.qty-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.quantity {
  font-weight: bold;
  min-width: 25px;
  text-align: center;
}

.item-total {
  font-weight: bold;
  color: #2c5aa0;
}

.remove-btn {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 1.1rem;
  padding: 0.5rem;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.remove-btn:hover {
  background: #fed7d7;
}

.cart-footer {
  padding: 1.5rem;
  border-top: 2px solid #e2e8f0;
  background: #f7fafc;
}

.total-section {
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
  padding: 1rem;
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

@media (max-width: 480px) {
  .cart-modal {
    width: 100%;
  }
}
</style>