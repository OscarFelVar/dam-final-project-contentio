# Anexo — Diccionario de Datos
## CONTENTIO — Base de Datos PostgreSQL

> Este diccionario describe la estructura completa de la base de datos relacional de CONTENTIO.
> Motor: **PostgreSQL 16** · ORM: **Eloquent (Laravel 11)**

---

## Índice de tablas

| Tabla | Descripción |
|---|---|
| [users](#1-users) | Usuarios del sistema (agencia y clientes) |
| [clinics](#2-clinics) | Clínicas estéticas (clientes de la agencia) |
| [clinic_user](#3-clinic_user) | Relación N:M entre clínicas y usuarios cliente |
| [content_items](#4-content_items) | Contenidos digitales (reels, posts, stories) |
| [media_assets](#5-media_assets) | Archivos multimedia asociados a un contenido |
| [comments](#6-comments) | Comentarios generales sobre un contenido |
| [annotations](#7-annotations) | Anotaciones visuales con pin sobre el archivo |
| [state_histories](#8-state_histories) | Historial de cambios de estado de un contenido |

---

## Convenciones

| Símbolo | Significado |
|---|---|
| PK | Clave primaria (Primary Key) |
| FK | Clave foránea (Foreign Key) |
| UK | Valor único en la tabla (Unique Key) |
| NN | No nulo (NOT NULL) |
| AUTO | Autoincremental (generado por la base de datos) |

---

## 1. users

**Propósito:** Almacena todos los usuarios del sistema. Un usuario puede ser administrador de la agencia, creador de contenidos o cliente (clínica).

**Relaciones:**
- Un usuario puede crear múltiples clínicas (`clinics`)
- Un usuario puede estar asociado a múltiples clínicas como cliente (`clinic_user`)
- Un usuario puede crear múltiples contenidos (`content_items`)
- Un usuario puede escribir múltiples comentarios (`comments`) y anotaciones (`annotations`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único del usuario | Entero positivo |
| `name` | `VARCHAR(255)` | NN | Nombre completo del usuario | Texto libre |
| `email` | `VARCHAR(255)` | NN, UK | Correo electrónico — usado como login | Formato email válido |
| `password` | `VARCHAR(255)` | NN | Hash de la contraseña (bcrypt) | Hash generado por Laravel |
| `role` | `ENUM` | NN | Rol del usuario en el sistema | `admin` · `creator` · `client` |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de creación del registro | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

**Descripción de roles:**

| Rol | Descripción |
|---|---|
| `admin` | Administrador de la agencia. Gestiona proyectos, usuarios y toda la plataforma |
| `creator` | Miembro de la agencia. Puede subir y gestionar contenidos |
| `client` | Usuario de la clínica. Solo puede revisar, comentar y aprobar/rechazar |

---

## 2. clinics

**Propósito:** Representa a las clínicas estéticas que son clientes de la agencia. Cada clínica agrupa los proyectos y contenidos que le pertenecen.

**Relaciones:**
- Pertenece a un usuario creador (`users`)
- Puede tener múltiples usuarios cliente asignados (`clinic_user`)
- Puede tener múltiples contenidos (`content_items`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único de la clínica | Entero positivo |
| `name` | `VARCHAR(255)` | NN | Nombre de la clínica | Texto libre |
| `description` | `TEXT` | NULL | Descripción o notas sobre la clínica | Texto libre opcional |
| `contact_email` | `VARCHAR(255)` | NULL | Email de contacto principal de la clínica | Formato email válido |
| `user_id` | `BIGINT` | FK → users.id, NN | Usuario (admin/creator) que creó la clínica | ID existente en users |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de creación | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

---

## 3. clinic_user

**Propósito:** Tabla pivot que resuelve la relación muchos a muchos entre clínicas y usuarios con rol `client`. Permite que una clínica tenga múltiples usuarios revisores y que un usuario pueda revisar contenidos de varias clínicas.

**Relaciones:**
- Referencia a `clinics.id`
- Referencia a `users.id` (solo usuarios con rol `client`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `clinic_id` | `BIGINT` | FK → clinics.id, NN | Clínica a la que se asocia el usuario | ID existente en clinics |
| `user_id` | `BIGINT` | FK → users.id, NN | Usuario cliente asociado a la clínica | ID existente en users |
| `created_at` | `TIMESTAMP` | NN | Fecha en que se realizó la asociación | Fecha UTC |

**Clave primaria compuesta:** (`clinic_id`, `user_id`)

---

## 4. content_items

**Propósito:** Tabla central del sistema. Representa cada contenido digital (reel, post o story) que la agencia crea para una clínica. Gestiona el ciclo de vida completo del contenido mediante un campo de estado.

**Relaciones:**
- Pertenece a una clínica (`clinics`)
- Creado por un usuario (`users`)
- Puede tener múltiples archivos multimedia (`media_assets`)
- Puede tener múltiples comentarios (`comments`)
- Registra su historial de estados (`state_histories`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único del contenido | Entero positivo |
| `clinic_id` | `BIGINT` | FK → clinics.id, NN | Clínica a la que pertenece el contenido | ID existente en clinics |
| `created_by` | `BIGINT` | FK → users.id, NN | Usuario que creó el contenido | ID existente en users |
| `title` | `VARCHAR(255)` | NN | Título o nombre descriptivo del contenido | Texto libre |
| `type` | `ENUM` | NN | Tipo de contenido de redes sociales | `reel` · `post` · `story` |
| `status` | `ENUM` | NN | Estado actual en el flujo de revisión | Ver tabla de estados |
| `deadline_at` | `TIMESTAMP` | NULL | Fecha límite para la aprobación del contenido | Fecha UTC opcional |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de creación | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

**Ciclo de vida del campo `status`:**

| Estado | Descripción | Quién lo activa |
|---|---|---|
| `draft` | Borrador — recién creado, sin publicar para revisión | Sistema (al crear) |
| `ready_for_review` | Listo — la agencia lo publica y el cliente puede revisarlo | Agencia |
| `in_correction` | En corrección — el cliente lo rechazó, la agencia está corrigiendo | Sistema (tras rechazo) |
| `approved` | Aprobado — el cliente dio su visto bueno | Cliente |
| `rejected` | Rechazado — el cliente lo rechazó definitivamente | Cliente |

---

## 5. media_assets

**Propósito:** Almacena los metadatos de los archivos multimedia (imágenes y vídeos) subidos a CONTENTIO. El archivo físico se almacena en disco mediante Laravel Storage; esta tabla guarda únicamente la referencia y los metadatos.

**Relaciones:**
- Pertenece a un contenido (`content_items`)
- Puede tener múltiples anotaciones visuales (`annotations`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único del archivo | Entero positivo |
| `content_item_id` | `BIGINT` | FK → content_items.id, NN | Contenido al que pertenece el archivo | ID existente en content_items |
| `file_path` | `VARCHAR(500)` | NN | Ruta relativa del archivo en el sistema de almacenamiento | Ej: `uploads/clinics/1/reel_abc.mp4` |
| `file_name` | `VARCHAR(255)` | NN | Nombre original del archivo subido por el usuario | Texto, ej: `reel_enero.mp4` |
| `mime_type` | `VARCHAR(100)` | NN | Tipo MIME del archivo | `image/jpeg` · `image/png` · `video/mp4` |
| `size` | `BIGINT` | NN | Tamaño del archivo en bytes | Entero positivo |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de subida | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

---

## 6. comments

**Propósito:** Almacena los comentarios generales que los usuarios (principalmente clientes) escriben sobre un contenido. Son comentarios de texto libre sin posición visual, a diferencia de las anotaciones con pin.

**Relaciones:**
- Pertenece a un contenido (`content_items`)
- Escrito por un usuario (`users`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único del comentario | Entero positivo |
| `content_item_id` | `BIGINT` | FK → content_items.id, NN | Contenido sobre el que se comenta | ID existente en content_items |
| `user_id` | `BIGINT` | FK → users.id, NN | Usuario autor del comentario | ID existente en users |
| `body` | `TEXT` | NN | Texto completo del comentario | Texto libre, mínimo 1 carácter |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de creación | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

---

## 7. annotations

**Propósito:** Almacena las anotaciones visuales con pin que los usuarios colocan sobre un archivo multimedia. Cada anotación tiene una posición X/Y (en porcentaje relativo al tamaño de la imagen) y un texto asociado. Es la funcionalidad diferencial de CONTENTIO.

**Relaciones:**
- Pertenece a un archivo multimedia (`media_assets`)
- Creada por un usuario (`users`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único de la anotación | Entero positivo |
| `media_asset_id` | `BIGINT` | FK → media_assets.id, NN | Archivo sobre el que se coloca el pin | ID existente en media_assets |
| `user_id` | `BIGINT` | FK → users.id, NN | Usuario que creó la anotación | ID existente en users |
| `pin_number` | `INTEGER` | NN | Número secuencial del pin en el contenido | Entero positivo, empieza en 1 |
| `x_position` | `DECIMAL(5,2)` | NN | Coordenada X del pin en porcentaje sobre el ancho | 0.00 a 100.00 |
| `y_position` | `DECIMAL(5,2)` | NN | Coordenada Y del pin en porcentaje sobre el alto | 0.00 a 100.00 |
| `comment` | `TEXT` | NN | Texto de la anotación asociada al pin | Texto libre |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora de creación | Fecha UTC |
| `updated_at` | `TIMESTAMP` | NN | Fecha y hora de última modificación | Fecha UTC |

**Nota técnica:** Las coordenadas `x_position` e `y_position` se almacenan como porcentaje (0-100) en lugar de píxeles para que las anotaciones sean independientes de la resolución de pantalla del revisor.

---

## 8. state_histories

**Propósito:** Registra cada cambio de estado que sufre un contenido a lo largo de su ciclo de vida. Proporciona trazabilidad completa: se puede saber quién cambió el estado, de qué a qué, y cuándo. Es la tabla de auditoría del flujo de aprobación.

**Relaciones:**
- Pertenece a un contenido (`content_items`)
- Ejecutado por un usuario (`users`)

| Columna | Tipo | Restricciones | Descripción | Valores posibles |
|---|---|---|---|---|
| `id` | `BIGINT` | PK, NN, AUTO | Identificador único del registro histórico | Entero positivo |
| `content_item_id` | `BIGINT` | FK → content_items.id, NN | Contenido cuyo estado cambió | ID existente en content_items |
| `user_id` | `BIGINT` | FK → users.id, NN | Usuario que realizó el cambio de estado | ID existente en users |
| `from_status` | `VARCHAR(50)` | NN | Estado anterior al cambio | Valores del ENUM de content_items.status |
| `to_status` | `VARCHAR(50)` | NN | Estado nuevo tras el cambio | Valores del ENUM de content_items.status |
| `created_at` | `TIMESTAMP` | NN | Fecha y hora exacta del cambio de estado | Fecha UTC |

**Nota:** Esta tabla es de solo inserción — los registros históricos nunca se modifican ni eliminan, garantizando la integridad de la auditoría.

---

## Diagrama de relaciones (resumen)

```
users ──────────────────────────────────────────────────────────┐
  │ crea                                                         │
  ▼                                                              │
clinics ◄──── clinic_user ────► users (client)                  │
  │ tiene                                                        │
  ▼                                                              │
content_items ◄─── created_by ─────────────────────────────── users
  │                     │                    │
  │ contiene            │ registra           │ tiene
  ▼                     ▼                    ▼
media_assets      state_histories        comments
  │ tiene
  ▼
annotations
```

---

## Políticas de integridad referencial

| Relación | ON DELETE | ON UPDATE |
|---|---|---|
| `clinics.user_id` → `users.id` | RESTRICT | CASCADE |
| `clinic_user.clinic_id` → `clinics.id` | CASCADE | CASCADE |
| `clinic_user.user_id` → `users.id` | CASCADE | CASCADE |
| `content_items.clinic_id` → `clinics.id` | RESTRICT | CASCADE |
| `content_items.created_by` → `users.id` | RESTRICT | CASCADE |
| `media_assets.content_item_id` → `content_items.id` | CASCADE | CASCADE |
| `comments.content_item_id` → `content_items.id` | CASCADE | CASCADE |
| `comments.user_id` → `users.id` | RESTRICT | CASCADE |
| `annotations.media_asset_id` → `media_assets.id` | CASCADE | CASCADE |
| `annotations.user_id` → `users.id` | RESTRICT | CASCADE |
| `state_histories.content_item_id` → `content_items.id` | RESTRICT | CASCADE |
| `state_histories.user_id` → `users.id` | RESTRICT | CASCADE |
