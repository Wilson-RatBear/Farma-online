<template>
  <div class="modal" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Iniciar Sesión</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="handleLogin">
          <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" v-model="form.email" required placeholder="tu@email.com">
          </div>
          <div class="form-group password-group">
            <label for="password">Contraseña:</label>
            <div class="password-input-container">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="password" 
                v-model="form.password" 
                required 
                placeholder="••••••••"
              >
              <button 
                type="button" 
                class="password-toggle"
                @click="showPassword = !showPassword"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>
          
          <!-- Mensaje de error -->
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
          
          <button type="submit" class="btn btn-primary" :disabled="loading">
            {{ loading ? 'Iniciando Sesión...' : 'Iniciar Sesión' }}
          </button>
          
          <div class="auth-links">
            <!-- ENLACE DE RECUPERACIÓN DE CONTRASEÑA AGREGADO -->
            <p>¿Olvidaste tu contraseña? 
              <a href="#" class="link" @click.prevent="showForgotPassword">Recuperar</a>
            </p>
            <div class="register-prompt">
              <p>¿No tienes cuenta?</p>
              <button type="button" class="btn-register" @click="$emit('show-register')">
                Crear Cuenta
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { authService } from '../services/authService'

export default {
  name: 'LoginModal',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      showPassword: false,
      loading: false,
      error: ''
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true;
      this.error = '';
      
      try {
        // ✅ USAR SERVICIO REAL DE AUTENTICACIÓN
        const response = await authService.login(this.form);
        
        // ✅ CORREGIDO: Enviar TODA la respuesta, no solo el user
        this.$emit('login', response);
        
        // Cerrar modal automáticamente
        this.$emit('close');
        
      } catch (error) {
        console.error('Error en login:', error);
        this.error = error.message || 'Credenciales incorrectas. Intenta nuevamente.';
      } finally {
        this.loading = false;
      }
    },
    
    // MÉTODO NUEVO PARA RECUPERACIÓN DE CONTRASEÑA
    showForgotPassword() {
      this.$emit('show-forgot-password');
    }
  }
}
</script>

<style scoped>
.modal {
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

.modal-content {
  background: white;
  border-radius: 15px;
  width: 90%;
  max-width: 400px;
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

.close-btn {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 5px 10px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.2em;
  transition: background 0.3s;
}

.close-btn:hover {
  background: rgba(255,255,255,0.3);
}

.modal-body {
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

.password-group {
  position: relative;
}

.password-input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-container input {
  padding-right: 45px;
  width: 100%;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  color: #666;
  cursor: pointer;
  padding: 5px;
  border-radius: 4px;
  transition: color 0.3s;
}

.password-toggle:hover {
  color: #1e88e5;
  background-color: rgba(30, 136, 229, 0.1);
}

.password-toggle i {
  font-size: 16px;
}

.btn {
  width: 100%;
  padding: 15px;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.btn-register {
  background: transparent;
  color: #1e88e5;
  border: 2px solid #1e88e5;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: bold;
  transition: all 0.3s;
}

.btn-register:hover {
  background: #1e88e5;
  color: white;
}

.error-message {
  background: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 8px;
  margin: 15px 0;
  text-align: center;
  border: 1px solid #feb2b2;
}

.auth-links {
  margin-top: 20px;
  text-align: center;
}

.auth-links p {
  margin: 10px 0;
  color: #666;
}

.link {
  color: #1e88e5;
  text-decoration: none;
  font-weight: bold;
}

.link:hover {
  text-decoration: underline;
}

.register-prompt {
  margin-top: 20px;
  padding-top: 15px;
  border-top: 1px solid #eee;
}

.register-prompt p {
  margin-bottom: 10px;
  color: #666;
}
</style>