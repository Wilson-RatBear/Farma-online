<template>
  <div id="admin-layout">
    <!-- Sidebar de Navegación -->
    <div class="admin-sidebar">
      <div class="sidebar-header">
        <h2><i class="fas fa-capsules"></i> Farmacia Admin</h2>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/admin" class="nav-item">
          <i class="fas fa-tachometer-alt"></i>
          Dashboard
        </router-link>
        
        <router-link to="/admin/orders" class="nav-item">
          <i class="fas fa-shopping-bag"></i>
          Gestión de Pedidos
        </router-link>
        
        <router-link to="/admin/products" class="nav-item">
          <i class="fas fa-cubes"></i>
          Gestión de Productos
        </router-link>
        
        <router-link to="/admin/users" class="nav-item">
          <i class="fas fa-users"></i>
          Gestión de Usuarios
        </router-link>
        
        <router-link to="/admin/categories" class="nav-item">
          <i class="fas fa-tags"></i>
          Gestión de Categorías
        </router-link>
        
        <router-link to="/admin/reports" class="nav-item">
          <i class="fas fa-chart-bar"></i>
          Reportes
        </router-link>
        
        <router-link to="/" class="nav-item back-to-store">
          <i class="fas fa-store"></i>
          Volver a la Tienda
        </router-link>
      </nav>
    </div>

    <!-- Contenido Principal -->
    <div class="admin-content">
      <router-view />
    </div>
  </div>
</template>

<script>
export default {
  name: 'AdminLayout'
}
</script>

<style scoped>
#admin-layout {
  min-height: 100vh;
  background: linear-gradient(135deg, #1e88e5 0%, #0d47a1 100%);
  display: flex;
}

.admin-sidebar {
  width: 280px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px 0;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
}

.sidebar-header {
  padding: 0 20px 20px 20px;
  border-bottom: 1px solid #e0e0e0;
  margin-bottom: 20px;
}

.sidebar-header h2 {
  color: #1e88e5;
  margin: 0;
  font-size: 1.4rem;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 5px;
  padding: 0 15px;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 15px;
  color: #333;
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.3s ease;
  font-weight: 500;
  cursor: pointer;
}

.nav-item:hover {
  background: #e3f2fd;
  color: #1e88e5;
  transform: translateX(5px);
}

.nav-item.router-link-active {
  background: #1e88e5;
  color: white;
}

.nav-item i {
  width: 20px;
  text-align: center;
}

.back-to-store {
  margin-top: 20px;
  border-top: 1px solid #e0e0e0;
  padding-top: 20px;
  background: #f5f5f5;
}

.back-to-store:hover {
  background: #e0e0e0;
}

.admin-content {
  flex: 1;
  padding: 20px;
  overflow-y: auto;
}

/* Responsive */
@media (max-width: 768px) {
  .admin-sidebar {
    width: 250px;
  }
  
  .admin-content {
    padding: 15px;
  }
}

@media (max-width: 480px) {
  #admin-layout {
    flex-direction: column;
  }
  
  .admin-sidebar {
    width: 100%;
    padding: 10px 0;
  }
  
  .sidebar-nav {
    flex-direction: row;
    overflow-x: auto;
    padding: 0 10px;
  }
  
  .nav-item {
    flex-shrink: 0;
    white-space: nowrap;
  }
  
  .nav-item span {
    display: none;
  }
  
  .back-to-store {
    display: none;
  }
}
</style>