# Sesión 06/04/2026 — Sprint 5: Tests + Infraestructura producción

## Resumen

Sesión de trabajo completo del Bloque 2 (Tests) y adelanto del Bloque 3 (Producción).

---

## 1. Rama feature/S5-closing

- Creada desde `origin/develop` (que ya tenía el merge de PR #47 S4)
- El `develop` local estaba desactualizado — resuelto con `git fetch`

---

## 2. PHPUnit — 66 tests, 85.3% coverage

### Tests escritos (working tree, pendiente commitear el 10/04)

| Archivo | Tests | Qué cubre |
|---------|-------|-----------|
| `AuthTest.php` | 6 | Login, logout, perfil, 401 |
| `ContentItemTest.php` | 12 | CRUD + paginación + autorización |
| `ContentStateTest.php` | 10 | State machine + emails |
| `CommentTest.php` | 6 | CRUD + ownership |
| `AnnotationTest.php` | 7 | Pins + posición + ownership |
| `ClinicTest.php` | 14 | CRUD clínicas + gestión usuarios |
| `AdminTest.php` | 9 | Stats + usuarios + self-delete |

### Fix aplicado en ContentItemTest

La paginación de S4-08 cambió la respuesta de array plano a objeto Laravel con 13 claves.
`assertJsonCount(3)` fallaba porque contaba claves del objeto raíz, no items.
**Fix:** `assertJsonCount(3, 'data')` y `assertJsonCount(0, 'data')`

### Infraestructura añadida para coverage

- **PCOV** añadido al `docker/php/Dockerfile` (driver de cobertura ligero)
- **Problema:** entrypoint.sh tenía CRLF (Windows) → Docker en Linux fallaba con "no such file or directory"
- **Fix:** `sed -i 's/\r//' docker/php/entrypoint.sh` + `.gitattributes` con `*.sh text eol=lf`
- **UserFactory:** añadido estado `admin()` (faltaba, solo existían `agency()` y `clinic()`)

### Resultado final
```
Tests:    66 passed (145 assertions)
Coverage: 85.3%  (objetivo: ≥70% ✓)
```

---

## 3. Playwright E2E — 11/11 tests

### Tests escritos (working tree, pendiente commitear el 15/04)

- `e2e/auth.spec.js` — 4 tests: redirect, error credenciales, login, logout
- `e2e/content-happy-path.spec.js` — 7 tests: dashboard, navegación, crear item, comentario, enviar a revisión, sidebar, review page

### Problemas encontrados y resueltos

| Error | Causa | Fix |
|-------|-------|-----|
| `localStorage SecurityError` | `page.evaluate` antes de navegar | Añadir `page.goto('/')` en `beforeEach` |
| Timeout en inputs | Placeholders no coinciden (`tu@agencia.com`, `••••••••••`) | Cambiar a `input[type="email"]` y `input[type="password"]` |
| Botón "Iniciar sesión" no encontrado | Acento en ó no matchea `/sesion/i` | Cambiar a `button[type="submit"]` |
| Link "Revisar" no encontrado | Las cards son links que envuelven toda la card, sin texto "Revisar" | Cambiar a `a[href*="/review"]` |
| Sidebar "Dashboard" no encontrado | El link se llama **"Inicio"**, no "Dashboard" | Cambiar a `/inicio/i` |
| `getByLabel(/titulo/i)` timeout | Label sin `for`/`id`, `/titulo/i` no matchea "Título" (acento) | Cambiar a `getByPlaceholder(/Ej:/i)` |
| Tipo de contenido no seleccionable | Es un selector custom con `<button>`, no `<select>` | `getByRole('button', { name: 'Reel' })` |
| Redirección inesperada tras crear | El form redirige a `/content-items` (lista), no a la review | Ajustar `toHaveURL(/\/content-items/)` |
| Dos botones "Enviar" | "Enviar" y "Enviar a revisión" en la misma página | `{ name: 'Enviar', exact: true }` |
| `getByText` strict violation | Retries creaban múltiples items con el mismo nombre | `.first()` |

### Resultado final
```
11 passed (21.8s)
```

---

## 4. Infraestructura producción — revisión y mejoras

### Problema crítico resuelto: SSL chicken-and-egg

**Problema:** `prod.conf` tiene bloque HTTPS con `ssl_certificate`. Nginx no arranca si el certificado no existe. Pero certbot necesita Nginx corriendo en HTTP para obtener el certificado.

**Solución:**
1. Creado `docker/nginx/init.conf` — config HTTP-only temporal
2. `deploy.sh` arranca Nginx con `init.conf` → obtiene cert → swap a `prod.conf` → reload
3. `docker-compose.prod.yml` ahora monta `current.conf` (generado por deploy.sh)

### Archivos creados/modificados

| Archivo | Cambio |
|---------|--------|
| `deploy.sh` | Reescrito completo: fases ordenadas, sustitución de YOUR_DOMAIN, verificación de .env, cron SSL automático |
| `update.sh` | Nuevo: git pull → rebuild → migrate → cache → nginx reload |
| `backup.sh` | Nuevo: pg_dump + storage tar.gz, rotación 7d diaria + 4 semanas domingos |
| `docker/nginx/init.conf` | Nuevo: config HTTP-only para primer arranque |
| `docker-compose.prod.yml` | Nginx monta `current.conf` en vez de `prod.conf` directamente |
| `backend/.env.production.example` | Añadida `VITE_API_URL` (faltaba, usada en docker-compose.prod.yml) |
| `.gitignore` (raíz) | Nuevo: ignora `current.conf`, `backups/`, `backend/.env` |

---

## 5. Decisiones de arquitectura / producto

### Dominio y deploy
- TFG: usar `contentio.app` (~12€/año)
- Negocio de M.estudio: dominio separado `mestudio.es`
- Un VPS puede servir ambos dominios con Nginx
- OVH VPS Starter (2GB RAM, ~3,99€/mes) + Ubuntu 22.04 LTS

### Multi-tenant (post-TFG)
- Contentio ya tiene la base (separación por `user_id`)
- Para white-label real faltaría tabla `tenants`
- Flujo posible: `agencia.contentio.app` o dominio propio apuntando al VPS

---

## 6. Estado de commits

**NINGÚN commit realizado en esta sesión** (respetando el calendario del plan).

Commits pendientes según `PLANIFICACION_TFG.md`:
- **10/04** → `[S5-01]` PHPUnit (tests + Dockerfile + .gitattributes + UserFactory + ClinicTest + AdminTest)
- **15/04** → `[S5-02]` Playwright E2E (e2e/, playwright.config.js, package.json)
- **21/04** → `[S5-03]` Deploy OVH (deploy.sh + update.sh + backup.sh + docker-compose.prod.yml + init.conf)

---

## 7. Próximos pasos

- [ ] Esta semana: registrarse en OVH, contratar VPS Starter + dominio `contentio.app`
- [ ] 07/04: Bloque 2 "oficialmente" arranca (issues GitHub #33 → In Progress)
- [ ] 10/04: Commit `[S5-01]`
- [ ] 14/04: Bloque 3 arranca — provisionar VPS + DNS
- [ ] 15/04: Commit `[S5-02]`
