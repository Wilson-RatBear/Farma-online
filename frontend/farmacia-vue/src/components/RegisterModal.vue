<template>
  <div class="modal" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Crear Cuenta</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <form ref="registerForm">
          <div class="form-group">
            <label for="registerName">Nombre Completo:</label>
            <input type="text" id="registerName" v-model="form.name" required placeholder="Tu nombre completo">
          </div>
          <div class="form-group">
            <label for="registerEmail">Email:</label>
            <input type="email" id="registerEmail" v-model="form.email" required placeholder="tu@email.com">
          </div>
          
          <!-- CAMPOS NUEVOS AGREGADOS -->
          <div class="form-group">
            <label for="registerDireccion">Dirección:</label>
            <input type="text" id="registerDireccion" v-model="form.direccion" placeholder="Tu dirección completa">
          </div>
          <div class="form-group">
            <label for="registerTelefono">Teléfono:</label>
            <input type="tel" id="registerTelefono" v-model="form.telefono" placeholder="Tu número de teléfono">
          </div>
          
          <div class="form-group password-group">
            <label for="registerPassword">Contraseña:</label>
            <div class="password-input-container">
              <input 
                :type="showPassword ? 'text' : 'password'" 
                id="registerPassword" 
                v-model="form.password" 
                required 
                placeholder="Mínimo 6 caracteres"
                minlength="6"
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
          <div class="form-group password-group">
            <label for="registerConfirmPassword">Confirmar Contraseña:</label>
            <div class="password-input-container">
              <input 
                :type="showConfirmPassword ? 'text' : 'password'" 
                id="registerConfirmPassword" 
                v-model="form.confirmPassword" 
                required 
                placeholder="Repite tu contraseña"
              >
              <button 
                type="button" 
                class="password-toggle"
                @click="showConfirmPassword = !showConfirmPassword"
              >
                <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>
          
          <!-- Mensaje de error -->
          <div v-if="error" class="error-message">
            {{ error }}
          </div>
          
          <!-- Mensaje de éxito -->
          <div v-if="success" class="success-message">
            {{ success }}
          </div>
          
          <button type="button" class="btn btn-primary" :disabled="loading" @click="handleRegister">
            {{ loading ? 'Creando Cuenta...' : 'Registrarse' }}
          </button>
          
          <div class="auth-links">
            <div class="login-prompt">
              <p>¿Ya tienes cuenta?</p>
              <button type="button" class="btn-login" @click="$emit('show-login')">
                Iniciar Sesión
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
  name: 'RegisterModal',
  data() {
    return {
      form: {
        name: '',
        email: '',
        direccion: '',    
        telefono: '',     
        confirmPassword: ''
      },
      showPassword: false,
      showConfirmPassword: false,
      loading: false,
      error: '',
      success: ''
    }
  },
  methods: {
    async handleRegister() {
      // ✅ PREVENIR DOBLE ENVÍO
      if (this.loading) {
        console.log('⏳ Registro ya en proceso...');
        return;
      }
      
      // Validaciones frontend
      if (this.form.password !== this.form.confirmPassword) {
        this.error = 'Las contraseñas no coinciden';
        return;
      }
      
      if (this.form.password.length < 6) {
        this.error = 'La contraseña debe tener al menos 6 caracteres';
        return;
      }
      
      this.loading = true;
      this.error = '';
      this.success = '';
      
      try {
        // ✅ DEBUG: Ver datos que se envían
        console.log('📤 Enviando datos de registro:', {
          name: this.form.name,
          email: this.form.email,
          direccion: this.form.direccion,     
          telefono: this.form.telefono,       
          password: this.form.password,
          password_confirmation: this.form.confirmPassword
        });
        
        // ✅ USAR SERVICIO REAL DE REGISTRO
        const userData = {
          name: this.form.name,
          email: this.form.email,
          direccion: this.form.direccion,     
          telefono: this.form.telefono,       
          password: this.form.password,
          password_confirmation: this.form.confirmPassword
        };
        
        const response = await authService.register(userData);

console.log('✅ Registro exitoso:', response);

// ✅ DEBUG AGREGADO:
console.log('🔍 DEBUG - Respuesta completa del backend:', JSON.stringify(response, null, 2));
console.log('🔍 DEBUG - Tipo de respuesta:', typeof response);
console.log('🔍 DEBUG - Tiene authorization?:', !!response.authorization);
console.log('🔍 DEBUG - Tiene token?:', !!response.authorization?.token);
console.log('🔍 DEBUG - Tiene user?:', !!response.user);

// Continuar con el código normal...
        
        // Mostrar mensaje de éxito
        this.success = '¡Cuenta creada exitosamente! Redirigiendo...';
        
        // Emitir evento de registro exitoso al componente padre (App.vue)
        setTimeout(() => {
          this.$emit('register', response);
          this.$emit('close');
        }, 1500);
        
      } catch (error) {
        console.error('❌ Error en registro:', error);
        
        // Mostrar error específico del backend
        if (error.errors) {
          // Si el backend devuelve errores de validación detallados
          const firstError = Object.values(error.errors)[0][0];
          this.error = firstError;
        } else {
          this.error = error.message || 'Error al crear la cuenta. Intenta nuevamente.';
        }
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 90%;
  max-width: 450px;
  max-height: 85vh; /* ← Limitar altura máxima */
  display: flex;
  flex-direction: column;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  overflow: hidden; /* ← IMPORTANTE */
}

.modal-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0; /* ← Evitar que se encoja */
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: white;
}

.close-btn {
  background: none;
  border: none;
  color: white;
  font-size: 1.8rem;
  cursor: pointer;
  padding: 0;
  width: 30px;
  height: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: background-color 0.3s;
}

.close-btn:hover {
  background-color: rgba(255, 255, 255, 0.2);
}

.modal-body {
  padding: 1.5rem;
  flex: 1;
  overflow-y: auto; /* ← Permitir scroll si es necesario */
  display: flex;
  flex-direction: column;
}

.form-group {
  margin-bottom: 1.2rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 500;
  color: #333;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #1e88e5;
  box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
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
  color: var(--primary-color);
  background-color: rgba(30, 136, 229, 0.1);
}

.password-toggle i {
  font-size: 16px;
}

.error-message {
  background-color: #fed7d7;
  color: #c53030;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
  border: 1px solid #feb2b2;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.success-message {
  background-color: #c6f6d5;
  color: #276749;
  padding: 0.75rem;
  border-radius: 6px;
  margin-bottom: 1rem;
  border: 1px solid #9ae6b4;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.btn {
  width: 100%;
  padding: 0.875rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
  margin-top: auto; /* ← Empujar hacia abajo */
  flex-shrink: 0; /* ← Evitar que se encoja */
}

.btn-primary {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(30, 136, 229, 0.4);
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

.auth-links {
  margin-top: 1.5rem;
  text-align: center;
  flex-shrink: 0;
}

.login-prompt {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.login-prompt p {
  margin: 0;
  color: #666;
}

.btn-login {
  background: none;
  border: none;
  color: #1e88e5;
  cursor: pointer;
  font-weight: 600;
  text-decoration: underline;
  padding: 0;
}

.btn-login:hover {
  color: #0d47a1;
}

/* Scroll personalizado para el modal */
.modal-body::-webkit-scrollbar {
  width: 6px;
}

.modal-body::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>