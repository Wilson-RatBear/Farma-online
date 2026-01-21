<template>
  <div class="users-management-container">
    <!-- Header Mejorado -->
    <header class="page-header">
      <div class="header-container">
        <!-- Navegación y Título -->
        <div class="header-top">
          <button class="back-btn" @click="$router.push('/admin')">
            <i class="fas fa-chevron-left"></i>
          </button>
  
          <div class="header-title-section">
            <div class="title-main">
              <div class="title-content">
                <h1>Gestión de Usuarios</h1>
                <p class="title-subtitle">Administra y gestiona todos los usuarios del sistema</p>
              </div>
            </div>
            
            <div class="header-actions">
              <button class="header-action-btn export" @click="exportUsers" title="Exportar usuarios">
                <i class="icon-export"></i>
                <span>Exportar</span>
              </button>
              <button class="header-action-btn refresh" @click="loadUsers" :disabled="loading" title="Actualizar lista">
                <i class="icon-refresh"></i>
                <span>Actualizar</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Estadísticas en tiempo real -->
        <div class="header-stats-wrapper">
          <div class="stats-cards">
            <div class="stat-card" @click="resetFilters">
              <div class="stat-icon total">
                <i class="icon-users"></i>
              </div>
              <div class="stat-info">
                <h3>{{ totalUsers }}</h3>
                <p>Usuarios Totales</p>
              </div>
              <div class="stat-trend">
                <span class="trend-up" v-if="totalUsers > 0">
                  <i class="icon-trend-up"></i>
                </span>
              </div>
            </div>
            
            <div class="stat-card" @click="filterStatus = 'active'; filterUsers()">
              <div class="stat-icon active">
                <i class="icon-check-circle"></i>
              </div>
              <div class="stat-info">
                <h3>{{ activeUsers }}</h3>
                <p>Activos</p>
              </div>
              <div class="stat-badge success" v-if="activeUsers > 0">
                {{ Math.round((activeUsers / totalUsers) * 100) }}%
              </div>
            </div>
            
            <div class="stat-card" @click="filterStatus = 'inactive'; filterUsers()">
              <div class="stat-icon inactive">
                <i class="icon-x-circle"></i>
              </div>
              <div class="stat-info">
                <h3>{{ inactiveUsers }}</h3>
                <p>Inactivos</p>
              </div>
              <div class="stat-badge warning" v-if="inactiveUsers > 0">
                {{ Math.round((inactiveUsers / totalUsers) * 100) }}%
              </div>
            </div>
            
            <div class="stat-card" @click="filterRole = 'admin'; filterUsers()">
              <div class="stat-icon admin">
                <i class="icon-shield"></i>
              </div>
              <div class="stat-info">
                <h3>{{ adminCount }}</h3>
                <p>Administradores</p>
              </div>
              <div class="stat-badge premium" v-if="adminCount > 0">
                Admin
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Panel de Control Mejorado -->
    <div class="control-section">
      <div class="section-header">
        <h2><i class="icon-search"></i> Filtros y Búsqueda</h2>
        <p>Encuentra rápidamente los usuarios que necesitas</p>
      </div>
      
      <div class="filters-grid">
        <!-- Búsqueda Avanzada -->
        <div class="search-container">
          <div class="search-header">
            <label class="search-label">
              <i class="icon-search"></i> Búsqueda Avanzada
            </label>
            <div class="search-tips">
              <span class="tip">
                <i class="icon-info"></i> Puedes buscar por nombre, email o ID
              </span>
            </div>
          </div>
          <div class="search-box">
            <i class="icon-search"></i>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="Ej: 'Juan Pérez', 'correo@ejemplo.com', '#123'..."
              class="search-input"
              @input="filterUsers"
            >
            <button class="clear-search" @click="searchQuery = ''; filterUsers()" v-if="searchQuery">
              <i class="icon-x"></i>
            </button>
          </div>
          <div class="search-stats" v-if="searchQuery">
            <span>{{ filteredUsers.length }} resultados para "{{ searchQuery }}"</span>
          </div>
        </div>

        <!-- Filtros en Grid -->
        <div class="filters-container">
          <div class="filter-group">
            <div class="filter-header">
              <label class="filter-label">
                <i class="icon-tag"></i> Filtrar por Rol
              </label>
              <span class="filter-count" v-if="filterRole">
                {{ filteredByRole }} usuarios
              </span>
            </div>
            <div class="filter-options">
              <button 
                class="filter-option" 
                :class="{ active: filterRole === '' }"
                @click="filterRole = ''; filterUsers()"
              >
                <i class="icon-users"></i> Todos
              </button>
              <button 
                class="filter-option" 
                :class="{ active: filterRole === 'admin' }"
                @click="filterRole = 'admin'; filterUsers()"
              >
                <i class="icon-shield"></i> Administradores
              </button>
              <button 
                class="filter-option" 
                :class="{ active: filterRole === 'user' }"
                @click="filterRole = 'user'; filterUsers()"
              >
                <i class="icon-user"></i> Usuarios
              </button>
            </div>
          </div>

          <div class="filter-group">
            <div class="filter-header">
              <label class="filter-label">
                <i class="icon-bar-chart"></i> Filtrar por Estado
              </label>
              <span class="filter-count" v-if="filterStatus">
                {{ filteredByStatus }} usuarios
              </span>
            </div>
            <div class="filter-options">
              <button 
                class="filter-option" 
                :class="{ active: filterStatus === '' }"
                @click="filterStatus = ''; filterUsers()"
              >
                <i class="icon-layers"></i> Todos
              </button>
              <button 
                class="filter-option" 
                :class="{ active: filterStatus === 'active' }"
                @click="filterStatus = 'active'; filterUsers()"
              >
                <i class="icon-check-circle"></i> Activos
              </button>
              <button 
                class="filter-option" 
                :class="{ active: filterStatus === 'inactive' }"
                @click="filterStatus = 'inactive'; filterUsers()"
              >
                <i class="icon-x-circle"></i> Inactivos
              </button>
            </div>
          </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="actions-container">
          <div class="actions-header">
            <label class="actions-label">
              <i class="icon-zap"></i> Acciones Rápidas
            </label>
          </div>
          <div class="quick-actions-grid">
            <button class="quick-action reset" @click="resetFilters">
              <i class="icon-refresh-cw"></i>
              <span>Limpiar Filtros</span>
            </button>
            <button class="quick-action export" @click="exportUsers">
              <i class="icon-download"></i>
              <span>Exportar CSV</span>
            </button>
            <button class="quick-action refresh" @click="loadUsers" :disabled="loading">
              <i class="icon-refresh"></i>
              <span>Refrescar Datos</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección de Lista de Usuarios -->
    <div class="users-list-section">
      <div class="section-header">
        <div class="section-title">
          <h2><i class="icon-list"></i> Lista de Usuarios</h2>
          <div class="section-stats">
            <span class="stat-pill total">{{ totalUsers }} total</span>
            <span class="stat-pill active">{{ activeUsers }} activos</span>
            <span class="stat-pill showing">{{ filteredUsers.length }} mostrando</span>
          </div>
        </div>
        
        <div class="view-options">
          <button class="view-option" :class="{ active: viewMode === 'grid' }" @click="viewMode = 'grid'">
            <i class="icon-grid"></i> Grid
          </button>
          <button class="view-option" :class="{ active: viewMode === 'list' }" @click="viewMode = 'list'">
            <i class="icon-list"></i> Lista
          </button>
          <div class="sort-options">
            <select v-model="sortBy" @change="sortUsers" class="sort-select">
              <option value="name">Ordenar por: Nombre</option>
              <option value="date">Ordenar por: Fecha</option>
              <option value="role">Ordenar por: Rol</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Estados de carga -->
      <div v-if="loading" class="loading-state">
        <div class="loading-content">
          <div class="loading-spinner">
            <div class="spinner"></div>
          </div>
          <h3>Cargando usuarios...</h3>
          <p>Estamos obteniendo la información más reciente</p>
          <div class="loading-progress">
            <div class="progress-bar"></div>
          </div>
        </div>
      </div>

      <div v-else-if="error" class="error-state">
        <div class="error-content">
          <div class="error-icon">
            <i class="icon-alert-circle"></i>
          </div>
          <h3>Error al cargar usuarios</h3>
          <p>No se pudieron cargar los usuarios. Por favor verifica tu conexión.</p>
          <div class="error-actions">
            <button class="btn-secondary" @click="loadUsers">
              <i class="icon-refresh"></i> Reintentar
            </button>
            <button class="btn-primary" @click="$router.push('/admin')">
              <i class="icon-arrow-left"></i> Volver al Panel
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="filteredUsers.length === 0" class="empty-state">
        <div class="empty-content">
          <div class="empty-icon">
            <i class="icon-users"></i>
          </div>
          <h3>No se encontraron usuarios</h3>
          <p v-if="searchQuery || filterRole || filterStatus">
            No hay usuarios que coincidan con los filtros aplicados
          </p>
          <p v-else>
            Aún no hay usuarios registrados en el sistema
          </p>
          <div class="empty-actions">
            <button class="btn-outline" @click="resetFilters">
              <i class="icon-refresh-cw"></i> Limpiar Filtros
            </button>
            <button class="btn-primary" @click="loadUsers">
              <i class="icon-refresh"></i> Recargar
            </button>
          </div>
        </div>
      </div>

      <!-- Grid/Lista de Usuarios -->
      <div v-else :class="['users-display', viewMode]">
        <div 
          v-for="user in filteredUsers" 
          :key="user.id" 
          class="user-card"
          :class="{ 
            'inactive': user.deleted_at, 
            'admin-user': user.is_admin,
            'current-user': user.id === currentUserId
          }"
        >
          <!-- Avatar y Estado -->
          <div class="user-avatar-section">
            <div class="user-avatar" :class="user.is_admin ? 'admin' : 'user'">
              {{ getUserInitials(user.name) }}
              <div class="avatar-badge" v-if="user.id === currentUserId">
                <i class="icon-star"></i>
              </div>
            </div>
            <div class="user-status" :class="user.deleted_at ? 'inactive' : 'active'">
              <i :class="user.deleted_at ? 'icon-x-circle' : 'icon-check-circle'"></i>
              <span>{{ user.deleted_at ? 'Inactivo' : 'Activo' }}</span>
            </div>
          </div>

          <!-- Información Principal -->
          <div class="user-info-main">
            <div class="user-header">
              <div class="user-title">
                <h3 class="user-name">{{ user.name }}</h3>
                <div class="user-role-badge" :class="user.is_admin ? 'admin' : 'user'">
                  <i :class="user.is_admin ? 'icon-shield' : 'icon-user'"></i>
                  <span>{{ user.is_admin ? 'Administrador' : 'Usuario' }}</span>
                </div>
              </div>
              <div class="user-id">#{{ user.id }}</div>
            </div>
            
            <div class="user-details">
              <div class="detail-row">
                <div class="detail-item">
                  <i class="icon-mail"></i>
                  <span class="detail-label">Email:</span>
                  <span class="detail-value">{{ user.email }}</span>
                </div>
                <div class="detail-item">
                  <i class="icon-calendar"></i>
                  <span class="detail-label">Registro:</span>
                  <span class="detail-value">{{ formatDate(user.created_at) }}</span>
                </div>
              </div>
              
              <div class="user-metrics">
                <div class="metric">
                  <i class="icon-package"></i>
                  <div class="metric-content">
                    <span class="metric-value">{{ user.pedidos_count || 0 }}</span>
                    <span class="metric-label">Pedidos</span>
                  </div>
                </div>
                <div class="metric">
                  <i class="icon-bar-chart"></i>
                  <div class="metric-content">
                    <span class="metric-value">{{ user.last_login ? 'Reciente' : 'Nunca' }}</span>
                    <span class="metric-label">Último acceso</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Acciones -->
          <div class="user-actions">
            <div class="action-group">
              <button 
                v-if="!user.deleted_at"
                class="action-btn edit" 
                @click="editUser(user)"
                title="Editar usuario"
              >
                <i class="icon-edit"></i>
                <span>Editar</span>
              </button>
              
              <button 
                v-if="!user.deleted_at && user.id !== currentUserId"
                class="action-btn toggle-role" 
                @click="toggleAdmin(user)"
                :title="user.is_admin ? 'Quitar admin' : 'Hacer admin'"
              >
                <i :class="user.is_admin ? 'icon-user' : 'icon-shield'"></i>
                <span>{{ user.is_admin ? 'Quitar Admin' : 'Hacer Admin' }}</span>
              </button>
            </div>
            
            <div class="action-group">
              <button 
                v-if="!user.deleted_at && user.id !== currentUserId"
                class="action-btn danger" 
                @click="confirmDeactivateUser(user)"
                title="Desactivar usuario"
              >
                <i class="icon-x-circle"></i>
                <span>Desactivar</span>
              </button>
              
              <button 
                v-if="user.deleted_at"
                class="action-btn success" 
                @click="activateUser(user.id)"
                title="Activar usuario"
              >
                <i class="icon-check-circle"></i>
                <span>Activar</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Paginación -->
      <div v-if="filteredUsers.length > 0" class="pagination-section">
        <div class="pagination-info">
          Mostrando <strong>{{ filteredUsers.length }}</strong> de <strong>{{ totalUsers }}</strong> usuarios
        </div>
        
        <div class="pagination-controls">
          <button class="pagination-btn prev" :disabled="true">
            <i class="icon-chevron-left"></i> Anterior
          </button>
          
          <div class="page-numbers">
            <span class="current-page">1</span>
            <span class="total-pages">de 1</span>
          </div>
          
          <button class="pagination-btn next" :disabled="true">
            Siguiente <i class="icon-chevron-right"></i>
          </button>
        </div>
        
        <div class="items-per-page">
          <span>Mostrar:</span>
          <select class="page-select">
            <option>10</option>
            <option selected>25</option>
            <option>50</option>
            <option>100</option>
          </select>
        </div>
      </div>
    </div>
  </div>
