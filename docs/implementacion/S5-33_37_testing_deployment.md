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

## Correcciones y mejoras post-despliegue (27/04/2026)

Durante las pruebas reales con usuarios en produccion se detectaron y resolvieron tres problemas.

### [S5-16] — Fix 500 en transicion de estado

**Problema:** Al cambiar el estado de un contenido (ej. enviar a revision), la API devolvía un error 500 en lugar de confirmar el cambio.

**Causa raiz:** La transicion se guardaba correctamente en la base de datos, pero a continuacion el sistema intentaba enviar una notificacion por email via SMTP (`smtp.gmail.com:587`). OVH bloquea los puertos SMTP salientes (25, 465, 587) en sus VPS, lo que provocaba un timeout de 60 segundos y una excepcion no capturada que interrumpía la respuesta HTTP.

**Solucion:** Envolver la llamada a `notifyStatusChange()` en un bloque `try/catch` en `ContentStateController`. Si el envío de email falla, se registra un aviso en el log pero la transicion se completa correctamente y el cliente recibe una respuesta 200.

```php
// ContentStateController.php
try {
    $this->notifyStatusChange($contentItem, $fromStatus, $toStatus);
} catch (\Exception $e) {
    \Log::warning('Email notification failed after transition', [
        'content_item_id' => $contentItem->id,
        'error'           => $e->getMessage(),
    ]);
}
```

**Leccion:** En sistemas que envian emails en respuesta a acciones de usuario, el envio nunca debe bloquear la respuesta HTTP. La solucion correcta a largo plazo es usar una cola de trabajos (`php artisan queue:work`) para enviar el email de forma asincrona.

---

### [S5-17] — Integracion de Resend como proveedor de email

**Problema:** OVH bloquea los puertos SMTP salientes, por lo que cualquier configuracion SMTP convencional (Gmail, Mailgun SMTP) falla con timeout.

**Solucion:** Sustituir el driver SMTP por **Resend**, un servicio de email transaccional que expone una API HTTP (puerto 443, nunca bloqueado). Laravel integra Resend nativamente a traves del paquete oficial.

**Instalacion:**

```bash
composer require resend/resend-laravel
```

**Configuracion del dominio en Resend:**

Para enviar desde `noreply@contentio.tech` se requiere verificar la propiedad del dominio añadiendo registros DNS en OVH:

| Tipo | Nombre | Proposito |
|---|---|---|
| TXT | `resend._domainkey` | Firma DKIM — autentica el origen del email |
| MX | `send` | Registro de rebote para Amazon SES (infraestructura de Resend) |
| TXT | `send` | Registro SPF — autoriza el envio desde AWS |
| TXT | `_dmarc` | Politica DMARC — indica a los receptores como tratar emails no autenticados |

**Variables de entorno en produccion (`.env`):**

```env
MAIL_MAILER=resend
RESEND_API_KEY=re_xxxxxxxxxxxx
MAIL_FROM_ADDRESS=noreply@contentio.tech
MAIL_FROM_NAME="Contentio"
```

**Resultado:** Los emails de notificacion (cambios de estado del contenido) llegan correctamente a las bandejas Gmail de los usuarios de clinica y agencia. No es necesario tener un buzon de entrada en `@contentio.tech` — el dominio solo se usa como remitente.

**Diferencia clave SMTP vs API HTTP:**

| | SMTP | Resend HTTP |
|---|---|---|
| Puerto | 465 / 587 (bloqueado por OVH) | 443 (HTTPS, siempre abierto) |
| Autenticacion | Usuario + contrasena | API Key en cabecera HTTP |
| Integracion Laravel | Driver `smtp` | Driver `resend` (paquete oficial) |

---

### [S5-18] — Mostrar fecha de vencimiento en listado y visor

**Problema:** El campo `deadline_at` se definía al crear el contenido pero no se mostraba en ninguna vista del frontend.

**Cambios realizados:**

- **`ContentReviewView.vue`** — se añade la fecha de vencimiento en el encabezado, junto al nombre de la clinica, con un icono de calendario. Solo visible si el campo tiene valor.

- **`ContentListView.vue`** — en la parte inferior de cada tarjeta se muestra la fecha de vencimiento (sustituyendo `created_at`, menos relevante para el usuario). Solo visible si el campo tiene valor.

En ambos casos se reutiliza la funcion `formatDate()` ya existente, que formatea la fecha en locale `es-ES` con dia, mes, año, hora y minuto.

---

### Mejora: `update.sh` incluye `composer install`

El script de actualizacion en produccion no ejecutaba `composer install` tras el `git pull`, lo que impedía que nuevos paquetes PHP se instalaran automaticamente al desplegar. Se añadio el paso correspondiente:

```bash
# Instalar dependencias PHP (por si hay paquetes nuevos en composer.json)
$COMPOSE run --rm --no-deps php composer install --no-dev --optimize-autoloader --no-interaction
```

Esto garantiza que cualquier paquete añadido al `composer.json` en el repositorio queda disponible tras ejecutar `bash update.sh`, sin intervención manual en el servidor.

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
