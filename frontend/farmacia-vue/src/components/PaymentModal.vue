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
          <div class="step-actions">
            <button class="btn btn-primary" @click="$emit('next-step')">Continuar con el Pago</button>
          </div>
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
            <div class="step-actions">
              <button type="button" class="btn btn-outline" @click="$emit('prev-step')">Atrás</button>
              <button type="submit" class="btn btn-primary">Continuar al Pago</button>
            </div>
          </form>
        </div>

        <!-- Paso 3: Método de Pago -->
        <div v-if="step === 3" class="payment-step">
          <h3>Método de Pago</h3>
          <div class="payment-methods">
            <!-- ✅ MÉTODOS DE PAGO REALES -->
            <label class="payment-method">
              <input type="radio" v-model="paymentInfo.method" value="pago_movil" required>
              <span class="checkmark"></span>
              📱 Pago Móvil
            </label>

            <label class="payment-method">
              <input type="radio" v-model="paymentInfo.method" value="efectivo" required>
              <span class="checkmark"></span>
              💰 Pago en Efectivo
            </label>
          </div>
          
          <!-- ✅ INFORMACIÓN PARA PAGO MÓVIL -->
          <div v-if="paymentInfo.method === 'pago_movil'" class="payment-details">
            <div class="form-group">
              <label for="phoneNumber">Número de Teléfono:</label>
              <input type="tel" id="phoneNumber" v-model="paymentInfo.phoneNumber" placeholder="0412-1234567" required>
            </div>
            <div class="form-group">
              <label for="bank">Banco:</label>
              <select id="bank" v-model="paymentInfo.bank" required>
                <option value="">Selecciona tu banco</option>
                <option value="BDV">Banco de Venezuela</option>
                <option value="Banesco">Banesco</option>
                <option value="Mercantil">Banco Mercantil</option>
                <option value="Provincial">Banco Provincial</option>
                <option value="Bancaribe">Bancaribe</option>
              </select>
            </div>
            <div class="form-group">
              <label for="reference">Número de Referencia:</label>
              <input type="text" id="reference" v-model="paymentInfo.reference" placeholder="Ej: 123456" required>
            </div>
            <div class="payment-instructions">
              <p><strong>Instrucciones:</strong></p>
              <ol>
                <li>Realiza el pago móvil al número registrado</li>
                <li>Ingresa el número de referencia generado</li>
                <li>Guarda el comprobante de pago</li>
                <li>Tu pedido será procesado una vez verificado el pago</li>
              </ol>
            </div>
          </div>

          <!-- ✅ INFORMACIÓN PARA TRANSFERENCIA -->
          <div v-if="paymentInfo.method === 'transferencia'" class="payment-details">
            <div class="form-group">
              <label for="bankTransfer">Banco:</label>
              <select id="bankTransfer" v-model="paymentInfo.bank" required>
                <option value="">Selecciona tu banco</option>
                <option value="BDV">Banco de Venezuela</option>
                <option value="Banesco">Banesco</option>
                <option value="Mercantil">Banco Mercantil</option>
                <option value="Provincial">Banco Provincial</option>
              </select>
            </div>
            <div class="form-group">
              <label for="referenceTransfer">Número de Referencia:</label>
              <input type="text" id="referenceTransfer" v-model="paymentInfo.reference" placeholder="Número de transferencia" required>
            </div>
            <div class="account-info">
              <p><strong>Cuenta de la Farmacia:</strong></p>
              <p>Banco: <strong>Banesco</strong></p>
              <p>Tipo: <strong>Cuenta Corriente</strong></p>
              <p>Número: <strong>0134-1234-12-1234567890</strong></p>
              <p>RIF: <strong>J-12345678-9</strong></p>
            </div>
          </div>

          <!-- ✅ INFORMACIÓN PARA ZELLE -->
          <div v-if="paymentInfo.method === 'zelle'" class="payment-details">
            <div class="form-group">
              <label for="referenceZelle">Número de Referencia:</label>
              <input type="text" id="referenceZelle" v-model="paymentInfo.reference" placeholder="Referencia de Zelle" required>
            </div>
            <div class="payment-instructions">
              <p><strong>Instrucciones:</strong></p>
              <ol>
                <li>Realiza la transferencia vía Zelle al email: <strong>farmacia@empresa.com</strong></li>
                <li>Ingresa el número de referencia de la transacción</li>
                <li>Tu pedido será procesado una vez confirmado el pago</li>
              </ol>
            </div>
          </div>

          <!-- ✅ INFORMACIÓN PARA PAYPAL -->
          <div v-if="paymentInfo.method === 'paypal'" class="payment-details">
            <div class="payment-instructions">
              <p><strong>Instrucciones:</strong></p>
              <ol>
                <li>Realiza el pago vía PayPal al email: <strong>farmacia@empresa.com</strong></li>
                <li>No es necesario ingresar referencia para PayPal</li>
                <li>Tu pedido será procesado automáticamente</li>
              </ol>
            </div>
          </div>

          <!-- ✅ INFORMACIÓN PARA EFECTIVO -->
          <div v-if="paymentInfo.method === 'efectivo'" class="payment-details">
            <div class="payment-instructions">
              <p><strong>Instrucciones:</strong></p>
              <ol>
                <li>Realizarás el pago al momento de la entrega</li>
                <li>Asegúrate de tener el monto exacto: <strong>${{ total }}</strong></li>
                <li>El pedido será preparado y coordinado para entrega</li>
              </ol>
            </div>
          </div>

          <div class="step-actions">
            <button type="button" class="btn btn-outline" @click="$emit('prev-step')">Atrás</button>
            <button 
              type="button" 
              class="btn btn-primary" 
              @click="processRealPayment"
              :disabled="processing || !canProceedToPayment"
            >
              {{ processing ? 'Procesando...' : `Pagar $${total}` }}
            </button>
          </div>
        </div>

        <!-- Paso 4: Confirmación -->
        <div v-if="step === 4" class="payment-step confirmation">
          <div class="confirmation-icon">✅</div>
          <h3>¡Pedido Confirmado!</h3>
          <p>Tu pedido ha sido procesado exitosamente.</p>
          <p><strong>Número de orden:</strong> #{{ realOrderNumber }}</p>
          <p><strong>Método de Pago:</strong> {{ getMethodName(paymentInfo.method) }}</p>
          <p v-if="paymentInfo.reference"><strong>Referencia:</strong> {{ paymentInfo.reference }}</p>
          
          <div class="next-steps">
            <h4>Próximos Pasos:</h4>
            <ul>
              <li v-if="paymentInfo.method !== 'efectivo'">Verificaremos tu pago en las próximas horas</li>
              <li>Recibirás una confirmación por email</li>
              <li>Coordinaremos la entrega contigo</li>
            </ul>
          </div>
          
          <div class="step-actions">
            <button class="btn btn-primary" @click="finishRealPayment">Continuar Comprando</button>
          </div>
        </div>

        <!-- Mensaje de error -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { orderService } from '../services/orderService'

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
        phoneNumber: '',
        bank: '',
        reference: ''
      },
      processing: false,
      error: '',
      realOrderNumber: ''
    }
  },
  computed: {
  canProceedToPayment() {
    if (!this.paymentInfo.method) return false;
    
    // ✅ SOLO VALIDACIONES PARA PAGO MÓVIL
    if (this.paymentInfo.method === 'pago_movil') {
      return this.paymentInfo.phoneNumber && this.paymentInfo.bank && this.paymentInfo.reference;
    }
    // Efectivo no requiere validaciones adicionales
    return true;
  }
},
  methods: {
    getMethodName(methodCode) {
      const names = {
        'pago_movil': 'Pago Móvil',
        'transferencia': 'Transferencia Bancaria',
        'zelle': 'Zelle',
        'paypal': 'PayPal',
        'efectivo': 'Pago en Efectivo'
      };
      return names[methodCode] || methodCode;
    },

    async processRealPayment() {
      if (!this.canProceedToPayment) {
        this.error = 'Por favor completa la información del pago';
        return;
      }

      this.processing = true;
      this.error = '';

      try {
        console.log('💰 Procesando pago real con método:', this.paymentInfo.method);
        
        // ✅ CREAR DATOS PARA PAGOS REALES
        const orderData = {
          direccion_envio: this.shippingInfo.address,
          ciudad_envio: this.shippingInfo.city,
          telefono_contacto: this.shippingInfo.phone,
          metodo_pago: this.paymentInfo.method,
          referencia_pago: this.paymentInfo.reference,
          banco: this.paymentInfo.bank,
          telefono_pago: this.paymentInfo.phoneNumber
        };

        console.log('📦 Datos enviados al backend:', orderData);
        
        // ✅ USAR EL NUEVO MÉTODO PARA PAGOS REALES
        const response = await orderService.createOrderWithPayment(orderData);
        console.log('✅ Pedido creado con pago real:', response);
        
        this.realOrderNumber = response.data.pedido?.numero_orden || response.data.numero_orden;
        
        // Emitir evento de éxito
        this.$emit('process-payment', this.paymentInfo);
        
      } catch (error) {
        console.error('❌ Error creando pedido con pago real:', error);
        this.error = error.response?.data?.message || error.response?.data?.error || 'Error al procesar el pago. Intenta nuevamente.';
      } finally {
        this.processing = false;
      }
    },

    finishRealPayment() {
      this.$emit('finish-payment');
      // Resetear el modal
      this.shippingInfo = { fullName: '', address: '', city: '', phone: '' };
      this.paymentInfo = { method: '', phoneNumber: '', bank: '', reference: '' };
      this.error = '';
      this.realOrderNumber = '';
    }
  }
}
</script>

