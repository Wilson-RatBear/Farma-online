<template>
  <div class="modal-overlay" @click="$router.push('/admin')">
    <div class="users-management" @click.stop>
      <!-- Header Simple -->
      <div class="simple-header">
        <button class="simple-back-btn" @click="$router.push('/admin')">← Volver</button>
        <h2>👥 Gestión de Usuarios</h2>
        <button class="simple-close-btn" @click="$router.push('/')">×</button>
      </div>

      <div class="simple-content">
        <!-- Barra de acciones simple -->
        <div class="simple-action-bar">
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Buscar usuarios..."
            class="simple-search"
            @input="filterUsers"
          >
          <div class="simple-filters">
            <select v-model="filterRole" @change="filterUsers" class="simple-filter">
              <option value="">Todos los roles</option>
              <option value="admin">Administradores</option>
              <option value="user">Usuarios</option>
            </select>
            <select v-model="filterStatus" @change="filterUsers" class="simple-filter">
              <option value="">Todos los estados</option>
              <option value="active">Activos</option>
              <option value="inactive">Inactivos</option>
            </select>
          </div>
        </div>

        <!-- Lista de usuarios simple -->
        <div class="simple-users-list">
          <div v-if="loading" class="simple-loading">
            Cargando usuarios...
          </div>

          <div v-else-if="filteredUsers.length === 0" class="simple-empty">
            No hay usuarios registrados
          </div>

          <div v-else class="simple-users-grid">
            <div 
              v-for="user in filteredUsers" 
              :key="user.id" 
              class="simple-user-card"
              :class="{ 'admin-user': user.is_admin, 'inactive-user': user.deleted_at }"
            >
              <div class="simple-user-avatar">
                <div class="avatar-circle" :class="user.is_admin ? 'admin' : 'user'">
                  {{ getUserInitials(user.name) }}
                </div>
              </div>
              
              <div class="simple-user-info">
                <h4>{{ user.name }}</h4>
                <p class="simple-email">{{ user.email }}</p>
                <div class="simple-user-meta">
                  <span class="simple-role" :class="user.is_admin ? 'admin' : 'user'">
                    {{ user.is_admin ? 'Administrador' : 'Usuario' }}
                  </span>
                  <span class="simple-register-date">
                    Registrado: {{ formatDate(user.created_at) }}
                  </span>
                  <span class="simple-orders-count">
                    Pedidos: {{ user.pedidos_count || 0 }}
                  </span>
                </div>
                <div class="simple-user-status">
                  <span v-if="user.deleted_at" class="status-badge inactive">
                    ❌ Inactivo
                  </span>
                  <span v-else class="status-badge active">
                    ✅ Activo
                  </span>
                </div>
              </div>

              <div class="simple-user-actions">
                <button 
                  v-if="!user.deleted_at"
                  class="simple-btn edit" 
                  @click="editUser(user)"
                  title="Editar usuario"
                >
                  ✏️
                </button>
                
                <button 
                  v-if="!user.deleted_at && user.id !== currentUserId"
                  class="simple-btn toggle-admin" 
                  @click="toggleAdmin(user)"
                  :title="user.is_admin ? 'Quitar admin' : 'Hacer admin'"
                >
                  {{ user.is_admin ? '👑' : '👤' }}
                </button>
                
                <button 
                  v-if="!user.deleted_at && user.id !== currentUserId"
                  class="simple-btn deactivate" 
                  @click="confirmDeactivateUser(user)"
                  title="Desactivar usuario"
                >
                  🚫
                </button>
                
                <button 
                  v-if="user.deleted_at"
                  class="simple-btn activate" 
                  @click="activateUser(user.id)"
                  title="Activar usuario"
                >
                  ✅
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal simple para editar usuario -->
      <div v-if="showEditModal" class="simple-modal-overlay inner" @click="closeEditModal">
        <div class="simple-form-modal" @click.stop>
          <div class="simple-modal-header">
            <h3>Editar Usuario</h3>
            <button class="simple-close-btn" @click="closeEditModal">×</button>
          </div>

          <form @submit.prevent="updateUser" class="simple-form">
            <div class="simple-form-group">
              <label>Nombre *</label>
              <input 
                v-model="userForm.name" 
                type="text" 
                required
                placeholder="Nombre completo"
              >
            </div>

            <div class="simple-form-group">
              <label>Email *</label>
              <input 
                v-model="userForm.email" 
                type="email" 
                required
                placeholder="correo@ejemplo.com"
              >
            </div>

            <div class="simple-form-group">
              <label>Rol</label>
              <select v-model="userForm.is_admin">
                <option :value="false">Usuario</option>
                <option :value="true">Administrador</option>
              </select>
            </div>

            <div class="simple-form-actions">
              <button type="button" class="simple-btn secondary" @click="closeEditModal">
                Cancelar
              </button>
              <button type="submit" class="simple-btn primary" :disabled="processing">
                Actualizar
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal de confirmación simple -->
      <div v-if="showConfirmModal" class="simple-modal-overlay inner" @click="showConfirmModal = false">
        <div class="simple-confirm-modal" @click.stop>
          <div class="simple-confirm-header">
            <h3>⚠️ Confirmar Acción</h3>
          </div>
          <div class="simple-confirm-body">
            <p>{{ confirmMessage }}</p>
          </div>
          <div class="simple-confirm-actions">
            <button class="simple-btn secondary" @click="showConfirmModal = false">
              Cancelar
            </button>
            <button class="simple-btn danger" @click="executeConfirmAction" :disabled="processing">
              Confirmar
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { adminService } from '../services/adminService'

