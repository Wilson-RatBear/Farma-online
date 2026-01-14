<template>
  <div class="login-page">
    <div class="login-container">
      <div class="login-header">
        <h1>🏥 Farmacia Salud</h1>
        <p>Inicia sesión en tu cuenta</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label for="email">Correo electrónico</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="tu@email.com"
            required
          >
        </div>
        
        <div class="form-group">
          <label for="password">Contraseña</label>
          <input
            type="password"
            id="password"
            v-model="form.password"
            placeholder="••••••••"
            required
          >
        </div>
        
        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" v-model="form.remember">
            Recordarme
          </label>
          <a href="#" class="forgot-password">¿Olvidaste tu contraseña?</a>
        </div>
        
        <button type="submit" class="login-btn" :disabled="loading">
          {{ loading ? 'Iniciando sesión...' : 'Iniciar Sesión' }}
        </button>
      </form>
      
      <div class="login-footer">
        <p>¿No tienes cuenta? <a href="#" @click.prevent="goToRegister">Regístrate aquí</a></p>
        <button @click="goToHome" class="back-btn">← Volver al inicio</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginPage',
  data() {
    return {
      loading: false,
      form: {
        email: '',
        password: '',
        remember: false
      }
    }
  },
  methods: {
    async handleLogin() {
      this.loading = true
      
      try {
        // Simular login (reemplaza con tu API real)
        await new Promise(resolve => setTimeout(resolve, 1500))
        
        // Guardar sesión
        localStorage.setItem('isLoggedIn', 'true')
        localStorage.setItem('userEmail', this.form.email)
        
        // Redirigir al home
        this.$router.push('/')
        
      } catch (error) {
        alert('Error al iniciar sesión: ' + error.message)
      } finally {
        this.loading = false
      }
    },
    goToHome() {
      this.$router.push('/')
    },
    goToRegister() {
      // Para futura implementación de registro
      alert('Funcionalidad de registro próximamente')
    }
  },
  mounted() {
    // Si ya está logueado, redirigir al home
    if (localStorage.getItem('isLoggedIn')) {
      this.$router.push('/')
    }
  }
}
</script>

<style scoped>
.login-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-container {
  background: white;
  padding: 2.5rem;
  border-radius: 15px;
  box-shadow: 0 20px 40px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.login-header h1 {
  color: #2c5aa0;
  margin-bottom: 0.5rem;
  font-size: 1.8rem;
}

.login-header p {
  color: #718096;
  font-size: 0.9rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #2d3748;
  font-size: 0.9rem;
}

.form-group input {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus {
  outline: none;
  border-color: #2c5aa0;
}

.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  font-size: 0.9rem;
}

.remember-me {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #4a5568;
}

.forgot-password {
  color: #2c5aa0;
  text-decoration: none;
}

.forgot-password:hover {
  text-decoration: underline;
}

.login-btn {
  width: 100%;
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  border: none;
  padding: 1rem;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 15px rgba(44, 90, 160, 0.3);
}

.login-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.login-footer {
  margin-top: 2rem;
  text-align: center;
}

.login-footer p {
  color: #718096;
  margin-bottom: 1rem;
}

.login-footer a {
  color: #2c5aa0;
  text-decoration: none;
}

.login-footer a:hover {
  text-decoration: underline;
}

.back-btn {
  background: none;
  border: 1px solid #e2e8f0;
  color: #4a5568;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: #f7fafc;
}
</style>