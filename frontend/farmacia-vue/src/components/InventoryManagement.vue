<template>
  <div class="modal-overlay" @click="$emit('close')">
    <div class="orders-management" @click.stop>
      <!-- Header con diseño unificado -->
      <div class="modal-header">
        <button class="btn-back" @click="$emit('close')">
          ← Volver
        </button>
        <h2 class="modal-title">
          <i class="fas fa-boxes"></i>
          Gestión de Inventario
          <span class="badge-count">{{ stats.total_productos || 0 }}</span>
        </h2>
        <button class="btn-close" @click="$emit('close')">
          ×
        </button>
      </div>

      <!-- Contenido principal -->
      <div class="orders-content">
        <!-- Estadísticas rápidas -->
        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon danger">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.productos_stock_bajo || 0 }}</h3>
              <p>Stock Bajo</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon warning">
              <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.productos_sin_stock || 0 }}</h3>
              <p>Sin Stock</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon success">
              <i class="fas fa-warehouse"></i>
            </div>
            <div class="stat-info">
              <h3>${{ (stats.valor_inventario_total || 0).toLocaleString() }}</h3>
              <p>Valor Inventario</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon info">
              <i class="fas fa-truck"></i>
            </div>
            <div class="stat-info">
              <h3>{{ stats.proveedores_activos || 0 }}</h3>
              <p>Proveedores Activos</p>
            </div>
          </div>
        </div>

        <!-- Pestañas -->
        <div class="tabs-container">
          <div class="tabs-header">
            <button 
              class="tab-btn" 
              :class="{ active: activeTab === 'stock' }"
              @click="activeTab = 'stock'"
            >
              <i class="fas fa-exclamation-triangle"></i>
              Stock Bajo
            </button>
            <button 
              class="tab-btn" 
              :class="{ active: activeTab === 'movimientos' }"
              @click="activeTab = 'movimientos'"
            >
              <i class="fas fa-history"></i>
              Historial
            </button>
            <button 
              class="tab-btn" 
              :class="{ active: activeTab === 'proveedores' }"
              @click="activeTab = 'proveedores'"
            >
              <i class="fas fa-truck"></i>
              Proveedores
            </button>
            <button 
              class="tab-btn" 
              :class="{ active: activeTab === 'ajustes' }"
              @click="activeTab = 'ajustes'"
            >
              <i class="fas fa-edit"></i>
              Ajustar Stock
            </button>
          </div>

          <!-- Contenido de pestañas -->
          <div class="tab-content">
            <!-- Pestaña: Stock Bajo -->
            <div v-if="activeTab === 'stock'" class="tab-pane">
              <div class="section-header">
                <h3>Productos con Stock Bajo</h3>
                <span class="badge">{{ productosStockBajo.length }}</span>
              </div>
              
              <div v-if="loading" class="loading">Cargando productos...</div>
              
              <div v-else-if="productosStockBajo.length === 0" class="empty-state">
                <i class="fas fa-check-circle"></i>
                <p>¡Excelente! No hay productos con stock bajo.</p>
              </div>

              <div v-else class="products-grid">
                <div 
                  v-for="producto in productosStockBajo" 
                  :key="producto.id"
                  class="product-card stock-low"
                >
                  <div class="product-info">
                    <h4>{{ producto.nombre }}</h4>
                    <p class="product-category">{{ producto.categoria?.nombre }}</p>
                    <div class="stock-info">
                      <span class="stock-current">{{ producto.stock }} unidades</span>
                      <span class="stock-min">Mínimo: {{ producto.stock_minimo }}</span>
                    </div>
                    <div class="supplier-info" v-if="producto.proveedor">
                      <small>Proveedor: {{ producto.proveedor.nombre }}</small>
                    </div>
                  </div>
                  <div class="product-actions">
                    <button 
                      class="btn-primary btn-sm"
                      @click="abrirModalAjuste(producto)"
                    >
                      <i class="fas fa-edit"></i>
                      Reponer
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pestaña: Historial de Movimientos -->
            <div v-if="activeTab === 'movimientos'" class="tab-pane">
              <div class="section-header">
                <h3>Historial de Movimientos</h3>
                <div class="filters">
                  <select v-model="filtrosMovimientos.tipo" class="filter-select">
                    <option value="">Todos los tipos</option>
                    <option value="entrada">Entradas</option>
                    <option value="salida">Salidas</option>
                    <option value="ajuste">Ajustes</option>
                  </select>
                </div>
              </div>

              <div v-if="loadingMovimientos" class="loading">Cargando movimientos...</div>
              
              <div v-else-if="movimientos.length === 0" class="empty-state">
                <i class="fas fa-history"></i>
                <p>No hay movimientos registrados</p>
              </div>

              <div v-else class="movements-list">
                <div 
                  v-for="movimiento in movimientos" 
                  :key="movimiento.id"
                  class="movement-item"
                  :class="movimiento.tipo"
                >
                  <div class="movement-info">
                    <h4>{{ movimiento.producto?.nombre || 'Producto' }}</h4>
                    <p class="movement-meta">
                      <span class="movement-type" :class="movimiento.tipo">
                        {{ movimiento.tipo.toUpperCase() }}
                      </span>
                      • {{ movimiento.cantidad }} unidades
                      • {{ formatFecha(movimiento.created_at) }}
                    </p>
                    <p class="movement-reason">{{ movimiento.motivo }}</p>
                    <p class="movement-user" v-if="movimiento.usuario">
                      Por: {{ movimiento.usuario.name }}
                    </p>
                  </div>
                  <div class="movement-stock">
                    <div class="stock-change">
                      <span class="stock-before">{{ movimiento.stock_anterior }}</span>
                      <i class="fas fa-arrow-right"></i>
                      <span class="stock-after">{{ movimiento.stock_nuevo }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pestaña: Proveedores -->
            <div v-if="activeTab === 'proveedores'" class="tab-pane">
              <div class="section-header">
                <h3>Gestión de Proveedores</h3>
                <button class="btn-primary" @click="abrirModalProveedor()">
                  <i class="fas fa-plus"></i>
                  Nuevo Proveedor
                </button>
              </div>

              <div v-if="loadingProveedores" class="loading">Cargando proveedores...</div>

              <div v-else-if="proveedores.length === 0" class="empty-state">
                <i class="fas fa-truck"></i>
                <p>No hay proveedores registrados</p>
              </div>

              <div v-else class="suppliers-grid">
                <div 
                  v-for="proveedor in proveedores" 
                  :key="proveedor.id"
                  class="supplier-card"
                  :class="{ inactive: !proveedor.activo }"
                >
                  <div class="supplier-info">
                    <h4>{{ proveedor.nombre }}</h4>
                    <p class="supplier-contact" v-if="proveedor.contacto">
                      Contacto: {{ proveedor.contacto }}
                    </p>
                    <p class="supplier-phone" v-if="proveedor.telefono">
                      📞 {{ proveedor.telefono }}
                    </p>
                    <p class="supplier-email" v-if="proveedor.email">
                      ✉️ {{ proveedor.email }}
                    </p>
                    <p class="supplier-products">
                      {{ proveedor.productos_count || 0 }} productos
                    </p>
                    <p class="supplier-delivery">
                      Entrega: {{ proveedor.tiempo_entrega_dias }} días
                    </p>
                  </div>
                  <div class="supplier-actions">
                    <button 
                      class="btn-secondary btn-sm"
                      @click="abrirModalProveedor(proveedor)"
                    >
                      <i class="fas fa-edit"></i>
                    </button>
                    <button 
                      class="btn-danger btn-sm"
                      @click="eliminarProveedor(proveedor)"
                    >
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Pestaña: Ajustar Stock -->
            <div v-if="activeTab === 'ajustes'" class="tab-pane">
              <div class="section-header">
                <h3>Ajustar Stock de Productos</h3>
              </div>

              <div class="adjustment-form">
                <div class="form-group">
                  <label>Producto</label>
                  <select v-model="ajusteStock.producto_id" class="form-control">
                    <option value="">Seleccionar producto</option>
                    <option 
                      v-for="producto in todosProductos" 
                      :key="producto.id"
                      :value="producto.id"
                    >
                      {{ producto.nombre }} (Stock: {{ producto.stock }})
                    </option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Tipo de Movimiento</label>
                  <select v-model="ajusteStock.tipo" class="form-control">
                    <option value="entrada">Entrada</option>
                    <option value="salida">Salida</option>
                    <option value="ajuste">Ajuste Manual</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Cantidad</label>
                  <input 
                    type="number" 
                    v-model="ajusteStock.cantidad"
                    class="form-control"
                    min="1"
                  >
                </div>

                <div class="form-group">
                  <label>Motivo</label>
                  <select v-model="ajusteStock.motivo" class="form-control">
                    <option value="compra">Compra</option>
                    <option value="devolucion">Devolución</option>
                    <option value="ajuste_stock">Ajuste de Stock</option>
                    <option value="perdida">Pérdida</option>
                    <option value="donacion">Donación</option>
                  </select>
                </div>

                <div class="form-group">
                  <label>Observaciones</label>
                  <textarea 
                    v-model="ajusteStock.observaciones"
                    class="form-control"
                    rows="3"
                    placeholder="Observaciones adicionales..."
                  ></textarea>
                </div>

                <button 
                  class="btn-primary"
                  @click="registrarAjusteStock"
                  :disabled="!validarAjusteStock"
                >
                  <i class="fas fa-save"></i>
                  Registrar Movimiento
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { inventoryService } from '../services/inventoryService';

export default {
  name: 'InventoryManagement',
  data() {
    return {
      activeTab: 'stock',
      loading: false,
      loadingMovimientos: false,
      loadingProveedores: false,
      stats: {},
      productosStockBajo: [],
      movimientos: [],
      proveedores: [],
      todosProductos: [],
      filtrosMovimientos: {
        tipo: ''
      },
      ajusteStock: {
        producto_id: '',
        tipo: 'entrada',
        cantidad: 1,
        motivo: 'compra',
        observaciones: '',
        proveedor_id: null
      }
    };
  },
  computed: {
    validarAjusteStock() {
      return this.ajusteStock.producto_id && 
             this.ajusteStock.cantidad > 0 && 
             this.ajusteStock.motivo;
    }
  },
  async mounted() {
    await this.cargarDatosIniciales();
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'stock') {
        this.cargarStockBajo();
      } else if (newTab === 'movimientos') {
        this.cargarMovimientos();
      } else if (newTab === 'proveedores') {
        this.cargarProveedores();
      } else if (newTab === 'ajustes') {
        this.cargarTodosProductos();
      }
    },
    'filtrosMovimientos.tipo'() {
      if (this.activeTab === 'movimientos') {
        this.cargarMovimientos();
      }
    }
  },
  methods: {
    async cargarDatosIniciales() {
      this.loading = true;
      try {
        await Promise.all([
          this.cargarEstadisticas(),
          this.cargarStockBajo()
        ]);
      } catch (error) {
        console.error('Error cargando datos iniciales:', error);
      } finally {
        this.loading = false;
      }
    },

    async cargarEstadisticas() {
      try {
        const response = await inventoryService.getEstadisticas();
        if (response.data.success) {
          this.stats = response.data.data;
        }
      } catch (error) {
        console.error('Error cargando estadísticas:', error);
        // Datos de ejemplo si falla la API
        this.stats = {
          total_productos: 5,
          productos_stock_bajo: 2,
          productos_sin_stock: 0,
          valor_inventario_total: 153.50,
          proveedores_activos: 3
        };
      }
    },

    async cargarStockBajo() {
      try {
        const response = await inventoryService.getStockBajo();
        if (response.data.success) {
          this.productosStockBajo = response.data.data;
        }
      } catch (error) {
        console.error('Error cargando stock bajo:', error);
        this.productosStockBajo = [];
      }
    },

    async cargarMovimientos() {
      this.loadingMovimientos = true;
      try {
        const response = await inventoryService.getHistorialMovimientos(this.filtrosMovimientos);
        if (response.data.success) {
          this.movimientos = response.data.data?.data || response.data.data || [];
        }
      } catch (error) {
        console.error('Error cargando movimientos:', error);
        this.movimientos = [];
      } finally {
        this.loadingMovimientos = false;
      }
    },

    async cargarProveedores() {
  this.loadingProveedores = true;
  try {
    console.log('🟡 Iniciando carga de proveedores...');
    const response = await inventoryService.getProveedores();
    console.log('🔍 Respuesta completa de proveedores:', response);
    console.log('📊 response.data:', response.data);
    console.log('✅ response.data.success:', response.data.success);
    console.log('📦 response.data.data:', response.data.data);
    
    if (response.data.success) {
      this.proveedores = response.data.data;
      console.log('✅ Proveedores cargados:', this.proveedores);
      console.log('🔢 Cantidad de proveedores:', this.proveedores.length);
    } else {
      console.log('❌ Respuesta no exitosa:', response.data);
      this.proveedores = [];
    }
  } catch (error) {
    console.error('💥 Error cargando proveedores:', error);
    console.error('💥 Error details:', error.response);
    this.proveedores = [];
  } finally {
    this.loadingProveedores = false;
    console.log('🏁 Carga de proveedores terminada');
  }
},

    async cargarTodosProductos() {
      try {
        // Usar productos de stock bajo como ejemplo
        this.todosProductos = this.productosStockBajo;
        // Si no hay productos, cargar algunos de ejemplo
        if (this.todosProductos.length === 0) {
          this.todosProductos = [
            { id: 1, nombre: 'Paracetamol 500mg', stock: 15 },
            { id: 2, nombre: 'Protector solar FPS 50', stock: 8 }
          ];
        }
      } catch (error) {
        console.error('Error cargando productos:', error);
      }
    },

    formatFecha(fecha) {
      return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },

    abrirModalAjuste(producto) {
      this.ajusteStock.producto_id = producto.id;
      this.activeTab = 'ajustes';
    },

    abrirModalProveedor(proveedor = null) {
  if (proveedor) {
    // Editar proveedor existente - versión simple con prompt
    const nuevoNombre = prompt(`Editar nombre del proveedor:`, proveedor.nombre);
    if (nuevoNombre && nuevoNombre !== proveedor.nombre) {
      const nuevoContacto = prompt(`Editar contacto:`, proveedor.contacto || '');
      const nuevoTelefono = prompt(`Editar teléfono:`, proveedor.telefono || '');
      const nuevoEmail = prompt(`Editar email:`, proveedor.email || '');
      
      inventoryService.actualizarProveedor(proveedor.id, {
        nombre: nuevoNombre,
        contacto: nuevoContacto,
        telefono: nuevoTelefono,
        email: nuevoEmail,
        activo: proveedor.activo,
        tiempo_entrega_dias: proveedor.tiempo_entrega_dias
      }).then(() => {
        this.cargarProveedores();
        alert('Proveedor actualizado exitosamente');
      }).catch(error => {
        alert('Error actualizando proveedor: ' + error.message);
      });
    }
  } else {
    // Nuevo proveedor - versión simple con prompt
    const nombre = prompt('Nombre del nuevo proveedor:');
    if (nombre) {
      const contacto = prompt('Contacto:');
      const telefono = prompt('Teléfono:');
      const email = prompt('Email:');
      const direccion = prompt('Dirección:');
      const tiempoEntrega = prompt('Tiempo de entrega (días):', '7');
      
      inventoryService.crearProveedor({
        nombre: nombre,
        contacto: contacto,
        telefono: telefono,
        email: email,
        direccion: direccion,
        activo: true,
        tiempo_entrega_dias: parseInt(tiempoEntrega) || 7
      }).then(() => {
        this.cargarProveedores();
        alert('Proveedor creado exitosamente');
      }).catch(error => {
        alert('Error creando proveedor: ' + error.message);
      });
    }
  }
},

    async eliminarProveedor(proveedor) {
      if (confirm(`¿Estás seguro de eliminar al proveedor "${proveedor.nombre}"?`)) {
        try {
          await inventoryService.eliminarProveedor(proveedor.id);
          this.cargarProveedores();
          alert('Proveedor eliminado correctamente');
        } catch (error) {
          console.error('Error eliminando proveedor:', error);
          alert('Error al eliminar proveedor: ' + (error.response?.data?.message || error.message));
        }
      }
    },

    async registrarAjusteStock() {
      try {
        console.log('📝 Registrando movimiento:', this.ajusteStock);
        
        const response = await inventoryService.registrarMovimiento(this.ajusteStock);
        
        if (response.data.success) {
          alert('✅ Movimiento registrado correctamente');
          
          // Limpiar formulario
          this.ajusteStock = {
            producto_id: '',
            tipo: 'entrada',
            cantidad: 1,
            motivo: 'compra',
            observaciones: '',
            proveedor_id: null
          };

          // Recargar datos - ✅ NOMBRE CORREGIDO
          await this.cargarDatosIniciales();
          
          // Volver a la pestaña de stock
          this.activeTab = 'stock';
        } else {
          throw new Error(response.data.message || 'Error al registrar movimiento');
        }
        
      } catch (error) {
        console.error('❌ Error al registrar movimiento:', error);
        alert('Error al registrar movimiento: ' + (error.response?.data?.message || error.message));
      }
    }
  }
};
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  z-index: 1000;
  display: flex;
  align-items: center;
  justify-content: center;
}

