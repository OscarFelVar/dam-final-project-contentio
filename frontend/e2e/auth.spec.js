import { test, expect } from '@playwright/test'

const AGENCY_EMAIL    = 'test@example.com'
const AGENCY_PASSWORD = 'password123'

test.describe('Autenticacion', () => {

  test.beforeEach(async ({ page }) => {
    // Navegar primero para tener contexto de pagina antes de limpiar localStorage
    await page.goto('/')
    await page.evaluate(() => localStorage.clear())
  })

  test('redirige al login si no esta autenticado', async ({ page }) => {
    await page.goto('/')
    await expect(page).toHaveURL(/\/login/)
  })

  test('muestra errores con credenciales incorrectas', async ({ page }) => {
    await page.goto('/login')

    await page.locator('input[type="email"]').fill('noexiste@test.com')
    await page.locator('input[type="password"]').fill('wrong')
    await page.locator('button[type="submit"]').click()

    // Verificar que aparece algun mensaje de error (texto exacto depende de la API)
    await expect(page.locator('p.text-red-500')).toBeVisible()
  })

  test('login correcto redirige al dashboard', async ({ page }) => {
    await page.goto('/login')

    await page.locator('input[type="email"]').fill(AGENCY_EMAIL)
    await page.locator('input[type="password"]').fill(AGENCY_PASSWORD)
    await page.locator('button[type="submit"]').click()

    await expect(page).toHaveURL(/\/dashboard/)
    await expect(page.getByText(/panel de control/i)).toBeVisible()
  })

  test('logout desautentica y redirige al login', async ({ page }) => {
    // Login
    await page.goto('/login')
    await page.locator('input[type="email"]').fill(AGENCY_EMAIL)
    await page.locator('input[type="password"]').fill(AGENCY_PASSWORD)
    await page.locator('button[type="submit"]').click()
    await expect(page).toHaveURL(/\/dashboard/)

    // Logout
    await page.getByRole('button', { name: /cerrar sesi/i }).click()
    await expect(page).toHaveURL(/\/login/)
  })

})
