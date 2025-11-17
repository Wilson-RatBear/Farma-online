<template>
  <div class="modal" v-if="show" @click="$emit('close')">
    <div class="modal-content payment-modal" @click.stop>
      <div class="modal-header">
        <h2>Proceso de Pago</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <!-- Paso 1: Información del Pedido -->
        <div v-if="step === 1" class="payment-step">
          <h3>Resumen de tu Pedido</h3>
          <div class="order-summary">
            <div class="order-items">
              <div class="order-item" v-for="item in items" :key="item.id">
                <span class="item-name">{{ item.name }}</span>
                <span class="item-quantity">x{{ item.quantity }}</span>
                <span class="item-price">${{ (item.price * item.quantity).toFixed(2) }}</span>
              </div>
            </div>
            <div class="order-total">
              <strong>Total: ${{ total }}</strong>
            </div>
          </div>
          <button class="btn btn-primary" @click="$emit('next-step')">Continuar con el Pago</button>
        </div>

        <!-- Paso 2: Información de Envío -->
        <div v-if="step === 2" class="payment-step">
          <h3>Información de Envío</h3>
          <form @submit.prevent="$emit('go-to-payment', shippingInfo)">
            <div class="form-group">
              <label for="fullName">Nombre Completo:</label>
              <input type="text" id="fullName" v-model="shippingInfo.fullName" required>
            </div>
            <div class="form-group">
              <label for="address">Dirección:</label>
              <input type="text" id="address" v-model="shippingInfo.address" required>
            </div>
            <div class="form-group">
              <label for="city">Ciudad:</label>
              <input type="text" id="city" v-model="shippingInfo.city" required>
            </div>
            <div class="form-group">
              <label for="phone">Teléfono:</label>
              <input type="tel" id="phone" v-model="shippingInfo.phone" required>
            </div>
            <div class="form-actions">
              <button type="button" class="btn btn-outline" @click="$emit('prev-step')">Atrás</button>
              <button type="submit" class="btn btn-primary">Continuar al Pago</button>
            </div>
          </form>
        </div>

        <!-- Paso 3: Método de Pago -->
        <div v-if="step === 3" class="payment-step">
          <h3>Método de Pago</h3>
          <div class="payment-methods">
            <label class="payment-method">
              <input type="radio" v-model="paymentInfo.method" value="credit-card" required>
              <span class="checkmark"></span>
              💳 Tarjeta de Crédito/Débito
            </label>
            <label class="payment-method">
              <input type="radio" v-model="paymentInfo.method" value="cash" required>
              <span class="checkmark"></span>
              💰 Pago en Efectivo
            </label>
          </div>
          
          <div v-if="paymentInfo.method === 'credit-card'" class="card-info">
            <div class="form-group">
              <label for="cardNumber">Número de Tarjeta:</label>
              <input type="text" id="cardNumber" v-model="paymentInfo.cardNumber" placeholder="1234 5678 9012 3456">
            </div>
            <div class="form-row">
              <div class="form-group">
                <label for="expiryDate">Fecha de Expiración:</label>
                <input type="text" id="expiryDate" v-model="paymentInfo.expiryDate" placeholder="MM/AA">
              </div>
              <div class="form-group">
                <label for="cvv">CVV:</label>
                <input type="text" id="cvv" v-model="paymentInfo.cvv" placeholder="123">
              </div>
            </div>
          </div>

          <div class="form-actions">
            <button type="button" class="btn btn-outline" @click="$emit('prev-step')">Atrás</button>
            <button type="button" class="btn btn-primary" @click="$emit('process-payment', paymentInfo)">Pagar ${{ total }}</button>
          </div>
        </div>

        <!-- Paso 4: Confirmación -->
        <div v-if="step === 4" class="payment-step confirmation">
          <div class="confirmation-icon">✅</div>
          <h3>¡Pago Exitoso!</h3>
          <p>Tu pedido ha sido procesado correctamente.</p>
          <p><strong>Número de orden:</strong> #{{ orderNumber }}</p>
          <p>Recibirás un correo de confirmación shortly.</p>
          <button class="btn btn-primary" @click="$emit('finish-payment')">Continuar Comprando</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'PaymentModal',
  props: {
    show: Boolean,
    step: Number,
    items: Array,
    total: String,
    orderNumber: String
  },
  data() {
    return {
      shippingInfo: {
        fullName: '',
        address: '',
        city: '',
        phone: ''
      },
      paymentInfo: {
        method: '',
        cardNumber: '',
        expiryDate: '',
        cvv: ''
      }
    }
  }
}
</script>