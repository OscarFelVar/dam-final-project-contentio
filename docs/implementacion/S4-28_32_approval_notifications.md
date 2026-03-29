# Sprint 4 — Approval & Notifications

**Rama:** `feature/S4-approval` → merge a `develop` previsto 06/04/2026
**Issues:** #28 · #29 · #30 · #31 · #32

---

## Resumen

Sprint 4 completa el flujo de aprobacion: interfaz de confirmacion modal antes de ejecutar transiciones de estado, timeline visual del historial, mejoras al dashboard con alertas de accion requerida y distribucion de estados, y notificaciones por email automaticas al cambiar el estado de un contenido.

---

## #28 — Approve/Reject endpoints

Los endpoints de transicion estaban implementados desde S3 (`ContentStateController`). Este issue verifica que las transiciones `approved`, `rejected` e `in_correction` funcionan correctamente con el nuevo flujo de confirmacion modal del frontend.

No requirio cambios en el backend.

---

## #29 — Email notifications

### Arquitectura

Se utiliza el sistema de **Laravel Notifications** con el canal `mail`. En desarrollo el driver es `log` (los emails se escriben en `storage/logs/laravel.log`).

### Clase de notificacion

**`ContentStatusChanged`** (`app/Notifications/ContentStatusChanged.php`)

```php
public function __construct(
    public readonly ContentItem $contentItem,
    public readonly string $fromStatus,
    public readonly string $toStatus,
) {}
```

### Logica de envio

| Transicion | Destinatario | Como |
|---|---|---|
| `* → ready_for_review` | Clinica | `Notification::route('mail', $clinic->contact_email)` |
| `* → approved / rejected / in_correction` | Agencia (usuario que creo el item) | `$contentItem->createdBy->notify(...)` |

Se usa `Notification::route()` para la clinica porque no existe un `User` de la clinica — la clinica solo tiene un `contact_email` en su modelo.

### Asuntos y contenido de los emails

| Estado destino | Asunto | Dirigido a |
|---|---|---|
| `ready_for_review` | `[Contentio] Nuevo contenido pendiente de revision` | Clinica |
| `approved` | `[Contentio] Contenido aprobado: {titulo}` | Agencia |
| `in_correction` | `[Contentio] Correcciones solicitadas: {titulo}` | Agencia |
| `rejected` | `[Contentio] Contenido rechazado: {titulo}` | Agencia |

Todos los emails incluyen un boton de accion con enlace directo a la pagina de revision del contenido.

### URL del frontend en los emails

Se añadio `FRONTEND_URL` al `.env` y `config/app.php` para construir los enlaces:

```php
$url = config('app.frontend_url') . "/content-items/{$item->id}/review";
```

### Verificar emails en desarrollo

```bash
docker compose exec php tail -f storage/logs/laravel.log
```

---

## #30 — Vue: Approval UI + Modal

### Componente

**`ApprovalModal.vue`** (`frontend/src/components/ApprovalModal.vue`)

Modal de confirmacion que se muestra antes de ejecutar cualquier transicion de estado. Sustituye la ejecucion directa al clic del boton.

### Props y eventos

| Prop/Evento | Tipo | Descripcion |
|---|---|---|
| `transition` | Object\|null | `{ status, label, style }`. Si es null, el modal no se muestra |
| `itemTitle` | String | Titulo del item, se muestra en el modal |
| `@confirm` | `(note: string\|null)` | Se emite al confirmar, con la nota opcional |
| `@cancel` | — | Se emite al cancelar o clicar el backdrop |

### Funcionalidades

- Icono y colores especificos por tipo de accion (verde aprobacion, naranja correccion, rojo rechazo, marron envio a revision)
- Textarea de nota opcional — visible solo en `in_correction` y `rejected`
- La nota se publica automaticamente como **comentario** al confirmar la transicion
- Animaciones de entrada/salida con `<Transition>` de Vue
- Cierra al clicar el backdrop

### Integracion en ContentReviewView

```js
// Antes: click directo
@click="doTransition(t.status)"

// Ahora: abre modal, confirmar ejecuta la transicion
@click="openTransitionModal(t)"

async function confirmTransition(note) {
  item.value = await store.transition(id, status)
  if (note) {
    const comment = await store.addComment(id, note)
    comments.value.push(comment)
  }
}
```

---

## #31 — Vue: State history timeline

### Componente

**`StateTimeline.vue`** (`frontend/src/components/StateTimeline.vue`)

Reemplaza la lista plana de historial de estados en `ContentReviewView` por una timeline vertical con linea conectora.

### Props

| Prop | Tipo | Descripcion |
|---|---|---|
| `histories` | Array | Array de StateHistory con relacion `user` cargada |

### Estructura visual

```
●  Borrador → En revision     Oscar F.     14/03 · 10:23
│
●  En revision → Aprobado     Clinica Demo  22/03 · 16:45
```

- Punto de color segun `to_status`
- Linea vertical entre puntos (oculta si solo hay 1 entrada)
- Badges `from_status → to_status` con colores consistentes con el resto de la app
- Autor y fecha en cada entrada

---

## #32 — Vue: Dashboard overview

### Mejoras en `DashboardView.vue`

#### Alerta de accion requerida

Banner visible solo cuando hay items que requieren atencion del usuario actual:

- **Agency:** items en `draft` o `in_correction`
- **Clinic:** items en `ready_for_review`

Incluye contador, descripcion contextual segun rol y enlace directo al listado.

#### Barra de distribucion de estados

Barra segmentada que muestra la proporcion visual de items por estado, con leyenda de colores. Solo visible si `total > 0`.

```
[████░░░░░░░░░░░░]  Borrador 4  En revision 1  Aprobado 2
```

#### Mejoras en actividad reciente

- Badge de estado en lugar del punto de color
- Enlace "Ver" lleva directamente a `/content-items/:id/review` en lugar de a la lista

---

## Estructura de archivos creados/modificados

```
backend/
  app/
    Http/Controllers/Api/
      ContentStateController.php   <- notifyStatusChange()
    Notifications/
      ContentStatusChanged.php     <- NUEVO
  config/
    app.php                        <- frontend_url
  .env                             <- FRONTEND_URL

frontend/
  src/
    components/
      ApprovalModal.vue            <- NUEVO: modal de confirmacion
      StateTimeline.vue            <- NUEVO: timeline visual
    views/
      ContentReviewView.vue        <- modal + timeline
      DashboardView.vue            <- alerta accion + distribucion
```