export default {
  name: 'UsersManagement',
  data() {
    return {
      loading: false,
      processing: false,
      users: [],
      filteredUsers: [],
      searchQuery: '',
      filterRole: '',
      filterStatus: '',
      showEditModal: false,
      showConfirmModal: false,
      editingUser: null,
      userToAction: null,
      confirmMessage: '',
      currentAction: '',
      currentUserId: null,
      
      userForm: {
        name: '',
        email: '',
        is_admin: false
      }
    }
  },
  async mounted() {
    await this.loadUsers()
    // Obtener ID del usuario actual para no permitir acciones sobre sí mismo
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}')
    this.currentUserId = currentUser.id
  },
  methods: {
    async loadUsers() {
      this.loading = true
      try {
        const response = await adminService.getAllUsers()
        this.users = response.usuarios || response.data || response
        this.filteredUsers = [...this.users]
      } catch (error) {
        console.error('Error cargando usuarios:', error)
        alert('Error al cargar usuarios')
      } finally {
        this.loading = false
      }
    },

    filterUsers() {
      this.filteredUsers = this.users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.searchQuery.toLowerCase())
        
        const matchesRole = !this.filterRole || 
                          (this.filterRole === 'admin' && user.is_admin) ||
                          (this.filterRole === 'user' && !user.is_admin)
        
        const matchesStatus = !this.filterStatus ||
                            (this.filterStatus === 'active' && !user.deleted_at) ||
                            (this.filterStatus === 'inactive' && user.deleted_at)
        
        return matchesSearch && matchesRole && matchesStatus
      })
    },

    editUser(user) {
      this.editingUser = user
      this.userForm = { ...user }
      this.showEditModal = true
    },

    async updateUser() {
      this.processing = true
      try {
        await adminService.updateUser(this.editingUser.id, this.userForm)
        alert('Usuario actualizado correctamente')
        await this.loadUsers()
        this.closeEditModal()
      } catch (error) {
        console.error('Error actualizando usuario:', error)
        alert('Error al actualizar usuario')
      } finally {
        this.processing = false
      }
    },

    toggleAdmin(user) {
      this.userToAction = user
      this.confirmMessage = `¿${user.is_admin ? 'Quitar' : 'Otorgar'} permisos de administrador a ${user.name}?`
      this.currentAction = 'toggleAdmin'
      this.showConfirmModal = true
    },

    confirmDeactivateUser(user) {
      this.userToAction = user
      this.confirmMessage = `¿Desactivar al usuario ${user.name}? No podrá acceder al sistema.`
      this.currentAction = 'deactivate'
      this.showConfirmModal = true
    },

    async executeConfirmAction() {
      this.processing = true
      try {
        switch (this.currentAction) {
          case 'toggleAdmin':
            await adminService.updateUser(this.userToAction.id, {
              is_admin: !this.userToAction.is_admin
            })
            alert('Rol de usuario actualizado')
            break
            
          case 'deactivate':
            await adminService.deleteUser(this.userToAction.id)
            alert('Usuario desactivado correctamente')
            break
            
          case 'activate':
            await adminService.restoreUser(this.userToAction.id)
            alert('Usuario activado correctamente')
            break
        }
        
        await this.loadUsers()
        this.showConfirmModal = false
        this.userToAction = null
      } catch (error) {
        console.error('Error ejecutando acción:', error)
        alert('Error al ejecutar la acción')
      } finally {
        this.processing = false
      }
    },

    async activateUser(userId) {
      this.userToAction = { id: userId }
      this.currentAction = 'activate'
      this.confirmMessage = '¿Activar este usuario?'
      this.showConfirmModal = true
    },

    closeEditModal() {
      this.showEditModal = false
      this.editingUser = null
      this.resetUserForm()
    },

    resetUserForm() {
      this.userForm = {
        name: '',
        email: '',
        is_admin: false
      }
    },

    getUserInitials(name) {
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES')
    }
  }
}
</script>

<style scoped>
/* SOLO CAMBIÉ ESTAS 3 PROPIEDADES - TODO LO DEMÁS ES EXACTAMENTE IGUAL */

.users-management {
  background: white;
  border-radius: 8px;
  width: 85%;
  max-width: 900px;
  max-height: 85vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  z-index: 1000;
}

/* TODO LO DEMÁS SE MANTIENE EXACTAMENTE IGUAL A TU CÓDIGO ORIGINAL */

