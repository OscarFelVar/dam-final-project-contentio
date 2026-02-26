# S0-02 — Configuración de la infraestructura con Docker

**Sprint:** 0 — Setup
**Fecha:** 26/02/2026
**Estado:** Completado ✅

---

## Contexto y motivación

Una de las primeras decisiones de cualquier proyecto web es cómo gestionar el entorno de desarrollo. La opción clásica, instalar PHP, PostgreSQL y Nginx directamente en el sistema operativo, tiene un problema evidente: genera dependencias implícitas con la máquina del desarrollador. Lo que funciona en un Windows con PHP 8.4 puede no arrancar en un Ubuntu con PHP 8.1, y la puesta en marcha en el servidor de producción se convierte en un proceso manual lleno de pequeñas diferencias.

Para CONTENTIO se optó por **Docker Compose** como solución a este problema. La idea es simple: en lugar de instalar el software en la máquina, se describe en un fichero qué servicios se necesitan, con qué versiones y cómo se comunican entre sí. Cualquier máquina con Docker instalado puede levantar el entorno completo ejecutando un único comando.

---

## Arquitectura de servicios

El entorno está formado por cuatro contenedores que conviven en una red virtual privada llamada `contentio`:

```
Navegador
    │
    │ HTTP  →  localhost:8080
    ▼
┌─────────┐
│  nginx  │   Servidor web — recibe las peticiones HTTP
└─────────┘
    │
    │ FastCGI  →  php:9000
    ▼
┌─────────┐       ┌──────────────┐
│   php   │──────►│   postgres   │
│  (FPM)  │  SQL  │   :5432      │
└─────────┘       └──────────────┘

┌─────────┐
│  node   │   Servidor de desarrollo Vue.js  →  localhost:5173
└─────────┘
```

Nginx y PHP no comparten proceso — son contenedores separados que se comunican mediante el protocolo **FastCGI**. Nginx recibe la petición HTTP, identifica que el recurso solicitado requiere ejecución PHP, y se lo pasa a PHP-FPM a través de una conexión TCP interna. PHP-FPM ejecuta el código, devuelve la respuesta, y Nginx la reenvía al cliente. Esta separación es intencionada: cada pieza tiene una responsabilidad única y puede escalarse o sustituirse de forma independiente.

El contenedor `node` corre el servidor de desarrollo de Vite de forma separada. En desarrollo, el frontend en `localhost:5173` se comunica con el backend en `localhost:8080` a través de un proxy configurado en Vite. En producción este contenedor no existe: Nginx sirve directamente el build estático generado por `npm run build`.

---

## Ficheros creados

### `docker-compose.yml`

Es el fichero central del sistema. Define los cuatro servicios, los volúmenes de datos y la red compartida.

Algunas decisiones concretas que merece la pena justificar:

**`depends_on` con `condition: service_healthy` en el servicio PHP.**
PostgreSQL necesita unos segundos para inicializarse antes de aceptar conexiones. Si PHP-FPM arranca antes, Laravel intentará conectarse a la base de datos y fallará. Para evitarlo, se configuró un `healthcheck` en el servicio de Postgres que ejecuta `pg_isready` cada cinco segundos. PHP no arranca hasta que ese check pasa satisfactoriamente.

**Volumen nombrado `pgdata` para PostgreSQL.**
Los datos de la base de datos se guardan en un volumen gestionado por Docker (`pgdata`), no en una carpeta del proyecto. Esto tiene una razón práctica: en Windows, montar una carpeta local como volumen para PostgreSQL puede generar problemas de permisos y de rendimiento con el sistema de ficheros. Con un volumen nombrado Docker gestiona el almacenamiento internamente, y los datos persisten aunque el contenedor se elimine con `docker compose down`.

**Puerto 5432 expuesto en Postgres.**
Aunque PostgreSQL solo necesita ser accesible desde PHP dentro de la red interna, exponer el puerto en la máquina anfitriona permite conectarse con herramientas externas como DBeaver o TablePlus durante el desarrollo, sin necesidad de entrar al contenedor.

