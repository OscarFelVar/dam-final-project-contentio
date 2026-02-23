# CONTENTIO — Guía de Wireframes para Figma
> Documento de referencia para el diseño UX/UI del MVP
> Versión 1.0 — Febrero 2026

---

## Contexto del proyecto

**CONTENTIO** es una plataforma web SaaS de revisión y aprobación de contenidos digitales entre agencias creativas y clínicas estéticas.

Permite a una agencia subir contenidos (reels, posts, stories), y al cliente (la clínica) revisarlos añadiendo comentarios visuales con pines (como Figma), para luego aprobarlos o rechazarlos con trazabilidad completa.

---

## Roles de usuario en el MVP

| Rol | Quién es | Qué puede hacer |
|---|---|---|
| **Agencia** | El equipo creativo | Crear proyectos, subir contenidos, ver feedback, gestionar usuarios |
| **Cliente** | La clínica estética | Revisar contenidos, añadir comentarios con pines, aprobar o rechazar |

> No hay registro público. Los usuarios cliente son **invitados por la agencia**.

---

## Stack tecnológico (para tener en cuenta en el diseño)

- Frontend: **Vue.js 3 + Tailwind CSS** (diseño utility-first, sin componentes Bootstrap)
- No hay app móvil en el MVP — diseñar exclusivamente para **escritorio (desktop)**
- Resolución objetivo: **1440px de ancho** como referencia principal
- Paleta, tipografía y sistema de diseño: a definir por la diseñadora

---

## Pantallas del MVP — 8 en total

### Prioridad de diseño recomendada

| Prioridad | Pantalla | Por qué |
|---|---|---|
| 🔴 Alta | Pantalla 7 — Visor/Revisor | Es el diferencial del producto. La más compleja. |
| 🔴 Alta | Pantalla 1 — Login | Primera impresión del producto |
| 🟠 Media | Pantalla 5 — Detalle de Proyecto | Ambos roles la usan constantemente |
| 🟠 Media | Pantalla 2 — Dashboard Agencia | Punto de entrada principal |
| 🟡 Normal | Pantalla 3 — Dashboard Cliente | Versión simplificada del de agencia |
| 🟡 Normal | Pantalla 4 — Lista de Proyectos | Interfaz de gestión estándar |
| 🟡 Normal | Pantalla 6 — Subir Contenido | Modal/formulario sencillo |
| 🟡 Normal | Pantalla 8 — Gestión de Usuarios | Tabla con modal de invitación |

---

## Detalle de cada pantalla

---

### Pantalla 1 — Login
**Roles:** Ambos (Agencia y Cliente)
**Descripción:** Unica pantalla pública de la aplicación. Acceso por email y contraseña.

**Elementos:**

| # | Elemento | Tipo | Notas |
|---|---|---|---|
| 1 | Logo CONTENTIO | Imagen/SVG | Centrado, parte superior |
| 2 | Campo email | Input texto | Label + placeholder |
| 3 | Campo contraseña | Input password | Con icono de mostrar/ocultar |
| 4 | Botón "Iniciar sesión" | Botón primario | Ancho completo del formulario |
| 5 | Link "¿Olvidaste tu contraseña?" | Texto enlace | Solo visual, no implementado en MVP |

**Notas de diseño:**
- Formulario centrado vertical y horizontalmente en la pantalla
- Fondo diferenciado del formulario (puede ser imagen, gradiente o color sólido)
- NO incluir botón de registro — no existe registro público

---

### Pantalla 2 — Dashboard Agencia
**Roles:** Agencia
**Descripción:** Pantalla de inicio tras el login. Visión general del estado de los proyectos.

**Layout:** Sidebar fijo a la izquierda + contenido principal a la derecha

**Elementos del Sidebar:**

| # | Elemento | Tipo |
|---|---|---|
| 1 | Logo CONTENTIO | Imagen/SVG |
| 2 | Link "Proyectos" | Item de navegación |
| 3 | Link "Usuarios" | Item de navegación |
| 4 | Nombre de usuario + avatar | Área de perfil (parte inferior del sidebar) |
| 5 | Botón "Cerrar sesión" | Botón o icono |

