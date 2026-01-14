<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="user-profile" @click.stop>
      <!-- Header -->
      <div class="profile-header">
        <h1>👤 Mi Perfil</h1>
        <button class="close-btn" @click="$emit('close')">×</button>
      </div>
      
      <!-- Contenido del perfil -->
      <div class="profile-content">
        <!-- Información Personal -->
        <div class="profile-section">
          <h2>📝 Información Personal</h2>
          <div class="info-grid">
            <div class="info-item">
              <label>Nombre:</label>
              <span>{{ userData.name || 'No especificado' }}</span>
            </div>
            <div class="info-item">
              <label>Email:</label>
              <span>{{ userData.email || 'No especificado' }}</span>
            </div>
            <div class="info-item">
              <label>Teléfono:</label>
              <span>{{ userData.telefono || 'No especificado' }}</span>
            </div>
            <div class="info-item">
              <label>Dirección:</label>
              <span>{{ userData.direccion || 'No especificado' }}</span>
            </div>
          </div>
        </div>

        <!-- Botón de editar -->
        <div class="profile-actions">
          <button class="edit-btn" @click="editProfile">
            ✏️ Editar Perfil
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'UserProfile',
  data() {
    return {
      userData: {}
    }
  },
  props: {
    currentUser: {
      type: Object,
      default: () => ({})
    }
  },
  watch: {
    // ✅ ESCUCHAR CAMBIOS EN currentUser Y ACTUALIZAR AUTOMÁTICAMENTE
    currentUser: {
      handler(newUser) {
        console.log('🔄 UserProfile: Usuario actualizado', newUser)
        this.loadUserData()
      },
      deep: true,
      immediate: true
    }
  },
  async mounted() {
    await this.loadUserData()
  },
  methods: {
    async loadUserData() {
      try {
        console.log('📥 Cargando datos del usuario...')
        
        // ✅ PRIORIDAD 1: Usar los datos actualizados de las props
        if (this.currentUser && Object.keys(this.currentUser).length > 0) {
          this.userData = { ...this.currentUser }
          console.log('✅ Datos cargados desde props:', this.userData)
        } 
        // ✅ PRIORIDAD 2: Fallback a $parent (compatibilidad)
        else if (this.$parent && this.$parent.currentUser) {
          this.userData = { ...this.$parent.currentUser }
          console.log('✅ Datos cargados desde $parent:', this.userData)
        }
        // ✅ PRIORIDAD 3: Fallback a localStorage
        else {
          const userData = JSON.parse(localStorage.getItem('user_data') || '{}')
          if (Object.keys(userData).length > 0) {
            this.userData = userData
            console.log('✅ Datos cargados desde localStorage:', this.userData)
          } else {
            console.log('⚠️ No se encontraron datos del usuario')
            this.userData = {}
          }
        }
        
      } catch (error) {
        console.error('❌ Error cargando datos del usuario:', error)
        this.userData = {}
      }
    },
    
    editProfile() {
      console.log('✏️ Solicitando edición de perfil')
      this.$emit('edit-profile')
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

.user-profile {
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  max-width: 600px;
  width: 95%;
  max-height: 80vh;
  display: flex;
  flex-direction: column;
  position: relative;
  margin: auto;
}

.profile-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-shrink: 0;
}

.profile-header h1 {
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

.profile-content {
  flex: 1;
  overflow-y: auto;
  padding: 1.5rem;
}

.profile-section {
  margin-bottom: 1.5rem;
}

.profile-section h2 {
  color: #2d3748;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.info-grid {
  display: grid;
  gap: 1rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.8rem;
  background: #f7fafc;
  border-radius: 6px;
}

.info-item label {
  font-weight: bold;
  color: #4a5568;
}

.info-item span {
  color: #2d3748;
}

.profile-actions {
  text-align: center;
  margin-top: 2rem;
}

.edit-btn {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  border: none;
  padding: 0.8rem 1.5rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.edit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(44, 90, 160, 0.3);
}

/* Responsive */
@media (max-width: 768px) {
  .info-item {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.3rem;
  }
  
  .user-profile {
    max-height: 90vh;
  }
}
</style>