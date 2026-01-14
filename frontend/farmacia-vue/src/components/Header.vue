<template>
  <header class="header">
    <div class="container">
      <div class="header-content">
        <!-- Logo y Menú Hamburguesa -->
        <div class="header-left">
          <div class="menu-toggle" @click="$emit('toggle-menu')">
            <span></span>
            <span></span>
            <span></span>
          </div>
          <div class="logo" @click="goToHome">
            <i class="fas fa-plus-square"></i>
            Farmacia Salud
          </div>
        </div>

        <!-- Barra de Búsqueda -->
        <div class="search-container">
          <div class="search-bar">
            <i class="fas fa-search"></i>
            <input 
              type="text" 
              placeholder="Buscar medicamentos..." 
              v-model="searchQuery"
              @input="$emit('search', searchQuery)"
            >
          </div>
        </div>

        <!-- Navegación Derecha -->
        <div class="header-right">
          <div class="filters-section">
            <span class="filters-label">Filtros</span>
            <select class="filter-select" v-model="selectedCategory" @change="$emit('filter-category', selectedCategory)">
              <option value="">Todas las categorías</option>
              <option value="aseo-personal">Aseo Personal</option>
              <option value="medicamentos">Medicamentos</option>
              <option value="cuidado-bebes">Cuidado Bebés</option>
              <option value="cuidado-piel">Cuidado Piel</option>
              <option value="vitaminas">Vitaminas</option>
              <option value="primeros-auxilios">Primeros Auxilios</option>
              <option value="bebidas-alimentos">Bebidas y Alimentos</option>
            </select>
          </div>

          <nav class="main-nav">
            <a href="#" class="nav-link">Inicio</a>
          </nav>

          <div class="cart-icon" @click="$emit('toggle-cart')">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count">{{ cartCount }}</span>
          </div>

          <!-- Solo botón de Iniciar Sesión -->
          <div class="auth-section" v-if="!currentUser">
            <button class="login-btn" @click="$emit('show-login')">
              Iniciar Sesión
            </button>
          </div>

          <!-- Usuario logueado -->
          <div class="user-menu" v-else>
            <div class="user-info">
              <i class="fas fa-user-circle"></i>
              <span class="user-name"><i class="fas fa-user"></i></span>
              <div class="user-dropdown">
                <!-- NUEVO: MI PERFIL FUNCIONAL -->
                <a href="#" class="dropdown-item" @click.prevent="showUserProfile">
                  <i class="fas fa-user"></i> Mi Perfil
                </a>
                <!-- FIN NUEVO -->
                
                <a href="#" class="dropdown-item" @click.prevent="showOrderHistory">
                  <i class="fas fa-shopping-bag"></i> Mis Pedidos
                </a>
                <a href="#" class="dropdown-item"><i class="fas fa-heart"></i> Favoritos</a>
                <a href="#" class="dropdown-item" @click="$emit('logout')"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  name: 'Header',
  props: {
    cartCount: Number,
    currentUser: Object
  },
  data() {
    return {
      searchQuery: '',
      selectedCategory: ''
    }
  },
  methods: {
    getFirstName(fullName) {
      return fullName.split(' ')[0]; // Toma solo el primer nombre
    },
    
    showOrderHistory() {
      this.$emit('show-orders');
    },
    
    // NUEVO MÉTODO: MI PERFIL
    showUserProfile() {
      console.log('👤 Header: Abriendo perfil de usuario');
      this.$emit('show-user-profile');
    },
    //volvera al inicio 
    goToHome() {
  console.log('🏠 Navegando a la página de inicio')
  
  // Redirigir a la página de inicio en la misma pestaña
  window.location.href = '/'
  
  console.log('✅ Redirigiendo a página de inicio')
}
  }
}
</script>