**Elementos del contenido principal:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Titulo "Bienvenido, [Nombre]" | Heading | Saludo personalizado |
| 2 | Tarjeta "Proyectos activos" | Tarjeta métrica | Numero grande + icono |
| 3 | Tarjeta "Pendientes de revision" | Tarjeta métrica | Numero grande + icono |
| 4 | Tarjeta "Aprobados" | Tarjeta métrica | Numero grande + icono (verde) |
| 5 | Tarjeta "Rechazados" | Tarjeta métrica | Numero grande + icono (rojo) |
| 6 | Lista "Ultima actividad" | Lista de items | Texto simple: "Cliente X aprobo Reel Y — hace 2h" |
| 7 | Botón "Nuevo proyecto" | Botón primario | Visible en la cabecera del contenido |

**Notas de diseño:**
- Las 4 tarjetas de métricas van en fila horizontal (grid 4 columnas)
- NO incluir gráficos, charts ni analytics — eso es versión futura (v3)
- La lista de actividad es texto plano, sin avatares ni interaccion

---

### Pantalla 3 — Dashboard Cliente
**Roles:** Cliente
**Descripción:** Pantalla de inicio simplificada para la clinica. Solo ve lo que le corresponde.

**Layout:** Header superior + contenido central (sin sidebar complejo)

**Elementos del Header:**

| # | Elemento | Tipo |
|---|---|---|
| 1 | Logo CONTENTIO | Imagen/SVG |
| 2 | Link "Inicio" | Navegacion |
| 3 | Link "Mis revisiones" | Navegacion |
| 4 | Nombre de usuario + avatar | Info de perfil |
| 5 | Botón "Cerrar sesión" | Botón o icono |

**Elementos del contenido principal:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | 3 tarjetas de métricas | Tarjetas | Pendientes de revisar / Aprobados / Rechazados |
| 2 | Seccion "Pendientes de revision" | Lista/Grid | Miniatura + nombre del contenido + nombre del proyecto |
| 3 | Badge de urgencia | Badge | Indica contenidos con mas de X dias sin revisar (opcional) |

**Notas de diseño:**
- Mas simple y limpio que el dashboard de agencia
- El cliente NO ve proyectos de otros clientes
- La lista de pendientes es el elemento mas importante de esta pantalla

---

### Pantalla 4 — Lista de Proyectos
**Roles:** Agencia
**Descripción:** Vista completa de todos los proyectos gestionados por la agencia.

**Layout:** Sidebar (igual que Dashboard Agencia) + contenido principal

**Elementos del contenido principal:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Titulo "Proyectos" | Heading | |
| 2 | Botón "Nuevo proyecto" | Botón primario | Esquina superior derecha |
| 3 | Input de búsqueda | Input texto | Filtrar por nombre de proyecto |
| 4 | Filtro por estado | Tabs o pills | Todos / Activos / Archivados |
| 5 | Tabla de proyectos | Tabla | Ver columnas abajo |
| 6 | Icono/botón "Ver" por fila | Icono accion | Navega al detalle del proyecto |
| 7 | Icono/botón "Archivar" por fila | Icono accion | Archiva el proyecto |

**Columnas de la tabla:**

| Columna | Contenido |
|---|---|
| Nombre | Texto con link al detalle |
| Cliente | Nombre del cliente asignado |
| Contenidos | Número total |
| Estado | Badge: Activo / Archivado |
| Fecha de creacion | DD/MM/YYYY |
| Acciones | Iconos Ver + Archivar |

**Notas de diseño:**
- No hay eliminación permanente en MVP — solo archivar
- La tabla puede tener paginacion si hay muchos proyectos

---

### Pantalla 5 — Detalle de Proyecto
**Roles:** Ambos (Agencia ve todo, Cliente solo sus proyectos asignados)
**Descripción:** Vista de un proyecto especifico con todos sus contenidos.

**Layout:** Sidebar + cabecera de proyecto + grid de contenidos

**Elementos de cabecera del proyecto:**

| # | Elemento | Tipo |
|---|---|---|
| 1 | Nombre del proyecto | Heading grande |
| 2 | Cliente asignado | Texto/badge |
| 3 | Descripcion del proyecto | Texto parrafo |
| 4 | Botón "Subir contenido" | Botón primario — **solo visible para Agencia** |

