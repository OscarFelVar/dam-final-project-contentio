# S1-08/11 — Autenticación y autorización con Laravel Sanctum

**Sprint:** 1 — Authentication & Authorization
**Fecha:** 11/03/2026
**Issues:** #8 Register · #9 Login · #10 Logout · #11 Role middleware
**Estado:** Completado ✅

---

## Contexto y motivación

CONTENTIO es una SPA (Single Page Application): el frontend en Vue.js y el backend en Laravel son dos aplicaciones independientes que se comunican exclusivamente mediante una API REST. Esto descarta el sistema de sesiones tradicional de Laravel (basado en cookies de sesión del servidor), ya que el servidor no mantiene estado entre peticiones.

La solución adoptada es **autenticación basada en tokens Bearer**. Al hacer login, el servidor genera un token único que el cliente guarda y envía en cada petición posterior en la cabecera HTTP `Authorization`. El servidor verifica el token y sabe quién hace la petición sin necesidad de mantener ninguna sesión.

**Laravel Sanctum** es el paquete oficial de Laravel para este patrón. Guarda los tokens en la tabla `personal_access_tokens` de la base de datos y proporciona el middleware `auth:sanctum` para proteger rutas.

---

## Flujo de autenticación

```
Cliente (Vue)                          Servidor (Laravel)
     │                                        │
     │  POST /api/auth/register               │
     │  { name, email, password, role }  ───► │  Valida datos
     │                                        │  Crea usuario (password hasheado)
     │  { user, token }              ◄───     │  Genera token Sanctum
     │                                        │
     │  Guarda token en Pinia/localStorage    │
     │                                        │
     │  GET /api/auth/me                      │
     │  Authorization: Bearer <token>    ───► │  Verifica token en BD
     │                                        │  Carga usuario
     │  { id, name, email, role }    ◄───     │  Devuelve datos
     │                                        │
     │  POST /api/auth/logout                 │
     │  Authorization: Bearer <token>    ───► │  Elimina token de BD
     │  { message: "Logged out" }    ◄───     │  Token invalidado
```

---

## Archivos creados / modificados

| Archivo | Tipo | Descripción |
|---|---|---|
| `backend/routes/api.php` | Nuevo | Rutas de la API (Laravel 12 no lo incluye por defecto) |
| `backend/bootstrap/app.php` | Modificado | Registro de api.php y alias del middleware `role` |
| `backend/app/Http/Controllers/Api/AuthController.php` | Nuevo | Controlador con register, login, logout, me |
| `backend/app/Http/Middleware/EnsureUserHasRole.php` | Nuevo | Middleware de autorización por rol |
| `backend/app/Models/User.php` | Modificado | Añadido HasApiTokens, role en fillable |
| `backend/config/sanctum.php` | Nuevo | Configuración de Sanctum (publicada) |
| `backend/config/auth.php` | Modificado | Guard `api` con driver `sanctum` |
| `backend/database/migrations/0001_01_01_000000_create_users_table.php` | Modificado | Roles corregidos a `admin/agency/clinic` |
| `backend/database/migrations/2026_03_11_..._create_personal_access_tokens_table.php` | Nuevo | Tabla de tokens de Sanctum |
| `docker/nginx/default.conf` | Modificado | Añadido `fastcgi_param HTTP_AUTHORIZATION` |

---

## Implementación detallada

### 1. Instalación de Sanctum

```bash
docker compose exec php composer require laravel/sanctum
docker compose exec php php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
docker compose exec php php artisan migrate
```

Esto instala Sanctum, publica su configuración y crea la tabla `personal_access_tokens`.

### 2. Registro de rutas API (Laravel 12)

En Laravel 12, el archivo `routes/api.php` no existe por defecto. Hay que crearlo y declararlo en `bootstrap/app.php`:

```php
// bootstrap/app.php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    api: __DIR__.'/../routes/api.php',   // ← añadido
    ...
)
```

Laravel añade automáticamente el prefijo `/api` a todas las rutas de este archivo.

### 3. AuthController

```php
// Rutas públicas
POST /api/auth/register  → register()
POST /api/auth/login     → login()

// Rutas protegidas (requieren token válido)
POST /api/auth/logout    → logout()
GET  /api/auth/me        → me()
```

El método `register` valida los datos con reglas inline:

```php
$data = $request->validate([
    'name'     => ['required', 'string', 'max:255'],
    'email'    => ['required', 'string', 'email', 'unique:users'],
    'password' => ['required', 'confirmed', Password::min(8)],
    'role'     => ['required', 'in:agency,clinic'],
]);
```

`Password::min(8)` es una regla fluida que se puede encadenar: `.mixedCase()`, `.numbers()`, `.symbols()`, etc.

El campo `'password' => 'hashed'` en el cast del modelo User hace que Laravel hashee automáticamente el valor antes de guardarlo en BD.

### 4. Role middleware

```php
// app/Http/Middleware/EnsureUserHasRole.php
public function handle(Request $request, Closure $next, string ...$roles): Response
{
    if (! $request->user() || ! in_array($request->user()->role, $roles)) {
        return response()->json(['message' => 'Forbidden...'], 403);
    }
    return $next($request);
}
```

El parámetro `string ...$roles` (variadic) permite pasar uno o varios roles:

```php
Route::middleware(['auth:sanctum', 'role:agency'])         // un rol
Route::middleware(['auth:sanctum', 'role:agency,admin'])   // varios roles
```

Se registra como alias en `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureUserHasRole::class,
    ]);
})
```

---

## Problemas encontrados y soluciones

### Nginx no reenvía el header Authorization a PHP

**Problema:** El middleware `auth:sanctum` devolvía `401 Unauthenticated` aunque el token era válido.

**Causa:** Nginx por defecto no reenvía la cabecera `Authorization` al backend FastCGI (PHP-FPM). Por tanto, Laravel nunca recibía el token.

**Solución:** Añadir en el bloque `location ~ \.php$` del config de Nginx:

```nginx
fastcgi_param HTTP_AUTHORIZATION $http_authorization;
```

### sed en Windows vacía archivos

**Problema:** Al usar `sed -i` desde Git Bash en Windows para editar archivos del proyecto, los archivos quedaban vacíos (0 bytes).

**Causa:** Git Bash en Windows tiene comportamiento distinto con `sed -i` respecto a Linux — crea un fichero temporal y falla al renombrarlo, dejando el original vacío.

**Solución:** Usar Python para cualquier edición de archivos de configuración:

```python
content = open(r'C:/ruta/archivo.conf').read()
content = content.replace(old_string, new_string)
open(r'C:/ruta/archivo.conf', 'w', newline='\n').write(content)
```

### Enum de roles incorrecto en la migración

**Problema:** La migración original del Sprint 0 definía los roles como `['admin', 'creator', 'client']` pero el diseño de CONTENTIO usa `['admin', 'agency', 'clinic']`.

**Solución:** Corregir la migración y ejecutar `php artisan migrate:fresh` (en desarrollo no hay datos que perder).

---

## Conceptos clave aprendidos

- **401 Unauthenticated** → no hay token o el token es inválido
- **403 Forbidden** → hay token válido pero el usuario no tiene el rol requerido
- **Bearer token** → el cliente lo incluye en cada petición: `Authorization: Bearer <token>`
- **Sanctum** almacena los tokens en BD (tabla `personal_access_tokens`) — al hacer logout se borra el registro y el token queda invalidado permanentemente
- En Laravel 12, las rutas API se declaran en `bootstrap/app.php`, no en `Kernel.php`
