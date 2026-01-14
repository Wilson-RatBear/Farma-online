import api from './api';

export const reviewService = {
  // Obtener reseñas de un producto
  getProductReviews(productoId) {
    return api.get(`/productos/${productoId}/resenas`);
  },

  // Obtener reseñas del usuario actual
  getMyReviews() {
    return api.get('/mis-resenas');
  },

  // Crear nueva reseña
  createReview(reviewData) {
    return api.post('/resenas', reviewData);
  },

  // Actualizar reseña
  updateReview(reviewId, reviewData) {
    return api.put(`/resenas/${reviewId}`, reviewData);
  },

  // Eliminar reseña
  deleteReview(reviewId) {
    return api.delete(`/resenas/${reviewId}`);
  }
};