**Elementos del area de contenidos:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Filtros por estado | Tabs/pills | Todos / Pendiente / Aprobado / Rechazado |
| 2 | Grid de tarjetas de contenido | Grid 3-4 columnas | Ver detalle de tarjeta abajo |

**Tarjeta de contenido (card):**

| Elemento | Detalle |
|---|---|
| Miniatura (thumbnail) | Imagen o preview del video |
| Tipo de contenido | Badge: Post / Reel / Story |
| Nombre del contenido | Texto |
| Estado | Badge con color: Pendiente (gris) / Aprobado (verde) / Rechazado (rojo) |
| Fecha de subida | DD/MM/YYYY |
| Click en la tarjeta | Navega al Visor (Pantalla 7) |

---

### Pantalla 6 — Subir Contenido
**Roles:** Agencia
**Formato:** Modal (ventana emergente sobre el Detalle de Proyecto) o pagina nueva
**Descripción:** Formulario para que la agencia suba un nuevo contenido al proyecto.

**Elementos:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Titulo "Subir contenido" | Heading del modal | |
| 2 | Selector tipo de contenido | 3 opciones visuales (cards o radio) | Post / Reel / Story |
| 3 | Campo "Nombre o titulo" | Input texto | Obligatorio |
| 4 | Campo "Descripcion" | Textarea | Opcional |
| 5 | Zona de upload | Drag & drop | Texto: "Arrastra aqui tu archivo o haz clic para seleccionar" |
| 6 | Preview del archivo | Miniatura | Se muestra tras seleccionar el archivo |
| 7 | Botón "Subir" | Botón primario | |
| 8 | Botón "Cancelar" | Botón secundario | Cierra el modal sin guardar |

**Notas de diseño:**
- Formatos aceptados: imagenes (JPG, PNG) y video (MP4) — indicarlo en la zona de upload
- El selector de tipo puede ser 3 cards con icono representativo de cada tipo

---

### Pantalla 7 — Visor / Revisor de Contenido
**Roles:** Ambos (comportamiento diferente segun rol)
**Descripción:** LA PANTALLA MAS IMPORTANTE DEL MVP. Aqui ocurre la revision del contenido con pines visuales (como Figma) y la decision de aprobacion o rechazo.

**Layout:** Dos columnas — Visor a la izquierda (~70%) + Panel a la derecha (~30%)

---

#### Vista Agencia (lectura del feedback recibido)

**Columna izquierda — Visor:**

| # | Elemento | Detalle |
|---|---|---|
| 1 | Imagen o reproductor de video | El contenido subido, a tamano completo de la columna |
| 2 | Pines numerados sobre el contenido | Puntos con numero (1, 2, 3...) en la posicion donde el cliente hizo clic — Solo lectura |

**Columna derecha — Panel:**

| # | Elemento | Detalle |
|---|---|---|
| 1 | Nombre del contenido | Heading |
| 2 | Badge de estado | Pendiente / Aprobado / Rechazado |
| 3 | Lista de comentarios | Cada comentario muestra: numero de pin + texto del comentario + nombre del cliente + fecha |
| 4 | Seccion "Historial" | Texto: "Revisado por [Cliente] el [fecha]" / "Sin revisar aun" |
| 5 | Botón "Volver al proyecto" | Botón secundario o breadcrumb |

---

#### Vista Cliente (interactiva — aqui ocurre la magia)

**Columna izquierda — Visor interactivo:**

| # | Elemento | Detalle |
|---|---|---|
| 1 | Imagen o reproductor de video | El contenido a revisar |
| 2 | Cursor especial al pasar por encima | Indica que se puede hacer clic para anadir un pin |
| 3 | Pines numerados | Se crean al hacer clic sobre la imagen/video. Cada clic = nuevo pin numerado |
| 4 | Pin activo/seleccionado | El pin seleccionado se resalta visualmente |

**Columna derecha — Panel de revision:**

| # | Elemento | Detalle |
|---|---|---|
| 1 | Nombre del contenido | Heading |
| 2 | **Botón "Aprobar"** | Botón grande, color verde — en la parte superior del panel |
| 3 | **Botón "Rechazar"** | Botón grande, color rojo — junto al de Aprobar |
| 4 | Lista de comentarios existentes | Cada item: numero de pin + texto + fecha |
| 5 | Formulario "Nuevo comentario" | Se activa al hacer clic en el visor — muestra el numero de pin creado + Textarea para escribir el comentario + Botón "Guardar comentario" |