.simple-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
}

.simple-header h2 {
  margin: 0;
  font-size: 1.3rem;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.simple-back-btn {
  background: rgba(255, 255, 255, 0.2);
  border: none;
  color: white;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
}

.simple-close-btn {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: white;
  padding: 5px;
}

.simple-content {
  flex: 1;
  overflow-y: auto;
  padding: 20px;
}

.simple-action-bar {
  display: flex;
  gap: 15px;
  margin-bottom: 20px;
  align-items: center;
  flex-wrap: wrap;
}

.simple-search {
  flex: 1;
  min-width: 200px;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.simple-filters {
  display: flex;
  gap: 10px;
}

.simple-filter {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
  background: white;
}

.simple-users-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 15px;
}

.simple-user-card {
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  padding: 20px;
  display: flex;
  gap: 15px;
  align-items: flex-start;
}

.simple-user-card.admin-user {
  border-left: 4px solid #ff9800;
  background: #fff3e0;
}

.simple-user-card.inactive-user {
  border-left: 4px solid #f44336;
  background: #ffebee;
  opacity: 0.7;
}

.simple-user-avatar {
  flex-shrink: 0;
}

.avatar-circle {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: white;
  font-size: 14px;
}

.avatar-circle.admin {
  background: linear-gradient(135deg, #ff9800, #f57c00);
}

.avatar-circle.user {
  background: linear-gradient(135deg, #2196f3, #1976d2);
}

.simple-user-info {
  flex: 1;
}

.simple-user-info h4 {
  margin: 0 0 5px 0;
  font-size: 1.1rem;
}

.simple-email {
  color: #666;
  font-size: 0.9rem;
  margin: 0 0 10px 0;
}

.simple-user-meta {
  display: flex;
  flex-direction: column;
  gap: 4px;
  margin-bottom: 10px;
}

.simple-role {
  font-size: 0.8rem;
  padding: 2px 8px;
  border-radius: 12px;
  display: inline-block;
  width: fit-content;
}

.simple-role.admin {
  background: #ffe0b2;
  color: #e65100;
}

.simple-role.user {
  background: #e3f2fd;
  color: #1565c0;
}

.simple-register-date,
.simple-orders-count {
  font-size: 0.8rem;
  color: #666;
}

.simple-user-status {
  margin-top: 8px;
}

.status-badge {
  font-size: 0.8rem;
  padding: 4px 8px;
  border-radius: 4px;
  display: inline-block;
}

.status-badge.active {
  background: #e8f5e8;
  color: #2e7d32;
}

.status-badge.inactive {
  background: #ffebee;
  color: #c62828;
}

.simple-user-actions {
  display: flex;
  gap: 5px;
  flex-direction: column;
}

.simple-btn {
  padding: 6px 10px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  min-width: 40px;
}

.simple-btn.edit {
  background: #2196f3;
  color: white;
}

.simple-btn.toggle-admin {
  background: #ff9800;
  color: white;
}

.simple-btn.deactivate {
  background: #f44336;
  color: white;
}

.simple-btn.activate {
  background: #4caf50;
  color: white;
}

.simple-btn.primary {
  background: #2196f3;
  color: white;
}

.simple-btn.secondary {
  background: #e0e0e0;
  color: #333;
}

.simple-btn.danger {
  background: #f44336;
  color: white;
}

.simple-modal-overlay.inner {
  background: rgba(0, 0, 0, 0.7);
  z-index: 1001;
}

.simple-form-modal,
.simple-confirm-modal {
  background: white;
  border-radius: 8px;
  max-width: 500px;
  width: 95%;
  max-height: 80vh;
  overflow-y: auto;
}

.simple-modal-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.simple-modal-header h3 {
  margin: 0;
}

.simple-form {
  padding: 20px;
}

.simple-form-group {
  margin-bottom: 15px;
}

.simple-form-group label {
  display: block;
  margin-bottom: 5px;
  font-weight: 600;
}

.simple-form-group input,
.simple-form-group select {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.simple-form-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.simple-confirm-header {
  background: #f44336;
  color: white;
  padding: 15px 20px;
}

.simple-confirm-header h3 {
  margin: 0;
}

.simple-confirm-body {
  padding: 20px;
}

.simple-confirm-actions {
  padding: 15px 20px;
  display: flex;
  gap: 10px;
  justify-content: flex-end;
}

.simple-loading,
.simple-empty {
  text-align: center;
  padding: 40px;
  color: #666;
}

@media (max-width: 768px) {
  .users-management {
    width: 95%;
    max-height: 90vh;
  }
  
  .simple-action-bar {
    flex-direction: column;
    align-items: stretch;
  }
  
  .simple-users-grid {
    grid-template-columns: 1fr;
  }
  
  .simple-user-card {
    flex-direction: column;
    text-align: center;
  }
  
  .simple-user-actions {
    flex-direction: row;
    justify-content: center;
  }
  
  .simple-filters {
    flex-direction: column;
  }
}
</style>