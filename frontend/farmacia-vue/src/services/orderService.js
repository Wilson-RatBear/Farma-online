import api from './api';

export const orderService = {
    // Métodos existentes
    createOrder(orderData) {
        return api.post('/pedidos/checkout', orderData);
    },

    getOrders() {
        return api.get('/pedidos');
    },

    getOrder(id) {
        return api.get(`/pedidos/${id}`);
    },

    updateOrderStatus(orderId, status) {
        return api.post(`/pedidos/${orderId}/estado`, { estado: status });
    },

    // ✅ NUEVOS MÉTODOS PARA PAGOS REALES
    getPaymentMethods() {
        return api.get('/metodos-pago');
    },

    createOrderWithPayment(orderData) {
        return api.post('/pedidos/checkout-con-pago', orderData);
    },

    verifyPayment(orderId) {
        return api.post(`/pedidos/${orderId}/verificar-pago`);
    }
};