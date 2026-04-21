# Sesión 08/04/2026 — Provisioning VPS + Dominio producción

## Resumen

Sesión de infraestructura: contratación y configuración inicial del VPS de producción y dominio para Contentio.

---

## 1. VPS contratado — OVH

| Campo | Valor |
|---|---|
| Plan | VPS-1 Zurich Local Zone |
| Specs | 4 vCore, 8GB RAM, 75GB SSD NVMe, 400Mbps ilimitado |
| SO | Ubuntu 24.04.3 LTS |
| Precio | 7,49€/mes sin compromiso |
| IP | `141.227.149.153` |
| IPv6 | `2001:41d0:ab05::4:0:54d` |
| Hostname | `vps-744cd8a9.vps.ovh.net` |
| Usuario | `ubuntu` |

### Decisiones tomadas
- Zurich en vez de Praga (Praga se agotó mientras se seleccionaba)
- Suiza es válida para GDPR — decisión de adecuación reconocida por la Comisión Europea (art. 45 RGPD)
- Ubuntu 24.04 LTS (no 25.04 que ya era EOL en enero 2026)
- Sin Automated Backup Standard (tenemos `backup.sh` propio)

---

## 2. Dominio contratado

- **Dominio:** `contentio.tech` (0,90€/año en OVH)
- `contentio.app`, `contentio.es`, `contentio.com` estaban reservados
- `.tech` elegido por precio y suficiente para el TFG

---

## 3. Configuración del servidor

### Acceso SSH
```bash
ssh ubuntu@141.227.149.153
```

### Pasos ejecutados
1. `sudo apt update && sudo apt upgrade -y` — sistema actualizado + kernel nuevo
2. `sudo reboot` — aplicar nuevo kernel
3. `curl -fsSL https://get.docker.com | sudo sh` — Docker 29.4.0 instalado
4. `sudo usermod -aG docker ubuntu && newgrp docker` — usuario añadido al grupo docker
5. `git clone https://github.com/OscarFelVar/dam-final-project-contentio.git contentio`

### Autenticación GitHub
- Personal Access Token (classic) con scope `repo`, 90 días
- Nombre: `contentio-vps`

---

## 4. Archivos de infra copiados por SCP

Los archivos de infraestructura del working tree (pendientes de commitear el 21/04) fueron copiados manualmente al servidor para poder hacer el deploy antes del commit planificado. El commit del 21/04 no se adelantó — el repo no fue modificado.

Archivos copiados:
- `deploy.sh` → `~/contentio/`
- `update.sh` → `~/contentio/`
- `backup.sh` → `~/contentio/`
- `docker-compose.prod.yml` → `~/contentio/`
- `backend/.env.production.example` → `~/contentio/backend/`
- `docker/nginx/init.conf` → `~/contentio/docker/nginx/`

---

## 5. Configuración .env producción

Archivo `backend/.env` creado en el servidor a partir de `.env.production.example` con:

```env
VITE_API_URL=https://contentio.tech/api
APP_URL=https://contentio.tech
FRONTEND_URL=https://contentio.tech
DB_PASSWORD=<configurada>
APP_KEY=<pendiente generar>
```

> El `APP_KEY` se generará automáticamente al ejecutar `deploy.sh` con `php artisan key:generate`.

---

## 6. DNS configurado

En OVH → Web Cloud → Dominios → `contentio.tech` → Zona DNS:

| Subdominio | Tipo | Destino |
|---|---|---|
| `@` | A | `141.227.149.153` |
| `www` | A | `141.227.149.153` |

Propagación esperada: 24-48h (verificar en dnschecker.org)

---

## 7. Estado de commits

**NINGÚN commit realizado** — respetando el calendario del plan.

---

## 8. Próximos pasos

- [ ] Verificar propagación DNS (~10/04) en dnschecker.org buscando `contentio.tech`
- [ ] Ejecutar `deploy.sh` cuando DNS propague
- [ ] **10/04:** Commit `[S5-01]` PHPUnit + `[S5-06]` sidebar rename
- [ ] **14/04:** Bloque 3 arranca — VPS ya provisionado ✅
- [ ] **15/04:** Commit `[S5-02]` Playwright E2E
- [ ] **21/04:** Commit `[S5-03]` Deploy infra
