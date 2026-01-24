import axios from 'axios';

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8001/api',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
});

// Interceptor para agregar token JWT automáticamente
api.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('auth_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
            console.log('Token enviado en request:', token.substring(0, 20) + '...'); // Solo primeros 20 chars
        } else {
            console.warn('No hay token en localStorage');
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);

// Interceptor de respuesta CON LOGS PARA DEBUG
api.interceptors.response.use(
    (response) => {
        console.log('Respuesta exitosa:', response.config.url);
        return response;
    },
    (error) => {
        console.error('Error en petición:', {
            url: error.config?.url,
            status: error.response?.status,
            statusText: error.response?.statusText,
            data: error.response?.data
        });
        
        if (error.response?.status === 401) {
            console.log('Error 401 - Token inválido o expirado');
            
            // Limpiar token inválido
            localStorage.removeItem('auth_token');
            
            // Redirigir solo si no está en login
            if (!window.location.pathname.includes('/login')) {
                setTimeout(() => {
                    window.location.href = '/login?reason=session_expired';
                }, 1000);
            }
        }
        
        return Promise.reject(error);
    }
);

export default api;