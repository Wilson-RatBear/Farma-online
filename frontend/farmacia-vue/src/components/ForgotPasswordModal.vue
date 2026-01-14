<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="forgot-password-modal" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <button class="back-btn" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i>
        </button>
        <h2>🔐 Recuperar Contraseña</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido -->
      <div class="modal-content">
        <div v-if="!emailSent" class="forgot-form">
          <p>Ingresa tu email y te enviaremos un enlace para restablecer tu contraseña.</p>
          
          <form @submit.prevent="sendResetLink">
            <div class="form-group">
              <label>Email:</label>
              <input 
                type="email" 
                v-model="email" 
                required 
                placeholder="tu@email.com"
                :disabled="loading"
              >
            </div>

            <button type="submit" class="submit-btn" :disabled="loading">
              <span v-if="loading">Enviando...</span>
              <span v-else>Enviar Enlace de Recuperación</span>
            </button>
          </form>
        </div>

        <div v-else class="success-message">
          <div class="success-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3>¡Email enviado!</h3>
          <p>Hemos enviado un enlace de recuperación a <strong>{{ email }}</strong></p>
          <p>Revisa tu bandeja de entrada y sigue las instrucciones.</p>
          <button @click="$emit('close')" class="close-success-btn">
            Entendido
          </button>
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
import { authService } from '../services/authService';

export default {
  name: 'ForgotPasswordModal',
  data() {
    return {
      email: '',
      loading: false,
      emailSent: false,
      error: ''
    };
  },
  methods: {
    async sendResetLink() {
      this.loading = true;
      this.error = '';

      try {
        const result = await authService.forgotPassword(this.email);
        
        if (result.success) {
          this.emailSent = true;
        } else {
          this.error = result.message || 'Error al enviar el email';
        }
      } catch (error) {
        this.error = error.message || 'Error de conexión';
      } finally {
        this.loading = false;
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.forgot-password-modal {
  background: white;
  border-radius: 15px;
  width: 90%;
  max-width: 450px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.modal-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-radius: 15px 15px 0 0;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.3em;
  color: white; 
}

.back-btn, .close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 8px 12px;
  border-radius: 5px;
  cursor: pointer;
  transition: background 0.3s;
}

.back-btn:hover, .close-btn:hover {
  background: rgba(255,255,255,0.3);
}

.modal-content {
  padding: 30px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: bold;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 12px;
  border: 2px solid #ddd;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus {
  border-color: #1e88e5;
  outline: none;
}

.submit-btn {
  width: 100%;
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  border: none;
  padding: 15px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: transform 0.2s;
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
}

.submit-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.success-message {
  text-align: center;
  padding: 20px 0;
}

.success-icon {
  font-size: 4em;
  color: #4CAF50;
  margin-bottom: 20px;
}

.close-success-btn {
  background: #1e88e5;
  color: white;
  border: none;
  padding: 12px 30px;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 20px;
  font-size: 16px;
}

.error-message {
  background: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 8px;
  margin-top: 15px;
  text-align: center;
}
</style>