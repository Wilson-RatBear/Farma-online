<template>
  <div>
    <div class="sidebar-overlay" v-if="active" @click="$emit('close')"></div>
    <aside class="sidebar" :class="{ active: active }">
      <div class="sidebar-header">
        <h3>Menú</h3>
        <button class="close-sidebar" @click="$emit('close')">
          <i class="fas fa-times"></i>
        </button>
      </div>
      
      <div class="sidebar-content">
        <!-- Área de Usuario -->
        <div class="sidebar-section">
          <h4 class="sidebar-title">
            <i class="fas fa-user-circle"></i>
            Área de Usuario
          </h4>
          <ul class="sidebar-menu">
            <!-- NUEVO: MI PERFIL -->
            <li>
              <a href="#" class="sidebar-link" @click.prevent="showUserProfile">
                <i class="fas fa-user"></i> Mi Perfil
              </a>
            </li>
            <!-- FIN NUEVO -->
            
            <li>
              <a href="#" class="sidebar-link" @click.prevent="showOrderHistory">
                <i class="fas fa-shopping-bag"></i> Mis Pedidos
              </a>
            </li>
            
            <!-- ITEM DE FAVORITOS -->
            <li>
              <a href="#" class="sidebar-link" @click.prevent="showFavorites">
                <i class="fas fa-heart"></i> Mis Favoritos
              </a>
            </li>
            
            <li><a href="#"><i class="fas fa-history"></i> Historial</a></li>
            
            <!-- Panel Administrativo (solo para admins) -->
            <div v-if="currentUser && currentUser.is_admin" class="admin-section">
              <h4 class="sidebar-title admin-title">
                <i class="fas fa-cog"></i>
                Panel Administrativo
              </h4>
              <ul class="sidebar-menu">
                <li>
                  <a href="#" class="sidebar-link admin-link" @click.prevent="showAdminPanel">
                    <i class="fas fa-tachometer-alt"></i> Dashboard Admin
                  </a>
                </li>
                <li>
  
<li class="sidebar-item">
  <a href="#" class="sidebar-link admin-link" @click.prevent="showInventoryManagement">
    <i class="fas fa-boxes"></i>
    Gestión de Inventario
  </a>
</li>          <a href="#" class="sidebar-link admin-link" @click.prevent="showAdminChat">
                    <i class="fas fa-headset"></i> Gestión de Chat
                  </a>
                </li>
              </ul>
            </div>
          </ul>
        </div>

        <!-- Medicamentos -->
        <div class="sidebar-section">
          <h4 class="sidebar-title">
            <i class="fas fa-pills"></i>
            Medicamentos
          </h4>
          <ul class="sidebar-menu">
            <li><a href="#"><i class="fas fa-capsules"></i> Analgésicos</a></li>
            <li><a href="#"><i class="fas fa-prescription-bottle"></i> Antibióticos</a></li>
            <li><a href="#"><i class="fas fa-heartbeat"></i> Vitaminas</a></li>
            <li><a href="#"><i class="fas fa-band-aid"></i> Cuidado Personal</a></li>
          </ul>
        </div>
      </div>
    </aside>
  </div>
</template>

<script>
export default {
  name: 'Sidebar',
  props: {
    active: Boolean,
    currentUser: Object
  },
  methods: {
    // NUEVO MÉTODO: MI PERFIL
    showUserProfile() {
      console.log('👤 Sidebar: Abriendo perfil de usuario')
      this.$emit('show-user-profile')
      this.$emit('close')
    },
    
    showOrderHistory() {
      this.$emit('show-orders')
      this.$emit('close')
    },
    
    showFavorites() {
      console.log('🔘 Botón Mis Favoritos CLICKEADO en Sidebar')
      this.$emit('show-favorites')
      this.$emit('close')
    },
    
    showAdminPanel() {
      console.log('🔘 Botón Panel Admin CLICKEADO en Sidebar - Abriendo nueva pestaña')
      // Abrir en nueva pestaña
      window.open('/admin', '_blank')
      // Cerrar el sidebar
      this.$emit('close')
      console.log('✅ Nueva pestaña abierta y sidebar cerrado')
    },
    
  showInventoryManagement() {
  console.log('🟡 CLIC EN Gestión de Inventario - Emitiendo evento');
  this.$emit('show-inventory-management');
  this.$emit('close');
},

    // NUEVO MÉTODO: GESTIÓN DE CHAT
    showAdminChat() {
      console.log('📞 Sidebar: Abriendo gestión de chat')
      this.$emit('show-admin-chat')
      this.$emit('close')
    }
  }
}
</script>

