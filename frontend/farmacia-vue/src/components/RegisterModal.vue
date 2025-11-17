<template>
  <div class="modal" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Crear Cuenta</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="$emit('register', form)">
          <div class="form-group">
            <label for="registerName">Nombre Completo:</label>
            <input type="text" id="registerName" v-model="form.name" required placeholder="Tu nombre completo">
          </div>
          <div class="form-group">
            <label for="registerEmail">Email:</label>
            <input type="email" id="registerEmail" v-model="form.email" required placeholder="tu@email.com">
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
          <button type="submit" class="btn btn-primary">Registrarse</button>
          
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
export default {
  name: 'RegisterModal',
  data() {
    return {
      form: {
        name: '',
        email: '',
        password: '',
        confirmPassword: ''
      },
      showPassword: false,
      showConfirmPassword: false
    }
  }
}
</script>

<style scoped>
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
</style>