# CONTENTIO — Guía de Entorno de Desarrollo

> Documento de referencia para levantar, usar y mantener el entorno local completo.
> Última actualización: 2026-04-21

---

## 1. Requisitos previos

| Herramienta | Versión | Para qué sirve |
|---|---|---|
| Docker Desktop | 29.2.0+ | Ejecutar todos los servicios |
| Git | cualquiera | Control de versiones |
| VS Code (recomendado) | cualquiera | Editar el código |
| DBeaver (recomendado) | cualquiera | Gestionar la base de datos |
| Navegador | Chrome/Edge | Ver el frontend |

> **Node.js y PHP NO se instalan en tu máquina.** Todo corre dentro de Docker.

---

## 2. Estructura del proyecto

```
dam-final-project-contentio/
├── backend/          ← Laravel 12 (PHP)
├── frontend/         ← Vue 3 + Vite + Tailwind
├── docker/           ← Configuración Docker (Nginx, PHP)
├── docs/             ← Documentación, mockups, diagramas
├── docker-compose.yml
└── .env (backend)
```

---

## 3. Los 4 servicios de Docker

| Contenedor | Tecnología | URL local | Para qué |
|---|---|---|---|
| `contentio_node` | Node 20 + Vite | http://localhost:5173 | Frontend Vue (dev server) |
| `contentio_nginx` | Nginx 1.27 | http://localhost:8080 | Backend API Laravel |
| `contentio_php` | PHP 8.3 + Laravel | — (interno) | Procesa la lógica PHP |
| `contentio_postgres` | PostgreSQL 16 | localhost:5433 | Base de datos |

---

## 4. Levantar el entorno (cada vez que quieras trabajar)

### Paso 1 — Abrir Docker Desktop
Asegúrate de que el icono de Docker está en la barra de tareas y el estado es **Running**.

### Paso 2 — Abrir terminal y levantar contenedores
```bash
cd "C:\Users\Usuario\Documents\FP DAM\dam-2\dam-final-project-contentio"
docker compose up -d
```

### Paso 3 — Verificar que todo está corriendo
```bash
docker compose ps
```
Debes ver los 4 contenedores con estado **Up**.

### Paso 4 — Abrir en el navegador
- **Frontend:** http://localhost:5173
- **API (test):** http://localhost:8080/api/auth/me

---

## 5. Accesos a cada capa

### 5.1 Frontend — Vue 3
- **URL:** http://localhost:5173
- **Código:** `frontend/src/`
- **Vistas:** `frontend/src/views/`
- **Componentes:** `frontend/src/components/`
- **Stores (Pinia):** `frontend/src/stores/`
- **Router:** `frontend/src/router/index.js`
- **Estilos:** `frontend/src/style.css`
- **Vite recarga automáticamente** al guardar cualquier archivo `.vue`

### Rutas Vue activas
| Ruta | Vista | Descripción |
|---|---|---|
| `/` | → `/dashboard` | Redirect |
| `/login` | LoginView | Acceso |
| `/dashboard` | DashboardView | Panel principal |
| `/content-items` | ContentListView | Lista de contenidos |
| `/content-items/new` | ContentFormView | Crear contenido |
| `/content-items/:id/edit` | ContentFormView | Editar contenido |
| `/content-items/:id/review` | ContentReviewView | Revisar + anotar |

### 5.2 Backend — Laravel 12
- **URL API:** http://localhost:8080/api/
- **Rutas API:** `backend/routes/api.php`
- **Controladores:** `backend/app/Http/Controllers/Api/`
- **Modelos:** `backend/app/Models/`
- **Migraciones:** `backend/database/migrations/`

> ⚠️ Después de cambiar código PHP, si las rutas no responden hacer:
> ```bash
> docker compose restart php
> ```

### API Endpoints

| Método | Ruta | Auth | Rol |
|---|---|---|---|
| POST | /api/auth/login | No | — |
| POST | /api/auth/logout | Sí | any |
| GET | /api/auth/me | Sí | any |
| GET | /api/clinics | Sí | agency |
| GET/POST | /api/content-items | Sí | agency |
| GET/PUT/DELETE | /api/content-items/{id} | Sí | agency |
| POST | /api/content-items/{id}/media | Sí | agency |
| DELETE | /api/content-items/{id}/media/{id} | Sí | agency |
| POST | /api/content-items/{id}/transition | Sí | agency,clinic |
| GET/POST | /api/content-items/{id}/comments | Sí | agency,clinic |
| DELETE | /api/content-items/{id}/comments/{id} | Sí | agency,clinic |
| GET/POST | /api/media-assets/{id}/annotations | Sí | agency,clinic |
| DELETE | /api/media-assets/{id}/annotations/{id} | Sí | agency,clinic |

### 5.3 Base de datos — PostgreSQL 16

**Datos de conexión (DBeaver):**
| Campo | Valor |
|---|---|
| Host | `localhost` |
| Puerto | `5433` |
| Base de datos | `contentio` |
| Usuario | `contentio` |
| Contraseña | vacío (modo trust) |

**Comandos útiles desde terminal:**
```bash
docker compose exec postgres psql -U contentio -d contentio
\dt        # ver tablas
\q         # salir
```

### 5.4 Comandos Artisan (Laravel)
```bash
docker compose exec php php artisan migrate
docker compose exec php php artisan migrate:fresh --seed
docker compose exec php php artisan route:list
docker compose exec php php artisan storage:link
docker compose exec php php artisan cache:clear
```

---

## 6. Credenciales de prueba

| Email | Contraseña | Rol | Clínica asignada |
|---|---|---|---|
| `test@example.com` | `password123` | agency | Clínica Demo (ID=1) |

> Si la BD está vacía, crear usuario y clínica manualmente desde DBeaver o tinker.

---

## 7. Subida de archivos (Media Assets)

- Límite: **100 MB** (configurado en PHP y Nginx)
- Disco: `public` → accesibles via `http://localhost:8080/storage/{file_path}`
- Tipos aceptados: jpg, jpeg, png, gif, mp4, mov, avi, webm
- Almacenamiento: `backend/storage/app/public/media/{content_item_id}/`

---

## 8. Flujo de trabajo diario

```bash
# Levantar
docker compose up -d

# Ver logs si algo falla
docker compose logs php -f
docker compose logs nginx -f

# Reiniciar PHP (tras cambios de rutas)
docker compose restart php

# Apagar
docker compose down
```

---

## 9. Flujo Git

### Ramas del proyecto
| Rama | Propósito |
|---|---|
| `main` | Producción |
| `develop` | Integración |
| `feature/S1-auth` | Sprint 1 ✅ mergeado |
| `feature/S2-content` | Sprint 2 ✅ mergeado |
| `feature/S3-review` | Sprint 3 🔄 en curso |

### Convención de commits
```
[S3-21] Add Vue content review page
[UI] Apply design system palette
[FIX] Fix file upload disk configuration
```

---

## 10. Solución de problemas

| Problema | Causa | Solución |
|---|---|---|
| Frontend no carga | Contenedor node tardando | Esperar 15s y recargar |
| API devuelve 404 | Ruta no encontrada | `docker compose restart php` |
| Cambios PHP no se ven | opcache activo | `docker compose restart php` |
| Error al subir archivo | Límite superado | Máximo 100MB por archivo |
| BD no conecta | Puerto incorrecto | Usar puerto **5433** en DBeaver |
| Token expirado | Sesión caducada | Logout y login de nuevo |

---

*CONTENTIO · TFG DAM2 · Oscar Felipe Vargas García · 2026*
