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
            <li><a href="#"><i class="fas fa-user"></i> Mi Perfil</a></li>
            <li><a href="#"><i class="fas fa-shopping-bag"></i> Mis Pedidos</a></li>
            <li><a href="#"><i class="fas fa-heart"></i> Favoritos</a></li>
            <li><a href="#"><i class="fas fa-history"></i> Historial</a></li>
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
    active: Boolean
  }
}
</script>