# Sprint 5 — Testing, Deployment & Closing

**Rama:** `feature/S5-closing` → merge a `develop` previsto 02/05/2026
**Issues:** #33 · #34 · #35 · #36 · #37

---

## Resumen

Sprint 5 cierra el proyecto con tests automatizados (backend + E2E), configuracion de despliegue en produccion con SSL, README tecnico completo y preparacion de la demo final.

---

## #33 — PHPUnit Feature Tests (cobertura >= 70%)

### Configuracion

- Motor: **PHPUnit 11** (incluido en Laravel 12)
- Base de datos de test: **SQLite in-memory** (`DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`)
- Mail en tests: driver `array` (captura emails sin enviarlos)
- Configuracion: `phpunit.xml`

### Factories creadas

| Factory | Modelo | Estados adicionales |
|---|---|---|
| `UserFactory` | User | `agency()`, `clinic()` |
| `ClinicFactory` | Clinic | — |
| `ContentItemFactory` | ContentItem | `draft()`, `readyForReview()`, `approved()` |
| `MediaAssetFactory` | MediaAsset | — |
| `CommentFactory` | Comment | — |
| `AnnotationFactory` | Annotation | — |

Se añadio el trait `HasFactory` a los modelos: `Clinic`, `ContentItem`, `MediaAsset`, `Comment`, `Annotation`.

### Test files

| Archivo | Tests | Que cubre |
|---|---|---|
| `AuthTest.php` | 6 | Login, credenciales incorrectas, perfil, logout con token Sanctum |
| `ContentItemTest.php` | 12 | CRUD completo, autorizacion por rol, validaciones |
| `ContentStateTest.php` | 10 | Transiciones validas/invalidas, historial, notificaciones |
| `CommentTest.php` | 6 | Listar, crear, eliminar propio, no eliminar ajeno |
| `AnnotationTest.php` | 7 | Listar, crear, pin_number autoincremental, validacion rango, eliminar |

**Resultado:** 43 tests · 89 assertions · 0 fallos

```bash
docker compose exec php php artisan test
```

### Nota sobre logout en tests

`actingAs($user)` no crea token Sanctum real. Para testear logout usar:

```php
$token = $user->createToken('test')->plainTextToken;
$this->withToken($token)->postJson('/api/auth/logout');
```

---

## #34 — Playwright E2E Tests

### Configuracion

**Archivo:** `frontend/playwright.config.js`

- Base URL: `http://localhost:5173`
- Browser: Chromium
- Requiere el stack Docker corriendo al ejecutar

### Test files

| Archivo | Tests | Que cubre |
|---|---|---|
| `e2e/auth.spec.js` | 4 | Redirect sin auth, errores login, login correcto, logout |
| `e2e/content-happy-path.spec.js` | 6 | Dashboard, navegacion, crear item, comentar, enviar a revision, estructura pagina revision |

### Ejecucion

```bash
# Una sola vez: instalar browser (en el host, no en Docker)
cd frontend
npx playwright install chromium

# Ejecutar (con Docker corriendo)
npm run test:e2e

# Modo visual interactivo
npm run test:e2e:ui
```

### Por que no corre en Docker

El contenedor Node usa Alpine Linux que no tiene las dependencias de sistema para browsers Chromium. Los tests E2E se ejecutan desde el host contra el stack Docker.

---

## #35 — Deploy Docker Compose produccion OVH VPS

### Archivos creados

| Archivo | Descripcion |
|---|---|
| `docker-compose.prod.yml` | Stack completo de produccion |
| `docker/nginx/prod.conf` | Nginx con SSL, redirect HTTP→HTTPS, SPA routing, proxy API |
| `docker/frontend/Dockerfile` | Build multi-stage Vue (Node builder → Alpine dist) |
| `backend/.env.production.example` | Plantilla de variables de entorno para produccion |
| `deploy.sh` | Script de despliegue inicial automatizado |

### Diferencias dev vs produccion

| Aspecto | Desarrollo | Produccion |
|---|---|---|
| Frontend | Vite dev server (Node container) | Build estatico servido por Nginx |
| SSL | No | Let's Encrypt (certbot) |
| Puertos | 5173 (Vue), 8080 (API) | 80 + 443 (mismo dominio) |
| APP_DEBUG | true | false |
| BCRYPT_ROUNDS | 10 | 12 |
| Mail driver | log | SMTP real |

