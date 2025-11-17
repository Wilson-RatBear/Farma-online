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
          <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" id="password" v-model="form.password" required placeholder="••••••••">
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
      }
    }
  }
}
</script>