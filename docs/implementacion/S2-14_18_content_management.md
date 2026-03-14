# Sprint 2 — Content Management

**Rama:** `feature/S2-content` → mergeada a `develop` (PR #39)
**Fecha:** 2026-03-14
**Issues:** #14 · #15 · #16 · #17 · #18

---

## Resumen

Sprint 2 implementa la gestión completa de contenidos: CRUD de content items, subida de archivos multimedia, vistas Vue del listado y formulario, y la state machine que controla el ciclo de vida de cada pieza de contenido.

---

## #14 — Content Items CRUD API

### Modelos creados

**`ContentItem`** (`app/Models/ContentItem.php`)
- `$fillable`: clinic_id, created_by, title, type, status, deadline_at
- Cast: `deadline_at` → datetime
- Relaciones: `clinic()`, `createdBy()`, `mediaAssets()`, `stateHistories()`

**`Clinic`** (`app/Models/Clinic.php`)
- `$fillable`: name, description, contact_email, user_id
- Relaciones: `owner()`, `contentItems()`

**`MediaAsset`** (`app/Models/MediaAsset.php`)
- `$fillable`: content_item_id, file_path, file_name, mime_type, size
- Relación: `contentItem()`

**`User`** (actualizado)
- Añadida relación: `clinics(): HasMany`

### Endpoints

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/content-items` | Lista items de las clínicas del usuario |
| POST | `/api/content-items` | Crea un nuevo item |
| GET | `/api/content-items/{id}` | Muestra un item |
| PUT | `/api/content-items/{id}` | Edita título, tipo y deadline |
| DELETE | `/api/content-items/{id}` | Elimina (solo en estado draft) |

**Middleware:** `auth:sanctum` + `role:agency`

### Nota de diseño (TODO Sprint 3+)
La autorización verifica que `clinic_id` pertenezca al usuario autenticado via `clinics()` (relación `user_id`). Cuando exista la tabla pivot `clinic_user` (many-to-many agencia↔clínica), ampliar esta validación.

---

## #15 — Media Asset Upload

### Endpoint

| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/content-items/{id}/media` | Sube un archivo al item |
| DELETE | `/api/content-items/{id}/media/{mediaId}` | Elimina un archivo |

**Almacenamiento:** disco `local` (privado) en `storage/app/private/media/{content_item_id}/`

**Formatos aceptados:** jpg, jpeg, png, gif, mp4, mov, avi, webm
**Tamaño máximo:** 100 MB

### Nota de diseño (TODO Sprint 3+)
Los archivos son privados — no accesibles por URL pública. Pendiente implementar `GET /content-items/{id}/media/{mediaId}/download` que sirva el archivo con `Storage::download()` tras verificar autenticación.

---

## #16 — Vue: Listado de contenidos

**Archivo:** `frontend/src/views/ContentListView.vue`

- Header con nombre de usuario y logout
- Botón "+ Nuevo contenido" → `/content-items/new`
- Estados vacío / cargando / error
- Tarjetas con badges de tipo (post/reel/story) y estado (draft/ready_for_review/etc.)
- Acciones: Editar (todos) · Eliminar (solo draft)

---

## #17 — Vue: Formulario crear/editar

**Archivo:** `frontend/src/views/ContentFormView.vue`
**Store:** `frontend/src/stores/content.js`

- Misma vista para crear (`/content-items/new`) y editar (`/content-items/:id`)
- Selector de clínica (solo en creación) — carga desde `GET /api/clinics`
- Campo de archivo **opcional** — si se adjunta, se sube inmediatamente tras crear/editar
- En edición: lista archivos ya subidos con opción de eliminar cada uno

### Flujo de subida con archivo inmediato
```
POST /content-items  →  POST /content-items/{id}/media
```
Dos llamadas consecutivas. Si no hay archivo, solo la primera.

---

## #18 — State Machine

**Archivo:** `app/Http/Controllers/Api/ContentStateController.php`
**Modelo:** `app/Models/StateHistory.php`

### Diagrama de estados

```
draft ──────────────────────────► ready_for_review
                                         │
                   ┌─────────────────────┼──────────────────┐
                   ▼                     ▼                   ▼
             in_correction           approved            rejected
                   │
                   └────────────► ready_for_review
```

### Transiciones válidas por rol

| Rol | Desde | Hacia |
|---|---|---|
| agency | draft | ready_for_review |
| agency | in_correction | ready_for_review |
| clinic | ready_for_review | in_correction |
| clinic | ready_for_review | approved |
| clinic | ready_for_review | rejected |

### Endpoint

```
POST /api/content-items/{id}/transition
Body: { "status": "ready_for_review" }
```

**Middleware:** `auth:sanctum` + `role:agency,clinic`

### Trazabilidad
Cada transición se registra en `state_histories`:
- `content_item_id` — item afectado
- `user_id` — quién hizo el cambio
- `from_status` / `to_status` — transición realizada
- `created_at` — timestamp

La actualización del status y la inserción en state_histories se ejecutan en una **transacción atómica** (`DB::transaction`).

### Errores
- Transición no permitida → `422` con mensaje descriptivo
- Sin autenticación → `401`
- Clínica no pertenece al usuario → `403`

---

## Optimización de rendimiento (fix sesión esta sesión)

Se resolvió lentitud de ~60s/petición a ~100ms:

| Cambio | Archivo | Efecto |
|---|---|---|
| `SESSION_DRIVER=file` | `.env` | Elimina queries DB para sesiones |
| `CACHE_STORE=file` | `.env` | Elimina queries DB para caché interna |
| `BCRYPT_ROUNDS=10` | `.env` | Hash 4× más rápido en dev |
| `opcache.validate_timestamps=0` | `docker/php/opcache.ini` | No hace stat() de archivos en cada petición |

> **Nota:** con `validate_timestamps=0`, ejecutar `docker compose restart php` después de cada cambio en código PHP.

---

## Estructura de archivos creados/modificados

```
backend/
  app/
    Http/Controllers/Api/
      ContentItemController.php   ← CRUD + autorización
      MediaAssetController.php    ← upload/delete archivos
      ContentStateController.php  ← state machine
      ClinicController.php        ← GET /clinics (index)
    Models/
      ContentItem.php
      Clinic.php
      MediaAsset.php
      StateHistory.php
      User.php                    ← añadida relación clinics()
  routes/
    api.php                       ← nuevas rutas S2

frontend/
  src/
    stores/
      content.js                  ← Pinia store CRUD + media
    views/
      ContentListView.vue         ← listado
      ContentFormView.vue         ← crear/editar
    router/
      index.js                    ← nuevas rutas + fix redirect

docker/
  php/
    opcache.ini                   ← validate_timestamps=0
```
