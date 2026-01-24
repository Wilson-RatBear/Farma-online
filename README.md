# 💊 Farma-Online: Sistema de Gestión para Farmacias

Este es un sistema completo de comercio electrónico para farmacias, diseñado con una arquitectura desacoplada utilizando **Laravel** (Backend) y **Vue.js** (Frontend).

---

## 🚀 Inicio Rápido con Docker (Recomendado)

Si tienes **Docker** y **Docker Compose** instalados, esta es la forma más rápida de poner en marcha el proyecto sin instalar PHP o Node.js localmente.

### 1. Levantar contenedores
Desde la raíz del proyecto, ejecuta:
```bash
docker compose up -d --build
```

### 2. Configuración inicial automática
Este comando instalará dependencias, creará la base de datos SQLite y generará las llaves de seguridad dentro del contenedor:
```bash
docker compose exec backend composer setup-docker
```

### 🌍 Acceso:
- **Frontend:** [http://localhost:3000](http://localhost:3000)
- **Backend (API):** [http://localhost:8001](http://localhost:8001)

---

## 🛠️ Instalación Local (Manual)

### Requisitos:
- PHP 8.2+
- Composer
- Node.js 20+ & NPM

### A. Configuración del Backend
1. Entra a la carpeta: `cd backend`
2. Instala dependencias: `composer install`
3. Crea el entorno: `cp .env.example .env`
4. Genera llaves: `php artisan key:generate && php artisan jwt:secret`
5. Base de datos (SQLite):
   ```bash
   touch database/database.sqlite
   php artisan migrate --seed
   ```
6. Inicia el servidor: `php artisan serve` (Correrá en http://127.0.0.1:8000)

### B. Configuración del Frontend
1. Entra a la carpeta: `cd frontend/farmacia-vue`
2. Instala dependencias: `npm install`
3. Inicia desarrollo: `npm run dev` (Correrá en http://localhost:3000 o 5173 según config)

---

## 📂 Estructura del Proyecto

- `backend/`: API Restful con Laravel.
- `frontend/farmacia-vue/`: Interfaz reactiva con Vue 3 + Vite.
- `compose.yml`: Orquestación de contenedores (Backend, Frontend, Nginx).
- `nginx.conf`: Configuración del servidor web para producción/docker.

---

## 🛠️ Comandos Útiles

| Acción | Comando |
| :--- | :--- |
| Ver Logs Docker | `docker compose logs -f` |
| Detener Docker | `docker compose down` |
| Reiniciar Frontend | `docker compose restart frontend` |
| Entrar al Backend | `docker compose exec backend bash` |

---

## 👤 Credenciales de Prueba
Puedes encontrar datos de prueba en la carpeta `configuracion o documentacion` o usar los generados por el comando `--seed`.

© 2026 Farma-Online Team