### Stack de produccion

```
nginx        → puerto 80/443, sirve frontend + proxy /api/* a PHP
php          → PHP-FPM 8.3 con OPcache y cache warmup en entrypoint
postgres     → PostgreSQL 16 con volumen persistente
frontend-build → construye Vue y deposita dist en volumen compartido
certbot      → obtener/renovar certificado SSL Let's Encrypt
```

### Flujo de despliegue inicial

```bash
bash deploy.sh tu-dominio.com tu-email@gmail.com
```

El script automatiza: arranque del stack → SSL con certbot → migraciones → storage:link → optimizacion Laravel.

### Renovacion SSL automatica (crontab en el VPS)

```bash
0 3 * * * docker compose -f /ruta/proyecto/docker-compose.prod.yml run --rm certbot renew \
  && docker compose -f /ruta/proyecto/docker-compose.prod.yml exec nginx nginx -s reload
```

---

## #36 — README y documentacion tecnica

### Archivos actualizados

- `README.md` — README principal del repositorio (descripcion, stack, quick start, tests, deploy, estructura, API, diagrama de estados)
- `frontend/README.md` — sustituye el placeholder de Vite por instrucciones reales

### Estructura del README principal

1. Descripcion del proyecto y caso de uso real
2. Features principales
3. Stack tecnologico
4. Requisitos e inicio rapido (4 comandos)
5. Como ejecutar tests backend y E2E
6. Despliegue en produccion
7. Estructura de directorios
8. Tabla de API endpoints
9. Diagrama ASCII del flujo de estados

---

## #37 — Demo y presentacion

Pendiente de grabar. Guion sugerido:

1. Login como agency → dashboard con metricas
2. Crear nuevo content item → subir imagen
3. Enviar a revision (modal de confirmacion)
4. Login como clinic → revisar imagen con anotaciones visuales
5. Aprobar con comentario (modal + nota → comentario automatico)
6. Volver a agency → ver historial de estados en timeline
7. Mostrar email en log: `docker compose exec php tail -f storage/logs/laravel.log`

---

## Gaps identificados para produccion real

Al completar S5 se identificaron mejoras necesarias para el uso real por parte de la agencia:

1. **Gestion de clinicas desde la UI** — actualmente las clinicas se crean solo desde BD
2. **Creacion de usuarios clinic** — no hay forma de invitar clientes desde la app
3. **Paginacion** — necesaria con volumenes altos de contenidos

Estos puntos se atacaran antes del cierre del proyecto.

---

## Estructura de archivos creados/modificados

```
backend/
  app/Models/
    Clinic.php          ← HasFactory añadido
    ContentItem.php     ← HasFactory añadido
    MediaAsset.php      ← HasFactory añadido
    Comment.php         ← HasFactory añadido
    Annotation.php      ← HasFactory añadido
  database/factories/
    ClinicFactory.php       ← NUEVO
    ContentItemFactory.php  ← NUEVO
    MediaAssetFactory.php   ← NUEVO
    CommentFactory.php      ← NUEVO
    AnnotationFactory.php   ← NUEVO
    UserFactory.php         ← estados agency() y clinic()
  tests/Feature/
    AuthTest.php            ← NUEVO
    ContentItemTest.php     ← NUEVO
    ContentStateTest.php    ← NUEVO
    CommentTest.php         ← NUEVO
    AnnotationTest.php      ← NUEVO

frontend/
  playwright.config.js      ← NUEVO
  e2e/
    auth.spec.js                    ← NUEVO
    content-happy-path.spec.js      ← NUEVO
  package.json              ← scripts test:e2e + @playwright/test

docker/
  frontend/
    Dockerfile              ← NUEVO: build multi-stage Vue
  nginx/
    prod.conf               ← NUEVO: config produccion con SSL

docker-compose.prod.yml     ← NUEVO
deploy.sh                   ← NUEVO
backend/.env.production.example ← NUEVO
README.md                   ← reescrito completo
frontend/README.md          ← actualizado
```
