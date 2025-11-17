import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api', // URL de tu Laravel
    withCredentials: true, // Importante para cookies y sesiones
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Interceptor para manejar errores globalmente
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            // Redirigir a login si no está autenticado
            window.location.href = '/login';
        }
        return Promise.reject(error);
    }
);

export default api;