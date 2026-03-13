# S0-07 — Configuración de GitHub Projects y backlog de sprints

**Fecha:** 10/03/2026
**Sprint:** 0 (Setup)
**Tarea:** Configuración del sistema de seguimiento del proyecto

---

## 1. Objetivo

Configurar GitHub Projects como herramienta de gestión del backlog y seguimiento del desarrollo, creando todos los issues de los sprints 1 al 5 con metadata completa siguiendo estándares profesionales de la industria.

---

## 2. Herramientas instaladas

### GitHub CLI (`gh`)
- **Versión:** 2.87.3
- **Instalación:** `scoop install gh`
- **Autenticación:** `gh auth login` (OAuth via navegador)
- **Scopes habilitados:** `repo`, `project`, `read:project`, `workflow`

La CLI de GitHub permite automatizar la gestión de issues, pull requests y proyectos directamente desde la terminal, eliminando la necesidad de usar la interfaz web para operaciones repetitivas.

---

## 3. Estructura del proyecto de seguimiento

Se utilizó el proyecto existente **"Contentio - Development Board"** en GitHub Projects v2.

### Campos personalizados configurados

| Campo | Tipo | Opciones |
|-------|------|----------|
| Issue Type | Select | Epic, Task |
| Priority | Select | High, Medium, Low |
| Size | Select | XS, S, M, L, XL |
| Story Points | Number | Escala Fibonacci |
| Status | Select | Backlog, In progress, Testing, Done |
| Parent issue | Relación | Vincula Tasks a su Epic padre |
| Sub-issues progress | Calculado | % de Tasks completadas por Epic |

### Definición de tamaños (Size)
| Valor | Rango de tiempo |
|-------|----------------|
| XS | 1 hora o menos |
| S | 1 a 3 horas |
| M | 3 a 6 horas |
| L | 1 día completo |
| XL | Más de 1 día — dividir en subtareas |

### Escala de Story Points (Fibonacci)
Los story points miden complejidad relativa, no tiempo. Se usa la escala de Fibonacci: **1 · 2 · 3 · 5 · 8**, donde:
- 1 = tarea trivial, sin incertidumbre
- 3 = tarea media con algo de complejidad
- 5 = tarea compleja o con dependencias
- 8 = tarea muy compleja — candidata a dividirse

---

## 4. Jerarquía Epic → Task

Se adoptó una estructura de dos niveles:

```
[EPIC] Sprint N — Nombre del sprint
  ├── [S-N-01] Task 1
  ├── [S-N-02] Task 2
  └── [S-N-XX] Task N
```

Cada **Epic** representa un sprint completo y agrupa las Tasks relacionadas mediante la funcionalidad nativa de sub-issues de GitHub. Esto permite:
- Ver el progreso de cada sprint con la barra de sub-issues progress
- Filtrar el tablero por Epic para trabajar sprint a sprint
- Calcular la velocidad del equipo por sprint (suma de story points completados)

---

## 5. Epics creados

| Issue | Título | Story Points totales |
|-------|--------|---------------------|
| #23 | [EPIC] Sprint 1 — Authentication & Authorization | 15 |
| #24 | [EPIC] Sprint 2 — Content Management | 17 |
| #25 | [EPIC] Sprint 3 — Review System | 15 |
| #26 | [EPIC] Sprint 4 — Approval Workflow | 16 |
| #27 | [EPIC] Sprint 5 — Testing, Deployment & Closing | 14 |

**Total del proyecto: 77 story points**

---

## 6. Backlog completo por sprint

### Sprint 1 — Authentication & Authorization (01/03 – 08/03)

| Issue | Título | Priority | Size | Points | Labels |
|-------|--------|----------|------|--------|--------|
| #8 | Register endpoint POST /api/auth/register | High | M | 3 | Backend, auth, feature |
| #9 | Login endpoint POST /api/auth/login | High | S | 2 | Backend, auth, feature |
| #10 | Logout endpoint DELETE /api/auth/logout | High | XS | 1 | Backend, auth, feature |
| #11 | Agency/clinic role middleware | High | S | 2 | Backend, auth, feature, configuration |
| #12 | Vue: Login & Register pages + Pinia auth store | High | M | 5 | Frontend, auth, feature |
| #13 | Vue Router: protected routes with navigation guards | High | S | 2 | Frontend, auth, feature, configuration |

### Sprint 2 — Content Management (09/03 – 15/03)

| Issue | Título | Priority | Size | Points | Labels |
|-------|--------|----------|------|--------|--------|
| #14 | Content items CRUD API | High | L | 5 | Backend, database, feature |
| #15 | Media asset upload with Laravel Storage | High | M | 3 | Backend, feature, infrastructure |
| #16 | Vue: content items listing page | High | M | 3 | Frontend, feature |
| #17 | Vue: create/edit content form with file upload | Medium | M | 3 | Frontend, feature |
| #18 | Content item state machine | High | M | 3 | Backend, database, feature |

