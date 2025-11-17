<template>
  <div class="modal" @click="$emit('close')">
    <div class="modal-content" @click.stop>
      <div class="modal-header">
        <h2>Iniciar Sesión</h2>
        <button class="close-btn" @click="$emit('close')">&times;</button>
      </div>
      <div class="modal-body">
        <form @submit.prevent="$emit('login', form)">
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
          <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
          
          <div class="auth-links">
            <p>¿Olvidaste tu contraseña? <a href="#" class="link">Recuperar</a></p>
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
export default {
  name: 'LoginModal',
  data() {
    return {
      form: {
        email: '',
        password: ''
      },
      showPassword: false
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