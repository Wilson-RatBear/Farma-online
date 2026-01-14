import api from './api';

export const reportService = {
  // Obtener métricas generales para el dashboard
  async getGeneralMetrics() {
    try {
      const response = await api.get('/admin/reports/metricas-generales');
      return response.data;
    } catch (error) {
      console.error('Error fetching general metrics:', error);
      throw error;
    }
  }
};

export default reportService;