<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="reset-password-modal" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <button class="back-btn" @click="$emit('close')">
          <i class="fas fa-arrow-left"></i>
        </button>
        <h2>🔄 Nueva Contraseña</h2>
        <button class="close-btn" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>

      <!-- Contenido -->
      <div class="modal-content">
        <div v-if="!success" class="reset-form">
          <p>Crea una nueva contraseña para tu cuenta.</p>
          
          <form @submit.prevent="resetPassword">
            <!-- ELIMINADA LA LÍNEA CON ERROR -->
            
            <div class="form-group">
              <label>Email:</label>
              <input 
                type="email" 
                :value="email" 
                required 
                readonly
                class="readonly-input"
              >
            </div>

            <div class="form-group">
              <label>Nueva Contraseña:</label>
              <div class="password-input-container">
                <input 
                  :type="showPassword ? 'text' : 'password'" 
                  v-model="password" 
                  required 
                  minlength="6"
                  placeholder="Mínimo 6 caracteres"
                  :disabled="loading"
                >
                <button type="button" class="toggle-password" @click="showPassword = !showPassword">
                  <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <div class="form-group">
              <label>Confirmar Contraseña:</label>
              <div class="password-input-container">
                <input 
                  :type="showConfirmPassword ? 'text' : 'password'" 
                  v-model="passwordConfirmation" 
                  required 
                  placeholder="Repite tu contraseña"
                  :disabled="loading"
                >
                <button type="button" class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                  <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="submit-btn" :disabled="loading || !passwordsMatch">
              <span v-if="loading">Actualizando...</span>
              <span v-else>Actualizar Contraseña</span>
            </button>
          </form>
        </div>

        <div v-else class="success-message">
          <div class="success-icon">
            <i class="fas fa-check-circle"></i>
          </div>
          <h3>¡Contraseña Actualizada!</h3>
          <p>Tu contraseña ha sido restablecida correctamente.</p>
          <p>Ahora puedes iniciar sesión con tu nueva contraseña.</p>
          <button @click="$emit('close')" class="close-success-btn">
            Iniciar Sesión
          </button>
        </div>

        <!-- Mensajes de error -->
        <div v-if="error" class="error-message">
          {{ error }}
        </div>

        <div v-if="!passwordsMatch && passwordConfirmation" class="warning-message">
          Las contraseñas no coinciden
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { authService } from '../services/authService';

export default {
  name: 'ResetPasswordModal',
  props: {
    token: {
      type: String,
      required: true
    },
    email: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      password: '',
      passwordConfirmation: '',
      showPassword: false,
      showConfirmPassword: false,
      loading: false,
      success: false,
      error: ''
    };
  },
  computed: {
    passwordsMatch() {
      return this.password === this.passwordConfirmation;
    }
  },
  methods: {
    async resetPassword() {
      if (!this.passwordsMatch) {
        this.error = 'Las contraseñas no coinciden';
        return;
      }

      this.loading = true;
      this.error = '';

      try {
        const resetData = {
          email: this.email,  // Usamos la prop directamente
          token: this.token,  // Usamos la prop directamente
          password: this.password,
          password_confirmation: this.passwordConfirmation
        };

        const result = await authService.resetPassword(resetData);
        
        if (result.success) {
          this.success = true;
        } else {
          this.error = result.message || 'Error al actualizar la contraseña';
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

.reset-password-modal {
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

.password-input-container {
  position: relative;
}

.form-group input {
  width: 100%;
  padding: 12px 45px 12px 12px;
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

.readonly-input {
  background-color: #f5f5f5;
  color: #666;
}

.toggle-password {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 5px;
}

.toggle-password:hover {
  color: #1e88e5;
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

.warning-message {
  background: #fff3e0;
  color: #ef6c00;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  text-align: center;
  border-left: 4px solid #ff9800;
}
</style>