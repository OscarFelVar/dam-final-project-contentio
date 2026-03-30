# Sprint 3 — Review & Annotations

**Rama:** `feature/S3-review` → merge a `develop` previsto 28/03/2026
**Issues:** #19 · #20 · #21 · #22

---

## Resumen

Sprint 3 implementa el flujo de revision de contenidos: comentarios generales sobre el item, anotaciones visuales tipo pin sobre imagenes (estilo Figma), y la vista Vue completa de revision con gestion de transiciones de estado.

---

## #19 — Comments API

### Modelo

**`Comment`** (`app/Models/Comment.php`)
- `$fillable`: content_item_id, user_id, body
- Relaciones: `contentItem()`, `user()`

### Endpoints

| Metodo | Ruta | Rol | Descripcion |
|---|---|---|---|
| GET | `/api/content-items/{id}/comments` | agency, clinic | Lista comentarios del item |
| POST | `/api/content-items/{id}/comments` | agency, clinic | Crea un comentario |
| DELETE | `/api/content-items/{id}/comments/{commentId}` | agency, clinic | Elimina (solo el propio) |

**Reglas de negocio:**
- Cualquier usuario autenticado con acceso al item puede comentar
- Solo el autor del comentario puede eliminarlo
- Los comentarios se devuelven ordenados por `created_at` ASC

---

## #20 — Annotations API

### Modelo

**`Annotation`** (`app/Models/Annotation.php`)
- `$fillable`: media_asset_id, user_id, pin_number, x_position, y_position, comment
- Casts: `x_position` → float, `y_position` → float, `pin_number` → integer
- Relaciones: `mediaAsset()`, `user()`

### Coordenadas

Las posiciones se almacenan como **porcentaje** (0–100) relativo al ancho/alto de la imagen. Esto hace las anotaciones independientes del tamano de visualizacion.

### Numeracion de pines

`pin_number` se asigna automaticamente en el backend como `MAX(pin_number) + 1` dentro del mismo `media_asset_id`.

### Endpoints

| Metodo | Ruta | Rol | Descripcion |
|---|---|---|---|
| GET | `/api/media-assets/{id}/annotations` | agency, clinic | Lista anotaciones del asset |
| POST | `/api/media-assets/{id}/annotations` | agency, clinic | Crea una anotacion |
| DELETE | `/api/media-assets/{id}/annotations/{annotationId}` | agency, clinic | Elimina (solo el propio) |

**Body POST:**
```json
{
  "x_position": 34.50,
  "y_position": 67.20,
  "comment": "Cambiar el logo por la version actualizada"
}
```

---

## #21 — Vue: Pagina de revision

**Archivo:** `frontend/src/views/ContentReviewView.vue`

### Funcionalidades

- **Header:** titulo del item, badge de estado, boton volver, acciones contextuales (editar/eliminar segun rol y estado)
- **Transiciones de estado:** botones disponibles segun rol y estado actual, delegan al modal de confirmacion (S4-#30)
- **Assets:** seccion principal (2/3) con visualizacion de imagenes (con AnnotationLayer), videos y documentos
- **Comentarios:** panel lateral (1/3) con lista y formulario de nuevo comentario
- **Historial de estados:** timeline visual (S4-#31) al pie de la seccion de assets

### Logica de transiciones disponibles

```js
// agency
if (status === 'draft' || status === 'in_correction')
  → [{ status: 'ready_for_review', label: 'Enviar a revision' }]

// clinic
if (status === 'ready_for_review')
  → [Aprobar, Pedir correccion, Rechazar]
```

### Carga de datos

```js
// Carga paralela al montar
const [itemData, commentsData] = await Promise.all([
  store.fetchItem(id),
  store.fetchComments(id),
])
```

Las anotaciones se pasan via prop `initialAnnotations` desde los `media_assets` del item para evitar llamadas N+1.

---

## #22 — Vue: Annotation pins (AnnotationLayer)

**Archivo:** `frontend/src/components/AnnotationLayer.vue`

### Descripcion

Componente que se superpone sobre una imagen y permite crear, visualizar y eliminar anotaciones tipo pin, inspirado en la funcionalidad de comentarios de Figma.

### Props

| Prop | Tipo | Descripcion |
|---|---|---|
| `asset` | Object | MediaAsset con `id` y `file_path` |
| `readonly` | Boolean | Desactiva creacion/eliminacion (default: false) |
| `initialAnnotations` | Array\|null | Precarga anotaciones desde el padre. Si es null, las carga via API |

### Flujo de creacion de pin

1. Usuario hace clic en la imagen → se calcula posicion en % (x, y)
2. Aparece un pin punteado provisional con formulario de texto
3. Al guardar → `POST /api/media-assets/{id}/annotations`
4. El pin se aniade al array local sin recargar toda la pagina

### Posicionamiento del popover

El popover se abre en direccion opuesta al borde mas cercano para no salirse de la imagen:

```js
top:   pin.y_position > 55 ? 'auto' : '100%'   // abre hacia arriba si esta abajo
left:  pin.x_position > 60 ? 'auto' : '50%'    // abre hacia izquierda si esta a la derecha
```

### Readonly automatico

El componente se pasa `readonly` cuando el item esta en estado `approved` o `rejected`, impidiendo nuevas anotaciones en contenido ya cerrado.

---

## Cambios de infraestructura incluidos en este sprint

| Cambio | Archivo | Motivo |
|---|---|---|
| Disco `local` → `public` en MediaAsset | `MediaAssetController.php` | Los assets deben ser accesibles por URL publica `/storage/...` |
| `client_max_body_size 100M` | `docker/nginx/default.conf` | Permitir subida de archivos grandes |
| OPcache + uploads.ini | `docker/php/Dockerfile` | Rendimiento y limites de subida |
| `createdItem` ref en ContentFormView | `ContentFormView.vue` | Evitar duplicados si falla el upload tras crear el item |
| Eager load `mediaAssets.annotations.user` | `ContentItemController.php` | Cargar anotaciones junto al item para evitar N+1 |
| Entrypoint con cache warmup | `docker/php/entrypoint.sh` | Cachear config/routes/events automaticamente al arrancar el contenedor |

---

## Estructura de archivos creados/modificados

```
backend/
  app/
    Http/Controllers/Api/
      CommentController.php
      AnnotationController.php
      ContentItemController.php    <- eager load anotaciones
      MediaAssetController.php     <- disco local -> public
    Models/
      Comment.php
      Annotation.php
  routes/
    api.php                        <- nuevas rutas S3

docker/
  php/
    entrypoint.sh                  <- cache warmup al arrancar
    uploads.ini
    opcache.ini
    Dockerfile                     <- opcache + entrypoint
  nginx/
    default.conf                   <- client_max_body_size 100M

frontend/
  src/
    components/
      AnnotationLayer.vue          <- NUEVO: pins sobre imagenes
    views/
      ContentReviewView.vue        <- NUEVO: pagina de revision completa
      ContentFormView.vue          <- fix createdItem
    stores/
      content.js                   <- metodos comments + annotations
```