</template>

<!-- Script permanece igual -->
<script>
import { adminService } from '../services/adminService'

export default {
  name: 'UsersManagement',
  data() {
    return {
      loading: false,
      processing: false,
      error: false,
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
      confirmTitle: '',
      confirmIcon: '⚠️',
      confirmActionText: 'Confirmar',
      actionIcon: '✅',
      currentAction: '',
      currentUserId: null,
      viewMode: 'grid',
      sortBy: 'name',
      
      userForm: {
        name: '',
        email: '',
        is_admin: false
      }
    }
  },
  computed: {
    totalUsers() {
      return this.users.length
    },
    activeUsers() {
      return this.users.filter(u => !u.deleted_at).length
    },
    inactiveUsers() {
      return this.users.filter(u => u.deleted_at).length
    },
    adminCount() {
      return this.users.filter(u => u.is_admin && !u.deleted_at).length
    },
    filteredByRole() {
      if (!this.filterRole) return 0
      return this.users.filter(user => {
        if (this.filterRole === 'admin') return user.is_admin
        if (this.filterRole === 'user') return !user.is_admin
        return true
      }).length
    },
    filteredByStatus() {
      if (!this.filterStatus) return 0
      return this.users.filter(user => {
        if (this.filterStatus === 'active') return !user.deleted_at
        if (this.filterStatus === 'inactive') return user.deleted_at
        return true
      }).length
    }
  },
  async mounted() {
    await this.loadUsers()
    const currentUser = JSON.parse(localStorage.getItem('currentUser') || '{}')
    this.currentUserId = currentUser.id
  },
  methods: {
    async loadUsers() {
      this.loading = true
      this.error = false
      try {
        const response = await adminService.getAllUsers()
        this.users = response.usuarios || response.data || response
        this.filteredUsers = [...this.users]
        this.sortUsers()
      } catch (error) {
        console.error('Error cargando usuarios:', error)
        this.error = true
      } finally {
        this.loading = false
      }
    },

    filterUsers() {
      this.filteredUsers = this.users.filter(user => {
        const matchesSearch = user.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            user.email.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            user.id.toString().includes(this.searchQuery)
        
        const matchesRole = !this.filterRole || 
                          (this.filterRole === 'admin' && user.is_admin) ||
                          (this.filterRole === 'user' && !user.is_admin)
        
        const matchesStatus = !this.filterStatus ||
                            (this.filterStatus === 'active' && !user.deleted_at) ||
                            (this.filterStatus === 'inactive' && user.deleted_at)
        
        return matchesSearch && matchesRole && matchesStatus
      })
      this.sortUsers()
    },

    sortUsers() {
      if (this.sortBy === 'name') {
        this.filteredUsers.sort((a, b) => a.name.localeCompare(b.name))
      } else if (this.sortBy === 'date') {
        this.filteredUsers.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
      } else if (this.sortBy === 'role') {
        this.filteredUsers.sort((a, b) => {
          if (a.is_admin === b.is_admin) return 0
          return a.is_admin ? -1 : 1
        })
      }
    },

    resetFilters() {
      this.searchQuery = ''
      this.filterRole = ''
      this.filterStatus = ''
      this.filterUsers()
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
        alert('✅ Usuario actualizado correctamente')
        await this.loadUsers()
        this.closeEditModal()
      } catch (error) {
        console.error('Error actualizando usuario:', error)
        alert('❌ Error al actualizar usuario')
      } finally {
        this.processing = false
      }
    },

    toggleAdmin(user) {
      this.userToAction = user
      this.confirmTitle = user.is_admin ? 'Quitar permisos de administrador' : 'Otorgar permisos de administrador'
      this.confirmMessage = `¿${user.is_admin ? 'Quitar' : 'Otorgar'} permisos de administrador a ${user.name}?`
      this.confirmActionText = user.is_admin ? 'Quitar Admin' : 'Hacer Admin'
      this.confirmIcon = user.is_admin ? '👤' : '👑'
      this.actionIcon = '🔄'
      this.currentAction = 'toggleAdmin'
      this.showConfirmModal = true
    },

    confirmDeactivateUser(user) {
      this.userToAction = user
      this.confirmTitle = 'Desactivar Usuario'
      this.confirmMessage = `¿Desactivar al usuario ${user.name}? No podrá acceder al sistema hasta que sea reactivado.`
      this.confirmActionText = 'Desactivar'
      this.confirmIcon = '🚫'
      this.actionIcon = '🚫'
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
            alert('✅ Rol de usuario actualizado')
            break
            
          case 'deactivate':
            await adminService.deleteUser(this.userToAction.id)
            alert('✅ Usuario desactivado correctamente')
            break
            
          case 'activate':
            await adminService.restoreUser(this.userToAction.id)
            alert('✅ Usuario activado correctamente')
            break
        }
        
        await this.loadUsers()
        this.showConfirmModal = false
        this.userToAction = null
      } catch (error) {
        console.error('Error ejecutando acción:', error)
        alert('❌ Error al ejecutar la acción')
      } finally {
        this.processing = false
      }
    },

    async activateUser(userId) {
      this.userToAction = this.users.find(u => u.id === userId)
      this.confirmTitle = 'Activar Usuario'
      this.confirmMessage = '¿Activar este usuario? Podrá acceder nuevamente al sistema.'
      this.confirmActionText = 'Activar'
      this.confirmIcon = '✅'
      this.actionIcon = '✅'
      this.currentAction = 'activate'
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
      if (!name) return '??'
      return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2)
    },

    formatDate(dateString) {
      if (!dateString) return 'N/A'
      const date = new Date(dateString)
      return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
      })
    },

    exportUsers() {
      const data = this.filteredUsers.map(user => ({
        ID: user.id,
        Nombre: user.name,
        Email: user.email,
        Rol: user.is_admin ? 'Administrador' : 'Usuario',
        Estado: user.deleted_at ? 'Inactivo' : 'Activo',
        'Fecha Registro': this.formatDate(user.created_at),
        'Total Pedidos': user.pedidos_count || 0
      }))
      
      const csv = this.convertToCSV(data)
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)
      link.setAttribute('href', url)
      link.setAttribute('download', `usuarios_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      
      alert('✅ Lista de usuarios exportada correctamente')
    },

    convertToCSV(data) {
      const headers = Object.keys(data[0])
      const rows = data.map(row => headers.map(header => row[header]))
      return [headers.join(','), ...rows.map(row => row.join(','))].join('\n')
    }
  }
}
</script>

<style scoped>
/* Estilos del Header Mejorado */
.page-header {
  background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
  color: white;
  padding: 0;
  box-shadow: 0 4px 25px rgba(0, 0, 0, 0.15);
  position: relative;
  overflow: hidden;
}

.page-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #059669, #3b82f6, #8b5cf6);
}

.header-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 2rem;
}

/* Header Top */
.header-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.back-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 10px 20px;
  border-radius: 10px;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
  font-weight: 500;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.back-btn i {
  font-size: 1.2rem;
}

/* Iconos modernos como pseudo-elementos o usando fuentes de iconos */
/* Puedes usar una fuente de iconos como Feather Icons o Font Awesome */
/* Para este ejemplo, usaré clases CSS con pseudo-elementos */

.icon-users::before { content: "👥"; }
.icon-check-circle::before { content: "✅"; }
.icon-x-circle::before { content: "❌"; }
.icon-shield::before { content: "🛡️"; }
.icon-export::before { content: "📤"; }
.icon-refresh::before { content: "🔄"; }
.icon-trend-up::before { content: "📈"; }
.icon-search::before { content: "🔍"; }
.icon-info::before { content: "ℹ️"; }
.icon-tag::before { content: "🏷️"; }
.icon-user::before { content: "👤"; }
.icon-bar-chart::before { content: "📊"; }
.icon-layers::before { content: "📋"; }
.icon-zap::before { content: "⚡"; }
.icon-refresh-cw::before { content: "🔄"; }
.icon-download::before { content: "⬇️"; }
.icon-list::before { content: "📝"; }
.icon-grid::before { content: "▦"; }
.icon-arrow-left::before { content: "←"; }
.icon-chevron-left::before { content: "‹"; }
.icon-chevron-right::before { content: "›"; }
.icon-star::before { content: "★"; }
.icon-mail::before { content: "✉️"; }
.icon-calendar::before { content: "📅"; }
.icon-package::before { content: "📦"; }
.icon-edit::before { content: "✏️"; }
.icon-x::before { content: "✕"; }
.icon-alert-circle::before { content: "⚠️"; }

/* Alternativamente, puedes usar una fuente de iconos real */
/* Añade en el head de tu HTML:
<link href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css" rel="stylesheet">
*/

/* Title Section */
.header-title-section {
  flex: 1;
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2rem;
}

.title-main {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.title-icon {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
  border-radius: 15px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
}

.title-content h1 {
  margin: 0;
  font-size: 2.2rem;
  font-weight: 800;
  background: linear-gradient(135deg, #fff, #e2e8f0);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.title-subtitle {
  margin: 0.5rem 0 0 0;
  color: #cbd5e1;
  font-size: 1rem;
  opacity: 0.9;
}

/* Header Actions */
.header-actions {
  display: flex;
  gap: 1rem;
}

.header-action-btn {
  padding: 12px 24px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
  white-space: nowrap;
}

.header-action-btn.export {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.header-action-btn.export:hover {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3);
}

.header-action-btn.refresh {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.header-action-btn.refresh:hover:not(:disabled) {
  background: rgba(255, 255, 255, 0.2);
  transform: translateY(-2px);
}

.header-action-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

/* Stats Wrapper */
.header-stats-wrapper {
  padding: 2rem 0;
}

.stats-cards {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1.5rem;
}

.stat-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 15px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 100%;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.1), transparent);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.stat-card:hover {
  background: rgba(255, 255, 255, 0.1);
  transform: translateY(-5px);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.stat-card:hover::before {
  opacity: 1;
}

.stat-icon {
  width: 60px;
  height: 60px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  flex-shrink: 0;
}

.stat-icon.total {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.2), rgba(30, 64, 175, 0.2));
  color: #60a5fa;
}

.stat-icon.active {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.2), rgba(5, 150, 105, 0.2));
  color: #34d399;
}

.stat-icon.inactive {
  background: linear-gradient(135deg, rgba(239, 68, 68, 0.2), rgba(185, 28, 28, 0.2));
  color: #f87171;
}

.stat-icon.admin {
  background: linear-gradient(135deg, rgba(139, 92, 246, 0.2), rgba(124, 58, 237, 0.2));
  color: #a78bfa;
}

.stat-info {
  flex: 1;
}

.stat-info h3 {
  margin: 0;
  font-size: 2rem;
  font-weight: 800;
  color: white;
}

.stat-info p {
  margin: 0.3rem 0 0 0;
  color: #cbd5e1;
  font-size: 0.9rem;
  opacity: 0.9;
}

.stat-trend {
  margin-left: auto;
}

.trend-up {
  color: #10b981;
  font-size: 1.5rem;
  font-weight: bold;
}

.stat-badge {
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 700;
}

.stat-badge.success {
  background: rgba(16, 185, 129, 0.2);
  color: #34d399;
  border: 1px solid rgba(16, 185, 129, 0.3);
}

.stat-badge.warning {
  background: rgba(245, 158, 11, 0.2);
  color: #fbbf24;
  border: 1px solid rgba(245, 158, 11, 0.3);
}

.stat-badge.premium {
  background: rgba(139, 92, 246, 0.2);
  color: #a78bfa;
  border: 1px solid rgba(139, 92, 246, 0.3);
}

/* Sección de Control */
.control-section {
  max-width: 1400px;
  margin: 2rem auto;
  padding: 0 2rem;
}

.section-header {
  margin-bottom: 2rem;
}

.section-header h2 {
  margin: 0 0 0.5rem 0;
  font-size: 1.8rem;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.section-header p {
  margin: 0;
  color: #64748b;
  font-size: 1rem;
}

/* Filters Grid */
.filters-grid {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr;
  gap: 1.5rem;
  margin-bottom: 2rem;
}

@media (max-width: 1200px) {
  .filters-grid {
    grid-template-columns: 1fr;
  }
}

/* Search Container */
.search-container {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
}

.search-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.search-label {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.search-tips .tip {
  font-size: 0.85rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 10px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  gap: 0.3rem;
}

.search-box {
  position: relative;
  margin-bottom: 0.5rem;
}

.search-box i {
  position: absolute;
  left: 18px;
  top: 50%;
  transform: translateY(-50%);
  color: #64748b;
  font-size: 1.2rem;
}

.search-input {
  width: 100%;
  padding: 16px 24px 16px 50px;
  border: 2px solid #e2e8f0;
  border-radius: 12px;
  font-size: 1rem;
  transition: all 0.3s ease;
  background: #f8fafc;
  font-weight: 500;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  background: white;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
}

.clear-search {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: #e2e8f0;
  border: none;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #64748b;
  font-size: 1.2rem;
  transition: all 0.2s ease;
}

.clear-search:hover {
  background: #cbd5e1;
  color: #475569;
}

.search-stats {
  font-size: 0.9rem;
  color: #64748b;
  padding: 8px 0;
}

/* Filters Container */
.filters-container {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.filter-group {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
}

.filter-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.filter-label {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-count {
  font-size: 0.85rem;
  color: #3b82f6;
  background: #eff6ff;
  padding: 4px 12px;
  border-radius: 12px;
  font-weight: 600;
}

.filter-options {
  display: flex;
  gap: 0.8rem;
  flex-wrap: wrap;
}

.filter-option {
  flex: 1;
  padding: 12px 16px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  color: #475569;
  min-width: 100px;
}

.filter-option:hover {
  border-color: #cbd5e1;
  transform: translateY(-2px);
}

.filter-option.active {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1d4ed8;
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.filter-option.active i {
  color: #3b82f6;
}

/* Actions Container */
.actions-container {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
}

.actions-header {
  margin-bottom: 1rem;
}

.actions-label {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.quick-actions-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.8rem;
}

.quick-action {
  padding: 14px 16px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
  font-size: 0.95rem;
}

.quick-action.reset {
  background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
  color: #475569;
}

.quick-action.reset:hover {
  background: linear-gradient(135deg, #e2e8f0, #cbd5e1);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(148, 163, 184, 0.2);
}

.quick-action.export {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.quick-action.export:hover {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.quick-action.refresh {
  background: linear-gradient(135deg, #10b981, #059669);
  color: white;
}

.quick-action.refresh:hover:not(:disabled) {
  background: linear-gradient(135deg, #059669, #047857);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.quick-action:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none !important;
}

/* Sección de Lista de Usuarios */
.users-list-section {
  max-width: 1400px;
  margin: 2rem auto 3rem;
  padding: 0 2rem;
}

.section-title {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.section-title h2 {
  margin: 0;
  font-size: 1.8rem;
  color: #1e293b;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.section-stats {
  display: flex;
  gap: 0.8rem;
}

.stat-pill {
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.stat-pill.total {
  background: #eff6ff;
  color: #1d4ed8;
  border: 1px solid #bfdbfe;
}

.stat-pill.active {
  background: #f0fdf4;
  color: #065f46;
  border: 1px solid #bbf7d0;
}

.stat-pill.showing {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fde68a;
}

.view-options {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 2rem;
}

.view-option {
  padding: 10px 20px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  color: #475569;
}

.view-option:hover {
  border-color: #cbd5e1;
}

.view-option.active {
  border-color: #3b82f6;
  background: #eff6ff;
  color: #1d4ed8;
}

.sort-options {
  margin-left: auto;
}

.sort-select {
  padding: 10px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.95rem;
  background: white;
  color: #475569;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 180px;
}

.sort-select:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Estados de carga, error y vacío mejorados */
.loading-state,
.error-state,
.empty-state {
  background: white;
  border-radius: 15px;
  padding: 4rem 2rem;
  text-align: center;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  margin: 2rem 0;
}

.loading-content,
.error-content,
.empty-content {
  max-width: 500px;
  margin: 0 auto;
}

.loading-spinner {
  margin-bottom: 2rem;
}

.spinner {
  width: 60px;
  height: 60px;
  border: 4px solid #f1f5f9;
  border-top-color: #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.loading-state h3,
.error-state h3,
.empty-state h3 {
  margin: 0 0 1rem 0;
  color: #1e293b;
  font-size: 1.5rem;
}

.loading-state p,
.error-state p,
.empty-state p {
  margin: 0 0 2rem 0;
  color: #64748b;
  font-size: 1.1rem;
}

.loading-progress {
  width: 200px;
  height: 4px;
  background: #f1f5f9;
  border-radius: 2px;
  overflow: hidden;
  margin: 2rem auto 0;
}

.progress-bar {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #8b5cf6);
  animation: progress 2s ease-in-out infinite;
}

@keyframes progress {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(200%); }
}

.error-icon,
.empty-icon {
  font-size: 4rem;
  margin-bottom: 1.5rem;
}

.error-icon i {
  color: #ef4444;
}

.empty-icon i {
  color: #94a3b8;
}

.error-actions,
.empty-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
}

.btn-secondary,
.btn-primary,
.btn-outline {
  padding: 12px 28px;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.3s ease;
  border: none;
  font-size: 0.95rem;
}

.btn-secondary {
  background: #f1f5f9;
  color: #475569;
}

.btn-secondary:hover {
  background: #e2e8f0;
  transform: translateY(-2px);
}

.btn-primary {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
}

.btn-primary:hover {
  background: linear-gradient(135deg, #2563eb, #1e40af);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.btn-outline {
  background: white;
  color: #3b82f6;
  border: 2px solid #3b82f6;
}

.btn-outline:hover {
  background: #eff6ff;
  transform: translateY(-2px);
}

/* Grid/Lista de Usuarios */
.users-display {
  margin-bottom: 3rem;
}

.users-display.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
  gap: 1.5rem;
}

.users-display.list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Tarjeta de usuario - Versión mejorada */
.user-card {
  background: white;
  border-radius: 15px;
  padding: 1.5rem;
  display: flex;
  gap: 1.5rem;
  align-items: flex-start;
  border: 2px solid #e2e8f0;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  position: relative;
}

.user-card:hover {
  border-color: #3b82f6;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
  transform: translateY(-3px);
}

.user-card.inactive {
  opacity: 0.85;
  border-color: #94a3b8;
  background: #f8fafc;
}

.user-card.admin-user {
  border-left: 4px solid #8b5cf6;
  background: linear-gradient(135deg, #ffffff, #f5f3ff);
}

.user-card.current-user {
  border-color: #f59e0b;
  background: linear-gradient(135deg, #ffffff, #fef3c7);
}

/* Avatar Section */
.user-avatar-section {
  position: relative;
  flex-shrink: 0;
}

.user-avatar {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;
  font-weight: bold;
  color: white;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  position: relative;
}

.user-avatar.admin {
  background: linear-gradient(135deg, #8b5cf6, #7c3aed);
}

.user-avatar.user {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
}

.avatar-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  width: 30px;
  height: 30px;
  border-radius: 50%;
  background: #f59e0b;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 0.8rem;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.user-status {
  position: absolute;
  bottom: -10px;
  left: 50%;
  transform: translateX(-50%);
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 4px;
  white-space: nowrap;
  border: 2px solid white;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.user-status.active {
  background: #10b981;
  color: white;
}

.user-status.inactive {
  background: #ef4444;
  color: white;
}

/* User Info Main */
.user-info-main {
  flex: 1;
  min-width: 0;
}

.user-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.user-title {
  flex: 1;
}

.user-name {
  margin: 0 0 0.5rem 0;
  font-size: 1.3rem;
  font-weight: 700;
  color: #1e293b;
  line-height: 1.3;
}

.user-role-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 6px 14px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
}

.user-role-badge.admin {
  background: #ede9fe;
  color: #5b21b6;
}

.user-role-badge.user {
  background: #e0e7ff;
  color: #3730a3;
}

.user-id {
  font-size: 0.9rem;
  color: #64748b;
  background: #f1f5f9;
  padding: 4px 10px;
  border-radius: 12px;
  font-weight: 600;
}

/* User Details */
.user-details {
  margin-bottom: 1rem;
}

.detail-row {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.detail-item i {
  color: #64748b;
  font-size: 1rem;
  width: 20px;
  text-align: center;
}

.detail-label {
  font-size: 0.85rem;
  color: #64748b;
  font-weight: 500;
  min-width: 70px;
}

.detail-value {
  font-size: 0.95rem;
  color: #1e293b;
  font-weight: 500;
  flex: 1;
}

.user-metrics {
  display: flex;
  gap: 1.5rem;
  margin-top: 1rem;
}

.metric {
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.metric i {
  font-size: 1.2rem;
  color: #3b82f6;
}

.metric-content {
  display: flex;
  flex-direction: column;
}

.metric-value {
  font-weight: 700;
  color: #1e293b;
  font-size: 1.1rem;
}

.metric-label {
  font-size: 0.8rem;
  color: #64748b;
  margin-top: 2px;
}

/* User Actions */
.user-actions {
  display: flex;
  flex-direction: column;
  gap: 0.8rem;
  min-width: 150px;
}

.action-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.action-btn {
  padding: 10px 16px;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  font-size: 0.9rem;
  display: flex;
  align-items: center;
  gap: 0.8rem;
  transition: all 0.2s ease;
  font-weight: 600;
  white-space: nowrap;
}

.action-btn i {
  font-size: 1rem;
}

.action-btn.edit {
  background: #3b82f6;
  color: white;
}

.action-btn.edit:hover {
  background: #2563eb;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.action-btn.toggle-role {
  background: #8b5cf6;
  color: white;
}

.action-btn.toggle-role:hover {
  background: #7c3aed;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.action-btn.danger {
  background: #ef4444;
  color: white;
}

.action-btn.danger:hover {
  background: #dc2626;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.action-btn.success {
  background: #10b981;
  color: white;
}

.action-btn.success:hover {
  background: #059669;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

/* Paginación */
.pagination-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  background: white;
  border-radius: 15px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #e2e8f0;
  margin-top: 2rem;
}

.pagination-info {
  color: #64748b;
  font-size: 0.95rem;
}

.pagination-info strong {
  color: #1e293b;
}

.pagination-controls {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.pagination-btn {
  padding: 10px 20px;
  border: 2px solid #e2e8f0;
  background: white;
  border-radius: 10px;
  cursor: pointer;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  color: #475569;
}

.pagination-btn:hover:not(:disabled) {
  border-color: #3b82f6;
  color: #3b82f6;
  background: #eff6ff;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-numbers {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 600;
  color: #1e293b;
}

.current-page {
  background: #3b82f6;
  color: white;
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.total-pages {
  color: #64748b;
}

.items-per-page {
  display: flex;
  align-items: center;
  gap: 0.8rem;
  color: #64748b;
}

.page-select {
  padding: 8px 16px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  color: #475569;
  cursor: pointer;
  transition: all 0.3s ease;
}

.page-select:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Responsive */
@media (max-width: 1200px) {
  .stats-cards {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .users-display.grid {
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  }
}

@media (max-width: 768px) {
  .header-container {
    padding: 0 1rem;
  }
  
  .header-top {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
  
  .header-title-section {
    flex-direction: column;
    gap: 1.5rem;
    padding: 0;
  }
  
  .title-main {
    flex-direction: column;
    text-align: center;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
  }
  
  .users-display.grid {
    grid-template-columns: 1fr;
  }
  
  .user-card {
    flex-direction: column;
    align-items: center;
    text-align: center;
  }
  
  .user-header {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .user-details {
    text-align: center;
  }
  
  .detail-item {
    justify-content: center;
  }
  
  .user-metrics {
    justify-content: center;
  }
  
  .user-actions {
    width: 100%;
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .action-group {
    flex-direction: row;
    width: 100%;
  }
  
  .action-btn {
    flex: 1;
    justify-content: center;
  }
  
  .pagination-section {
    flex-direction: column;
    gap: 1rem;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .filter-options {
    flex-direction: column;
  }
  
  .filter-option {
    width: 100%;
  }
  
  .view-options {
    flex-direction: column;
    align-items: stretch;
  }
  
  .sort-options {
    margin-left: 0;
  }
  
  .sort-select {
    width: 100%;
  }
}
</style>