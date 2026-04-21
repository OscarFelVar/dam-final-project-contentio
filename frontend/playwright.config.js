import { defineConfig, devices } from '@playwright/test'

export default defineConfig({
  testDir: './e2e',
  fullyParallel: false,   // tests comparten estado de BD — no paralelizar
  retries: 1,
  timeout: 30_000,

  use: {
    baseURL:       'http://localhost:5173',
    headless:      true,
    screenshot:    'only-on-failure',
    video:         'retain-on-failure',
    trace:         'retain-on-failure',
  },

  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],

  // Asume que el stack Docker ya está corriendo:
  //   docker compose up -d
  // Para ejecutar los tests:
  //   npx playwright test
  // Para ver el reporte:
  //   npx playwright show-report
})
