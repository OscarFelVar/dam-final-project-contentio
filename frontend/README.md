# Contentio — Frontend

Vue 3 + Pinia + Vue Router + Tailwind v4 + Vite

## Desarrollo

```bash
# El frontend corre dentro de Docker — no necesitas Node local
docker compose up -d
# Accede en http://localhost:5173
```

## Tests E2E (Playwright)

```bash
npx playwright install chromium   # una sola vez
npm run test:e2e                  # ejecutar (requiere Docker corriendo)
npm run test:e2e:ui               # modo interactivo
npm run test:e2e:report           # ver reporte
```

## Build de produccion

Generado automaticamente via `docker/frontend/Dockerfile` durante el deploy.
Para generar manualmente: `npm run build` → output en `dist/`