<style scoped>
/* Tus estilos existentes se mantienen igual, solo agregamos estos nuevos: */

.payment-details {
  background: #f8f9fa;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border-left: 4px solid #2c5aa0;
}

.account-info, .payment-instructions {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border: 1px solid #e2e8f0;
}

.account-info p, .payment-instructions p {
  margin: 0.5rem 0;
  color: #4a5568;
}

.payment-instructions ol {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.payment-instructions li {
  margin: 0.25rem 0;
  color: #4a5568;
}

.next-steps {
  background: #f0fff4;
  padding: 1rem;
  border-radius: 8px;
  margin: 1rem 0;
  border-left: 4px solid #48bb78;
}

.next-steps h4 {
  margin-top: 0;
  color: #2d3748;
}

.next-steps ul {
  margin: 0.5rem 0;
  padding-left: 1.5rem;
}

.next-steps li {
  margin: 0.25rem 0;
  color: #4a5568;
}

/* Mantener todos tus estilos existentes */
.modal {
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

.modal-content {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  max-width: 500px;
  width: 90%;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}

.payment-modal {
  max-height: 85vh;
}

.modal-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-header h2 {
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

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
}

.payment-step {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.payment-step h3 {
  margin-top: 0;
  margin-bottom: 1.5rem;
  color: #2d3748;
  text-align: center;
}

.order-summary {
  flex: 1;
  margin-bottom: 1.5rem;
}

.order-items {
  margin-bottom: 1rem;
}

.order-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid #e2e8f0;
}

.order-item:last-child {
  border-bottom: none;
}

.item-name {
  flex: 1;
  font-weight: 500;
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
  text-align: center;
  font-size: 1.2rem;
  font-weight: bold;
  padding: 1rem;
  background: #f7fafc;
  border-radius: 8px;
  color: #2d3748;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #4a5568;
}

.form-group input,
.form-group select {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #2c5aa0;
}

.payment-methods {
  margin-bottom: 1.5rem;
}

.payment-method {
  display: flex;
  align-items: center;
  padding: 1rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  margin-bottom: 0.75rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.payment-method:hover {
  border-color: #cbd5e0;
}

.payment-method input {
  margin-right: 0.75rem;
}

.step-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: auto;
  padding-top: 1.5rem;
  flex-shrink: 0;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
}

.btn-primary {
  background: linear-gradient(135deg, #48bb78, #38a169);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #38a169, #2f855a);
  transform: translateY(-2px);
}

.btn-outline {
  background: white;
  color: #4a5568;
  border: 2px solid #e2e8f0;
}

.btn-outline:hover {
  border-color: #cbd5e0;
  background: #f7fafc;
}

.confirmation {
  text-align: center;
  justify-content: center;
}

.confirmation-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.confirmation h3 {
  color: #48bb78;
  margin-bottom: 1rem;
}

.confirmation p {
  margin-bottom: 0.5rem;
  color: #4a5568;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 8px;
  margin: 1rem 0;
  text-align: center;
  flex-shrink: 0;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

@media (max-width: 480px) {
  .modal-content {
    width: 95%;
    margin: 1rem;
  }
  
  .step-actions {
    flex-direction: column;
  }
  
  .btn {
    width: 100%;
  }
}
</style>