**Modal de rechazo (se abre al pulsar "Rechazar"):**

| # | Elemento | Detalle |
|---|---|---|
| 1 | Titulo "Motivo del rechazo" | Heading del modal |
| 2 | Textarea "Describe el motivo" | Obligatorio — no se puede confirmar sin texto |
| 3 | Botón "Confirmar rechazo" | Botón rojo |
| 4 | Botón "Cancelar" | Botón secundario — cierra el modal |

**Notas de diseño:**
- Esta es la pantalla que define el producto — dedicarle mas tiempo
- Los pines deben ser circulos pequenos con numero dentro (estilo Figma)
- Al hacer clic en un comentario del panel derecho, el pin correspondiente debe resaltarse (y viceversa)
- Los botones Aprobar/Rechazar deben ser muy visibles — son la accion final del flujo

---

### Pantalla 8 — Gestion de Usuarios
**Roles:** Agencia
**Descripcion:** Panel para que la agencia invite y gestione a los usuarios cliente.

**Layout:** Sidebar + contenido principal

**Elementos del contenido principal:**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Titulo "Usuarios" | Heading | |
| 2 | Botón "Invitar usuario" | Botón primario | Esquina superior derecha |
| 3 | Tabla de usuarios | Tabla | Ver columnas abajo |
| 4 | Botón/icono "Desactivar" por fila | Icono accion | Desactiva el acceso del usuario |

**Columnas de la tabla:**

| Columna | Contenido |
|---|---|
| Nombre | Nombre completo |
| Email | Correo electronico |
| Rol | Badge: Cliente |
| Estado | Badge: Activo / Invitacion pendiente |
| Acciones | Icono desactivar |

**Modal "Invitar usuario":**

| # | Elemento | Tipo | Detalle |
|---|---|---|---|
| 1 | Campo email | Input texto | Email al que se enviara la invitacion |
| 2 | Selector de proyecto | Dropdown | Proyectos activos de la agencia |
| 3 | Botón "Enviar invitacion" | Botón primario | |
| 4 | Botón "Cancelar" | Botón secundario | |

---

## Lo que NO debes diseñar en el MVP

Estas funciones estan planificadas para versiones futuras. Incluirlas ahora generaria confusion y trabajo innecesario.

| No incluir | Version en que aparece |
|---|---|
| Graficos y dashboards de analiticas | v3 |
| Comentarios con hilos anidados (respuestas a comentarios) | v2 |
| Notificaciones en tiempo real (campana, toast) | v2 |
| Calendario de publicaciones | v2 |
| Exportar PDF de informe de revision | v2 |
| Pantalla de configuracion del workspace o cuenta | v3 |
| Gestion multi-empresa (varios clientes en una cuenta) | v3 |
| Version movil o responsive para smartphone | v3 |
| Integracion con redes sociales | v3 |

---

## Resumen visual del flujo de usuario

```
LOGIN
  |
  +---> AGENCIA
  |       |
  |       +---> Dashboard Agencia
  |       +---> Lista de Proyectos --> Detalle de Proyecto --> Visor (lectura)
  |       +---> Subir Contenido (desde Detalle de Proyecto)
  |       +---> Gestion de Usuarios
  |
  +---> CLIENTE
          |
          +---> Dashboard Cliente
          +---> Detalle de Proyecto --> Visor (interactivo: pines + aprobar/rechazar)
```

---

## Notas finales para el diseno

1. **Prioridad absoluta:** Pantalla 7 (Visor). Si el tiempo aprieta, esta es la que debe estar perfecta.
2. **Consistencia:** Usar los mismos colores de estado en todas las pantallas — verde = aprobado, rojo = rechazado, gris/amarillo = pendiente.
3. **Desktop only:** No hace falta preocuparse por responsive en el MVP. Diseno a 1440px.
4. **Sin componentes externos:** El diseno se implementara con Tailwind CSS — mejor usar componentes simples y limpios que complejos.
5. **Nomenclatura en Figma:** Nombrar los frames exactamente como en este documento (Pantalla 1 — Login, etc.) para facilitar la comunicacion con el desarrollador.
