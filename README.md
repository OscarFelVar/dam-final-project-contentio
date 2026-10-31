# Contentio

> 📦 **Proyecto académico archivado.** Trabajo de Fin de Grado del CFGS en Desarrollo de Aplicaciones Multiplataforma (Prometeo The Power FP, 2026), calificado con un **10**.
> La versión evaluada corresponde a la release [`v1.0-tfg`](https://github.com/OscarFelVar/dam-final-project-contentio/releases/tag/v1.0-tfg). El proyecto continúa como producto en un repositorio privado independiente, por lo que este repositorio ya no recibe cambios.

Plataforma de gestión y aprobación de contenido audiovisual entre agencias creativas y sus clientes.

Contentio permite a realizadores audiovisuales subir contenido (reels, posts, stories), recibir feedback visual mediante anotaciones sobre las imágenes, y gestionar el ciclo de aprobación con sus clientes de forma ordenada y trazable.

---

## Características principales

- **Gestión de contenidos** — Crear, editar y organizar piezas audiovisuales por cliente y tipo
- **Subida de media** — Imágenes y vídeos hasta 500 MB
- **Anotaciones visuales** — Pines sobre imágenes al estilo Figma para feedback preciso
- **Flujo de aprobación** — State machine: borrador → revisión → aprobado/corrección/rechazado
- **Comentarios** — Hilo de conversación por contenido
- **Notificaciones por email** — Aviso automático al cambiar el estado de un contenido
- **Historial de estados** — Timeline visual de todos los cambios de estado
- **Dashboard** — Resumen de actividad con alertas de acción pendiente

---

## Stack tecnológico

| Capa | Tecnología |
|---|---|
| Backend | PHP 8.3 · Laravel 12 · Sanctum |
| Frontend | Vue 3 · Pinia · Vue Router · Vite · Tailwind v4 |
| Base de datos | PostgreSQL 16 |
| Infraestructura | Docker Compose |
| Tests backend | PHPUnit 11 |
| Tests E2E | Playwright |

---

## Requisitos

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) 29.2+
- Git

> Node.js y PHP no se instalan en la máquina local. Todo corre en Docker.

---

## Inicio rápido (desarrollo)

```bash
# 1. Clonar el repositorio
git clone https://github.com/OscarFelVar/dam-final-project-contentio
cd dam-final-project-contentio

# 2. Levantar el stack completo
docker compose up -d

# 3. Ejecutar migraciones (primera vez)
docker compose exec php php artisan migrate --seed

# 4. Crear enlace de storage
docker compose exec php php artisan storage:link
```

**Accesos:**

| Servicio | URL |
|---|---|
| Frontend (Vue) | http://localhost:5173 |
| API (Laravel) | http://localhost:8080/api |
| Base de datos | localhost:5433 |

**Credenciales de prueba (entorno local):**

| Email | Contraseña | Rol |
|---|---|---|
| `admin@contentio.com` | `password123` | admin |
| `test@example.com` | `password123` | agency |
| `cliente@clinica.com` | `password123` | clinic |
| `ana@bellezavalencia.com` | `password123` | clinic |

---

## Producción

Durante la evaluación, la aplicación estuvo desplegada en `https://contentio.tech` (VPS OVH, Docker Compose, Nginx + Let's Encrypt). Las cuentas de evaluación ya no están disponibles públicamente, y ese despliegue ya no corresponde a la versión de este repositorio.

Documentación del TFG: [Memoria](docs/memoria/Memoria_Contentio.pdf) · [Anteproyecto](docs/memoria/Anteproyecto_Contentio.pdf)

---

## Tests

### Backend (PHPUnit)

```bash
docker compose exec php php artisan test
```

66 tests · 145 assertions · **85.3% cobertura** — cubre auth, CRUD de contenidos, state machine, comentarios, anotaciones y notificaciones.

### E2E (Playwright)

Requiere el stack Docker corriendo y Playwright instalado en el host:

```bash
cd frontend
npx playwright install chromium   # una sola vez
npm run test:e2e
```

---

## Despliegue en producción

Ver [`deploy.sh`](./deploy.sh) y [`docker-compose.prod.yml`](./docker-compose.prod.yml).

El stack de producción incluye:
- Build estático del frontend (Vite)
- Nginx con SSL (Let's Encrypt / certbot)
- PHP-FPM con OPcache y caches de Laravel
- PostgreSQL con volumen persistente

```bash
# En el VPS, con el repo clonado y .env configurado:
bash deploy.sh tu-dominio.com tu-email@gmail.com
```

---

## Estructura del proyecto

```
contentio/
├── backend/                  ← Laravel 12
│   ├── app/
│   │   ├── Http/Controllers/Api/
│   │   ├── Models/
│   │   └── Notifications/
│   ├── database/
│   │   ├── migrations/
│   │   └── factories/
│   └── tests/Feature/        ← PHPUnit feature tests
├── frontend/                 ← Vue 3 + Vite
│   ├── src/
│   │   ├── views/
│   │   ├── components/
│   │   ├── stores/           ← Pinia
│   │   └── router/
│   └── e2e/                  ← Playwright tests
├── docker/
│   ├── nginx/                ← Configuración Nginx (dev + prod)
│   ├── php/                  ← Dockerfile PHP + entrypoint
│   └── frontend/             ← Dockerfile build Vue (prod)
├── docs/
│   ├── implementacion/       ← Documentación técnica por sprint
│   └── memoria/              ← Memoria del TFG
├── database/
│   └── schema.sql            ← Esquema PostgreSQL exportado
├── docker-compose.yml        ← Entorno de desarrollo
├── docker-compose.prod.yml   ← Entorno de producción
├── deploy.sh                 ← Script de despliegue inicial
├── update.sh                 ← Script de actualización
└── backup.sh                 ← Script de copias de seguridad
```

---

## Documentación

| Documento | Descripción |
|---|---|
| [docs/DEVELOPMENT.md](docs/DEVELOPMENT.md) | Guía completa del entorno de desarrollo |
| [docs/implementacion/](docs/implementacion/) | Documentación técnica por sprint |
| [PLANIFICACION_TFG.md](PLANIFICACION_TFG.md) | Planificación y calendario de sprints |

---

## API — Endpoints principales

| Método | Ruta | Rol |
|---|---|---|
| POST | `/api/auth/login` | — |
| POST | `/api/auth/logout` | any |
| GET | `/api/auth/me` | any |
| GET/POST | `/api/clinics` | agency |
| GET/POST/DELETE | `/api/clinics/{id}/users` | agency |
| GET/POST | `/api/content-items` | agency, clinic, admin |
| GET/PUT/DELETE | `/api/content-items/{id}` | agency, clinic, admin |
| POST | `/api/content-items/{id}/media` | agency |
| POST | `/api/content-items/{id}/transition` | agency, clinic, admin |
| GET/POST | `/api/content-items/{id}/comments` | agency, clinic, admin |
| GET/POST | `/api/media-assets/{id}/annotations` | agency, clinic, admin |
| GET | `/api/admin/stats` | admin |
| GET/POST/DELETE | `/api/admin/users` | admin |

Autenticación mediante **Laravel Sanctum** (token Bearer).

---

## Flujo de estados

```
draft ──────────────────────► ready_for_review
                                      │
              ┌───────────────────────┼──────────────────┐
              ▼                       ▼                   ▼
        in_correction             approved            rejected
              │
              └──────────────► ready_for_review
```

---

*Contentio · TFG DAM2 · Oscar Felipe Vargas García · Prometeo The Power FP Oficial · 2026*
