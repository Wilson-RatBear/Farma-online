import api from './api';

export const inventoryService = {
    // Obtener productos con stock bajo
    getStockBajo() {
        return api.get('/inventory/stock-bajo');
    },

    // Obtener estadísticas de inventario
    getEstadisticas() {
        return api.get('/inventory/estadisticas');
    },

    // Registrar movimiento de inventario
    registrarMovimiento(movimientoData) {
        return api.post('/inventory/registrar-movimiento', movimientoData);
    },

    // Obtener historial de movimientos
    getHistorialMovimientos(filters = {}) {
        return api.get('/inventory/historial-movimientos', { params: filters });
    },

    // Obtener todos los proveedores
    getProveedores() {
        return api.get('/proveedores');
    },

    // Crear nuevo proveedor
    crearProveedor(proveedorData) {
        return api.post('/proveedores', proveedorData);
    },

    // Actualizar proveedor
    actualizarProveedor(id, proveedorData) {
        return api.put(`/proveedores/${id}`, proveedorData);
    },

    // Eliminar proveedor
    eliminarProveedor(id) {
        return api.delete(`/proveedores/${id}`);
    },

    // Obtener estadísticas de proveedores
    getEstadisticasProveedores() {
        return api.get('/proveedores/estadisticas');
    }
};