.orders-management {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 90%;
  max-width: 1200px;
  height: 85vh;
  background: white;
  border-radius: 12px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.3);
  z-index: 1001;
  display: flex;
  flex-direction: column;
}

.modal-header {
  background: linear-gradient(135deg, #1e88e5, #0d47a1);
  color: white;
  padding: 1rem 1.5rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
  border-radius: 12px 12px 0 0;
}

.modal-title {
  margin: 0;
  font-size: 1.4rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.badge-count {
  background: rgba(255,255,255,0.2);
  padding: 0.2rem 0.6rem;
  border-radius: 20px;
  font-size: 0.8rem;
}

.btn-back, .btn-close {
  background: rgba(255,255,255,0.2);
  border: none;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.9rem;
}

.btn-back:hover, .btn-close:hover {
  background: rgba(255,255,255,0.3);
}

.orders-content {
  flex: 1;
  padding: 1.5rem;
  overflow-y: auto;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: #f8f9fa;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  border-left: 4px solid #1e88e5;
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 1.2rem;
}

.stat-icon.danger { background: #dc3545; }
.stat-icon.warning { background: #ffc107; }
.stat-icon.success { background: #28a745; }
.stat-icon.info { background: #17a2b8; }

.stat-info h3 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: bold;
}

.stat-info p {
  margin: 0;
  color: #666;
  font-size: 0.9rem;
}

/* Tabs */
.tabs-container {
  background: white;
  border-radius: 8px;
  overflow: hidden;
}

.tabs-header {
  display: flex;
  background: #f8f9fa;
  border-bottom: 1px solid #dee2e6;
}

.tab-btn {
  flex: 1;
  padding: 1rem;
  border: none;
  background: none;
  cursor: pointer;
  border-bottom: 3px solid transparent;
  transition: all 0.3s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
}

.tab-btn.active {
  background: white;
  border-bottom-color: #1e88e5;
  color: #1e88e5;
  font-weight: bold;
}

.tab-content {
  padding: 1.5rem;
}

/* Products Grid */
.products-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.product-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.product-card.stock-low {
  border-left: 4px solid #dc3545;
}

.stock-current {
  color: #dc3545;
  font-weight: bold;
}

.stock-min {
  color: #666;
  font-size: 0.9rem;
}

/* Movements List */
.movements-list {
  max-height: 400px;
  overflow-y: auto;
}

.movement-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e9ecef;
}

.movement-item.entrada {
  border-left: 4px solid #28a745;
}

.movement-item.salida {
  border-left: 4px solid #dc3545;
}

.movement-item.ajuste {
  border-left: 4px solid #ffc107;
}

.movement-type {
  padding: 0.2rem 0.5rem;
  border-radius: 4px;
  font-size: 0.8rem;
  font-weight: bold;
}

.movement-type.entrada { background: #d4edda; color: #155724; }
.movement-type.salida { background: #f8d7da; color: #721c24; }
.movement-type.ajuste { background: #fff3cd; color: #856404; }

/* Suppliers Grid */
.suppliers-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1rem;
}

.supplier-card {
  background: white;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  padding: 1rem;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.supplier-card.inactive {
  opacity: 0.6;
  background: #f8f9fa;
}

/* Forms */
.adjustment-form {
  max-width: 500px;
}

.form-group {
  margin-bottom: 1rem;
}

.form-control {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ced4da;
  border-radius: 4px;
  font-size: 1rem;
}

/* Buttons */
.btn-primary, .btn-secondary, .btn-danger {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn-primary {
  background: #1e88e5;
  color: white;
}

.btn-primary:disabled {
  background: #6c757d;
  cursor: not-allowed;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-sm {
  padding: 0.3rem 0.6rem;
  font-size: 0.8rem;
}

/* Estados */
.loading {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #666;
}

.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #ccc;
}

/* Responsive */
@media (max-width: 768px) {
  .orders-management {
    width: 95%;
    height: 90vh;
  }
  
  .stats-grid {
    grid-template-columns: 1fr 1fr;
  }
  
  .tabs-header {
    flex-direction: column;
  }
  
  .products-grid,
  .suppliers-grid {
    grid-template-columns: 1fr;
  }
}
</style>