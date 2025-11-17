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
          <div class="form-group">
            <label for="registerPassword">Contraseña:</label>
            <input type="password" id="registerPassword" v-model="form.password" required placeholder="Mínimo 6 caracteres">
          </div>
          <div class="form-group">
            <label for="registerConfirmPassword">Confirmar Contraseña:</label>
            <input type="password" id="registerConfirmPassword" v-model="form.confirmPassword" required placeholder="Repite tu contraseña">
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
      }
    }
  }
}
</script>