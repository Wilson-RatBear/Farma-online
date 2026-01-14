<template>
  <div id="admin-layout">
   

    <div class="admin-content">
      <router-view></router-view>
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
  display: flex;
  min-height: 100vh;
  background: #f8f9fa;
}

.admin-sidebar {
  width: 280px;
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

.sidebar-header {
  padding: 25px 20px;
  border-bottom: 1px solid rgba(255,255,255,0.1);
}

.sidebar-header h2 {
  margin: 0;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  gap: 10px;
}

.sidebar-nav {
  padding: 20px 0;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 15px 25px;
  color: white;
  text-decoration: none;
  transition: all 0.3s ease;
  border-left: 4px solid transparent;
}

.nav-item:hover {
  background: rgba(255,255,255,0.1);
  border-left-color: #ffeb3b;
  padding-left: 30px;
}

.nav-item.router-link-active {
  background: rgba(255,255,255,0.15);
  border-left-color: #4caf50;
}

.back-to-store {
  margin-top: 20px;
  border-top: 1px solid rgba(255,255,255,0.1);
  padding-top: 20px;
}

.admin-content {
  flex: 1;
  overflow: auto;
  background: #f8f9fa;
}
</style>