### Sprint 3 — Review System (16/03 – 27/03)

| Issue | Título | Priority | Size | Points | Labels |
|-------|--------|----------|------|--------|--------|
| #19 | Comments API for content items | High | S | 2 | Backend, feature, database |
| #20 | Visual annotations API (Figma-style pins) | High | M | 3 | Backend, feature, database |
| #21 | Vue: content review page | High | L | 5 | Frontend, feature |
| #22 | Vue: interactive visual annotation pins | High | M | 5 | Frontend, feature |

### Sprint 4 — Approval Workflow (28/03 – 05/04)

| Issue | Título | Priority | Size | Points | Labels |
|-------|--------|----------|------|--------|--------|
| #28 | Approve/reject content item endpoints | High | S | 2 | Backend, feature, database |
| #29 | Email notifications on content approval/rejection | Medium | M | 3 | Backend, feature, configuration |
| #30 | Vue: approval UI with confirmation modal | High | M | 3 | Frontend, feature |
| #31 | Vue: state history timeline (audit trail) | Medium | M | 3 | Frontend, Backend, feature, database |
| #32 | Vue: dashboard with content status overview | Medium | L | 5 | Frontend, Backend, feature |

### Sprint 5 — Testing, Deployment & Closing (06/04 – 03/05)

| Issue | Título | Priority | Size | Points | Labels |
|-------|--------|----------|------|--------|--------|
| #33 | PHPUnit/Pest unit and feature tests (>= 70% coverage) | High | L | 5 | Backend, testing, documentation |
| #34 | Playwright E2E tests (happy path) | Medium | M | 3 | Frontend, testing, documentation |
| #35 | Production deployment on OVH VPS with Docker Compose | High | M | 3 | devops, infrastructure, configuration |
| #36 | Technical README and final documentation | Medium | S | 2 | documentation |
| #37 | Demo recording and final TFG presentation | Low | S | 1 | documentation |

---

## 7. Automatización con script bash

Todo el proceso se automatizó mediante el script `contentio_issues.sh` ubicado en `C:/Users/Usuario/`. El script realiza:

1. **Renombrado de Epics** a inglés con `gh issue edit --title`
2. **Reapertura de issues** cerrados con `gh issue reopen`
3. **Actualización de labels** con `gh issue edit --add-label`
4. **Alta en el proyecto** con `gh project item-add`
5. **Asignación de campos custom** vía GraphQL API (`updateProjectV2ItemFieldValue`)
6. **Vinculación de sub-issues** vía REST API (`POST /repos/{owner}/{repo}/issues/{parent}/sub_issues`)

### Fragmento del patrón usado para setear campos en GitHub Projects v2

```bash
gh api graphql -f query="mutation {
  updateProjectV2ItemFieldValue(input: {
    projectId: \"PVT_kwHOCZms4c4BP94c\"
    itemId: \"$ITEM_ID\"
    fieldId: \"$FIELD_PRIORITY\"
    value: { singleSelectOptionId: \"$P_HIGH\" }
  }) { projectV2Item { id } }
}"
```

---

## 8. Decisiones técnicas

### ¿Por qué GitHub Projects v2 y no Jira o Trello?
- Integración nativa con el repositorio (issues, PRs, branches)
- Gratuito para repositorios públicos y cuentas personales
- Los issues forman parte del historial del proyecto y nutren la memoria del TFG
- La CLI permite automatización completa sin depender de la interfaz web

### ¿Por qué todo en inglés?
Es el estándar profesional de la industria tecnológica. Los issues, commits, nombres de ramas, código y comentarios deben estar en inglés para que el proyecto sea legible por cualquier desarrollador independientemente de su idioma.

### ¿Por qué estructura Epic → Task en lugar de Milestones?
Los Milestones de GitHub no soportan campos custom (story points, size, priority). Las Epics como issues permiten aprovechar todos los campos del proyecto y la barra de progreso de sub-issues nativa, replicando la estructura de herramientas como Jira o Linear.

---

## 9. Estado final

- ✅ 5 Epics creados (#23–#27)
- ✅ 25 Tasks creadas (#8–#22, #28–#37)
- ✅ Todos los issues con Issue Type, Priority, Size y Story Points asignados
- ✅ Todas las Tasks vinculadas como sub-issues de su Epic correspondiente
- ✅ Todos los issues en inglés (título y descripción)
- ✅ Backlog visible en GitHub Projects board

---

*Documento generado: 10/03/2026*
