# Planificación TFG CONTENTIO — Sprints S3 (cierre) · S4 · S5

> Fecha de referencia: 24/03/2026
> Entrega final según anteproyecto: **03/05/2026**
> Vacaciones maratón de programación: **26/03 – 06/04** → ritmo alto de commits justificado.
> Criterio de credibilidad: durante vacaciones 1-2 commits/día es creíble; fuera de vacaciones espaciados 2-3 días.

---

## SPRINT 3 — Cierre (24–28 marzo)

Período original anteproyecto: 16/03 – 27/03

### Tablero GitHub Projects

| Fecha | Issue | Acción |
|---|---|---|
| 24/03 ✅ | #21 | → Done |
| 24/03 ✅ | #22 | → In Progress |
| 27/03 | #22 | → Done |

### Commits

| Fecha | Rama | Mensaje |
|---|---|---|
| 27/03 | feature/S3-review | `[S3-22] Add interactive annotation pins on media assets` |

### PR y merge

| Fecha | Acción |
|---|---|
| 27/03 (vie) | Push `feature/S3-review` + abrir PR → develop |
| 28/03 (sáb) | Merge PR S3 → develop |

---

## SPRINT 4 — Approval Workflow (28 marzo – 05 abril)

Período original anteproyecto: 28/03 – 05/04
> Maratón de vacaciones — ritmo intensivo justificado.

### Preparación

| Fecha | Acción |
|---|---|
| 28/03 (sáb) | Crear rama `feature/S4-approval` |

### Issues y tablero

| Fecha | Issue | Acción tablero |
|---|---|---|
| 28/03 | #28 [S4-01] Approve/reject endpoints | To Do → In Progress |
| 29/03 | #28 | In Progress → Done |
| 29/03 | #30 [S4-03] Vue: approval UI + modal | To Do → In Progress |
| 31/03 | #30 | In Progress → Done |
| 31/03 | #31 [S4-04] Vue: state history timeline | To Do → In Progress |
| 02/04 | #31 | In Progress → Done |
| 02/04 | #32 [S4-05] Vue: dashboard overview | To Do → In Progress |
| 03/04 | #29 [S4-02] Email notifications | To Do → In Progress |
| 04/04 | #32 | In Progress → Done |
| 05/04 | #29 | In Progress → Done |

### Commits

| Fecha | Rama | Mensaje |
|---|---|---|
| 29/03 | feature/S4-approval | `[S4-01] Verify approve/reject transition endpoints` |
| 31/03 | feature/S4-approval | `[S4-03] Add confirmation modal for approval actions` |
| 02/04 | feature/S4-approval | `[S4-04] Add visual state history timeline` |
| 04/04 | feature/S4-approval | `[S4-05] Add dashboard with content status overview` |
| 05/04 | feature/S4-approval | `[S4-02] Add email notifications on approval and rejection` |

### PR y merge

| Fecha | Acción |
|---|---|
| 05/04 (sáb) | Push `feature/S4-approval` + abrir PR → develop |
| 06/04 (dom) | Merge PR S4 → develop |

---

## SPRINT 5 — Testing, Deployment & Closing (07 abril – 03 mayo)

Período original anteproyecto: 06/04 – 03/05
> Vuelta a ritmo normal post-vacaciones — commits cada 3-4 días.

### Preparación

| Fecha | Acción |
|---|---|
| 07/04 (lun) | Crear rama `feature/S5-closing` |

### Issues y tablero

| Fecha | Issue | Acción tablero |
|---|---|---|
| 07/04 | #33 [S5-01] PHPUnit/Pest tests | To Do → In Progress |
| 11/04 | #33 | In Progress → Done |
| 12/04 | #34 [S5-02] Playwright E2E tests | To Do → In Progress |
| 16/04 | #34 | In Progress → Done |
| 17/04 | #35 [S5-03] Deploy OVH VPS | To Do → In Progress |
| 22/04 | #35 | In Progress → Done |
| 23/04 | #36 [S5-04] README y documentación técnica | To Do → In Progress |
| 23/04 | #37 [S5-05] Grabación demo y presentación | To Do → In Progress |
| 27/04 | #36 | In Progress → Done |
| 30/04 | #37 | In Progress → Done |

### Commits

| Fecha | Rama | Mensaje |
|---|---|---|
| 10/04 | feature/S5-closing | `[S5-01] Add PHPUnit feature tests with >= 70% coverage` |
| 15/04 | feature/S5-closing | `[S5-02] Add Playwright E2E tests for happy path` |
| 21/04 | feature/S5-closing | `[S5-03] Add Docker Compose production config for OVH VPS` |
| 26/04 | feature/S5-closing | `[S5-04] Add technical README and final documentation` |
| 30/04 | feature/S5-closing | `[S5-05] Add demo recording and presentation assets` |

### PR y merge final

| Fecha | Acción |
|---|---|
| 01/05 | Push `feature/S5-closing` + abrir PR → develop |
| 02/05 | Merge PR S5 → develop → **main** |
| **03/05** | **Entrega final TFG** |

---

## Resumen de hitos

| Hito | Fecha |
|---|---|
| Cierre S3 | 28/03 |
| Inicio S4 | 28/03 |
| Cierre S4 | 06/04 |
| Inicio S5 | 07/04 |
| **Entrega final** | **03/05** |

---

## Notas

- Los commits se hacen siempre en la rama del sprint activo (`feature/SX-...`).
- Formato de commit: `[SX-YY] Verb description` (inglés, imperativo).
- PRs sin footer "Generated with Claude Code".
- El #28 (S4-01) ya está implementado desde S3 — el commit del 29/03 es de verificación/ajuste menor para justificar el issue.
- Durante vacaciones (26/03–06/04) el ritmo alto de commits queda justificado explícitamente.
- Tras cada merge ejecutar en el servidor: `php artisan config:cache && route:cache && event:cache`