<style scoped>
.sidebar-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 998;
}

.sidebar {
  position: fixed;
  top: 0;
  left: -300px;
  width: 300px;
  height: 100%;
  background: white;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
  transition: left 0.3s ease;
  z-index: 999;
  overflow-y: auto;
}

.sidebar.active {
  left: 0;
}

.sidebar-header {
  background: linear-gradient(135deg, #2c5aa0, #1e3a8a);
  color: white;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid #e2e8f0;
}

.sidebar-header h3 {
  margin: 0;
  font-size: 1.2rem;
}

.close-sidebar {
  background: none;
  border: none;
  color: white;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0.5rem;
}

.sidebar-content {
  padding: 1rem 0;
}

.sidebar-section {
  margin-bottom: 1.5rem;
}

.sidebar-title {
  padding: 0.75rem 1.5rem;
  margin: 0;
  font-size: 0.9rem;
  font-weight: 600;
  color: #4a5568;
  border-bottom: 1px solid #e2e8f0;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.sidebar-menu {
  list-style: none;
  padding: 0;
  margin: 0;
}

.sidebar-menu li {
  border-bottom: 1px solid #f7fafc;
}

.sidebar-menu a,
.sidebar-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.5rem;
  color: #4a5568;
  text-decoration: none;
  transition: all 0.3s ease;
  background: none;
  border: none;
  width: 100%;
  text-align: left;
  font-size: 0.95rem;
  cursor: pointer;
  transform: translateX(0);
}

/* ✅ ANIMACIÓN AGREGADA */
.sidebar-menu a:hover,
.sidebar-link:hover {
  background: #f7fafc;
  color: #2c5aa0;
  transform: translateX(5px);
}

/* ✅ ANIMACIÓN ESPECÍFICA PARA EL ENGRANAJE */
.sidebar-link:hover i.fa-cog {
  animation: rotateCog 0.6s ease;
}

@keyframes rotateCog {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(90deg);
  }
}

.sidebar-menu i {
  width: 20px;
  text-align: center;
}

/* ========================= */
/* ESTILOS DORADOS PARA ADMIN */
/* ========================= */

.admin-section {
  border-top: 2px solid rgba(255, 215, 0, 0.3);
  border-bottom: 2px solid rgba(255, 215, 0, 0.3);
  margin: 15px 0;
  padding: 10px 0;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.05), rgba(255, 193, 7, 0.02));
  border-radius: 8px;
}

.admin-title {
  color: #ffd700 !important;
  font-weight: 700 !important;
  border-bottom: 1px solid rgba(255, 215, 0, 0.3) !important;
}

.admin-title i {
  color: #ffd700 !important;
}

/* Items del administrador en dorado */
.admin-link {
  color: #daa520 !important; /* Dorado más elegante */
  font-weight: 600;
  position: relative;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.05), transparent) !important;
}

.admin-link i {
  color: #ffd700 !important; /* Íconos en dorado brillante */
}

/* Efectos hover para items admin */
.admin-link:hover {
  color: #ffd700 !important; /* Dorado más brillante al hover */
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.1), rgba(255, 193, 7, 0.05)) !important;
  transform: translateX(5px);
}

.admin-link:hover i {
  color: #ffed4a !important; /* Dorado más claro al hover */
  animation: pulseGold 0.6s ease;
}

/* Línea dorada a la izquierda en hover */
.admin-link::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0;
  height: 100%;
  width: 4px;
  background: linear-gradient(to bottom, #ffd700, #ffed4a);
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 0 2px 2px 0;
}

.admin-link:hover::before {
  opacity: 1;
}

/* Animación de pulso dorado para íconos admin */
@keyframes pulseGold {
  0% { 
    transform: scale(1); 
  }
  50% { 
    transform: scale(1.2); 
    color: #ffed4a !important;
  }
  100% { 
    transform: scale(1); 
  }
}

/* Animación especial para el engranaje admin */
.admin-link:hover i.fa-cog {
  animation: rotateCog 0.6s ease, pulseGold 0.6s ease;
}

@media (max-width: 480px) {
  .sidebar {
    width: 280px;
  }
  
  .admin-section {
    margin: 10px 0;
    padding: 8px 0;
  }
}
</style>