---

### `docker/php/Dockerfile`

La imagen oficial de PHP no incluye ni las extensiones para PostgreSQL ni Composer, por lo que se construye una imagen personalizada.

Se eligió la variante `php:8.3-fpm-alpine` por dos motivos. Alpine Linux es una distribución minimalista que reduce el tamaño de la imagen final de forma considerable respecto a las variantes basadas en Debian. Y la versión FPM (FastCGI Process Manager) es la correcta para este uso: en lugar de ejecutar PHP como proceso CLI de un solo uso, FPM mantiene un pool de workers PHP en escucha permanente, lo que es mucho más eficiente para servir peticiones web.

Las extensiones instaladas son las que requiere Laravel 11 según su documentación oficial:

| Extensión | Motivo |
|-----------|--------|
| `pdo` + `pdo_pgsql` | Conexión a PostgreSQL a través de la capa de abstracción PDO |
| `mbstring` | Manipulación de cadenas multibyte, imprescindible para UTF-8 |
| `zip` | Necesario para que Composer pueda instalar paquetes |
| `bcmath` | Aritmética de precisión arbitraria, requerida por Laravel |
| `xml` + `dom` | Procesado de XML, usado internamente por varios componentes |
| `fileinfo` | Detección de tipos MIME, necesario para Laravel Storage |
| `pcntl` | Control de procesos Unix, requerido por las queues y workers |

Para instalar Composer se utilizó una técnica llamada **multi-stage build**: en lugar de ejecutar el script de instalación oficial (que descarga un fichero de internet en tiempo de build), se copia directamente el ejecutable desde la imagen oficial de Composer. Es más limpio, más predecible y no introduce dependencias de red en el proceso de construcción.

```dockerfile
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer
```

---

### `docker/nginx/default.conf`

La configuración de Nginx sigue el patrón estándar recomendado para Laravel. El `root` apunta exclusivamente al directorio `public/`, lo que garantiza que el código de la aplicación, los ficheros de configuración y el `.env` no son nunca accesibles desde el exterior.

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

Esta directiva es la que hace posible el enrutamiento de Laravel. Cuando llega una petición a `/dashboard`, Nginx comprueba si existe un fichero con ese nombre en `public/`. Como no existe, lo manda a `index.php`, que es el único punto de entrada de Laravel. Desde ahí, el router del framework decide qué controlador responde.

La directiva que bloquea el acceso a ficheros ocultos es también importante:

```nginx
location ~ /\.(?!well-known).* {
    deny all;
}
```

Impide que rutas como `/.env` o `/.git` sean accesibles desde el navegador. La excepción `well-known` es necesaria para la renovación automática de certificados SSL con Let's Encrypt en producción.

---

## Red interna

Todos los servicios se declaran en la red `contentio` de tipo `bridge`. Esto crea una red virtual privada donde los contenedores se resuelven entre sí por nombre. Así, en la configuración de Nginx se puede escribir `fastcgi_pass php:9000` en lugar de una dirección IP, y en el `.env` de Laravel la conexión a la base de datos usa `DB_HOST=postgres`. Docker hace la resolución DNS interna.

---

## Lo que viene a continuación

Con la infraestructura definida, el siguiente paso es instalar Laravel dentro del contenedor PHP (S0-03) y Vue.js dentro del contenedor Node (S0-04). Los servicios `php` y `node` arrancarán sin errores cuando haya código en `backend/` y `frontend/` respectivamente. Por ahora, `docker compose up` levanta Nginx y Postgres correctamente; los otros dos servicios muestran errores esperados que desaparecerán en los siguientes sprints.

---

## Comandos de referencia

```bash
# Construir las imágenes y levantar todos los servicios
docker compose up --build

# Levantar en segundo plano
docker compose up -d --build

# Ver estado de los contenedores
docker compose ps

# Ver logs de un servicio concreto
docker compose logs php

# Parar todo (los datos de Postgres se conservan en pgdata)
docker compose down

# Parar todo Y eliminar los volúmenes (borra la base de datos)
docker compose down -v
```
