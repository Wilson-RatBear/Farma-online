<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="edit-profile-modal" @click.stop>
      <!-- Header -->
      <div class="modal-header">
        <h1>✏️ Editar Perfil</h1>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>
      
      <!-- Formulario de edición -->
      <div class="modal-content">
        <form @submit.prevent="updateProfile" class="profile-form">
          <!-- Nombre -->
          <div class="form-group">
            <label for="name">Nombre completo:</label>
            <input
              type="text"
              id="name"
              v-model="formData.name"
              placeholder="Ingresa tu nombre completo"
              required
            >
          </div>

          <!-- Email (solo lectura) -->
          <div class="form-group">
            <label for="email">Email:</label>
            <input
              type="email"
              id="email"
              :value="currentUser.email"
              disabled
              class="disabled-input"
            >
            <small class="help-text">El email no se puede modificar</small>
          </div>

          <!-- Teléfono -->
          <div class="form-group">
            <label for="telefono">Teléfono:</label>
            <input
              type="tel"
              id="telefono"
              v-model="formData.telefono"
              placeholder="Ej: +57 300 123 4567"
            >
          </div>

          <!-- Dirección -->
          <div class="form-group">
            <label for="direccion">Dirección:</label>
            <textarea
              id="direccion"
              v-model="formData.direccion"
              placeholder="Ingresa tu dirección completa"
              rows="3"
            ></textarea>
          </div>

          <!-- Botones de acción -->
          <div class="form-actions">
            <button type="button" class="cancel-btn" @click="$emit('close')">
              Cancelar
            </button>
            <button type="submit" class="save-btn" :disabled="loading">
              {{ loading ? 'Guardando...' : '💾 Guardar Cambios' }}
            </button>
          </div>
        </form>

        <!-- Mensajes de estado -->
        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>
        
        <div v-if="error" class="error-message">
          {{ error }}
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import api from '../services/api';

export default {
  name: 'EditProfileModal',
  props: {
    currentUser: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      formData: {
        name: '',
        telefono: '',
        direccion: ''
      },
      loading: false,
      successMessage: '',
      error: ''
    }
  },
  mounted() {
    this.loadCurrentData()
  },
  methods: {
    loadCurrentData() {
      this.formData = {
        name: this.currentUser.name || '',
        telefono: this.currentUser.telefono || '',
        direccion: this.currentUser.direccion || ''
      }
    },
    
    async updateProfile() {
      this.loading = true
      this.error = ''
      this.successMessage = ''
      
      try {
        console.log('📤 Enviando datos al backend:', this.formData)
        
        // DEBUG: Verificar token
        const token = localStorage.getItem('auth_token')
        console.log('🔐 Token JWT:', token ? 'Presente' : 'FALTANTE')
        
        const response = await api.put('/user/profile', this.formData)
        console.log('✅ Respuesta completa del backend:', response)
        console.log('📊 Datos de respuesta:', response.data)
        
        if (response.data.success) {
          this.successMessage = '✅ ' + (response.data.message || 'Cambios guardados correctamente')
          console.log('🎉 ÉXITO: Perfil actualizado')
          
          // Emitir evento con los datos actualizados
          this.$emit('profile-updated', response.data.user)
          
          // Esperar 3 segundos antes de cerrar
          setTimeout(() => {
            console.log('🚪 Cerrando modal...')
            this.$emit('close')
          }, 3000)
        } else {
          this.error = '❌ ' + (response.data.message || 'Error al guardar los cambios')
        }
        
      } catch (error) {
        console.error('💥 ERROR en la petición:', error)
        console.error('📋 Detalles del error:', error.response)
        this.error = '❌ ' + (error.response?.data?.message || 'Error de conexión con el servidor')
      } finally {
        this.loading = false
      }
    }
  }
}
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 10px;
}

.edit-profile-modal {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 95%;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  position: relative;
  margin: auto;
}

.modal-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.modal-header h1 {
  margin: 0;
  font-size: 1.3rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: white;
  padding: 5px;
}

.modal-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 1.2rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: bold;
  color: #2d3748;
}

.form-group input,
.form-group textarea {
  padding: 0.8rem;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #2c5aa0;
  box-shadow: 0 0 0 3px rgba(44, 90, 160, 0.1);
}

.disabled-input {
  background-color: #f7fafc;
  color: #718096;
  cursor: not-allowed;
}

.help-text {
  color: #718096;
  font-size: 0.8rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 1.5rem;
}

.cancel-btn {
  background: #e2e8f0;
  color: #4a5568;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.save-btn {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.save-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-btn:hover {
  background: #cbd5e0;
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
}

.success-message {
  background: #c6f6d5;
  color: #22543d;
  padding: 0.8rem;
  border-radius: 6px;
  margin-top: 1rem;
  text-align: center;
}

.error-message {
  background: #fed7d7;
  color: #c53030;
  padding: 0.8rem;
  border-radius: 6px;
  margin-top: 1rem;
  text-align: center;
}

/* Responsive */
@media (max-width: 768px) {
  .form-actions {
    flex-direction: column;
  }
  
  .cancel-btn,
  .save-btn {
    width: 100%;
  }
}
</style>