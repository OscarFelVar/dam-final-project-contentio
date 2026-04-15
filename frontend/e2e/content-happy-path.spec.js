import { test, expect } from '@playwright/test'
import path from 'path'

const AGENCY_EMAIL    = 'test@example.com'
const AGENCY_PASSWORD = 'password123'

// Helper: login y navegar al dashboard
async function login(page) {
  await page.goto('/login')
  await page.locator('input[type="email"]').fill(AGENCY_EMAIL)
  await page.locator('input[type="password"]').fill(AGENCY_PASSWORD)
  await page.locator('button[type="submit"]').click()
  await expect(page).toHaveURL(/\/dashboard/)
}

test.describe('Happy path — agencia', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.clear())
    await login(page)
  })

  test('dashboard muestra los contadores de contenido', async ({ page }) => {
    await expect(page.getByText(/panel de control/i)).toBeVisible()
    await expect(page.getByText(/contenidos/i).first()).toBeVisible()
  })

  test('puede navegar al listado de contenidos', async ({ page }) => {
    await page.getByRole('link', { name: /contenidos/i }).click()
    await expect(page).toHaveURL(/\/content-items/)
  })

  test('puede crear un nuevo content item', async ({ page }) => {
    await page.goto('/content-items/new')

    // Seleccionar tipo (botones custom, no select nativo)
    await page.getByRole('button', { name: 'Reel' }).click()

    // Rellenar titulo
    await page.getByPlaceholder(/Ej:/i).fill('Reel de prueba E2E')

    // Seleccionar primera clinica disponible
    await page.locator('select.input-contentio').first().selectOption({ index: 1 })

    await page.getByRole('button', { name: /crear contenido/i }).click()

    // Redirige al listado tras crear
    await expect(page).toHaveURL(/\/content-items/)
    await expect(page.getByText('Reel de prueba E2E').first()).toBeVisible()
  })

  test('puede añadir un comentario al contenido', async ({ page }) => {
    // Navegar al primer item disponible
    await page.goto('/content-items')
    await page.locator('a[href*="/review"]').first().click()

    // Escribir y enviar comentario
    await page.getByPlaceholder(/escribe un comentario/i).fill('Comentario de prueba E2E')
    await page.getByRole('button', { name: 'Enviar', exact: true }).click()

    await expect(page.getByText('Comentario de prueba E2E')).toBeVisible()
  })

  test('puede enviar un contenido a revision', async ({ page }) => {
    await page.goto('/content-items')
    await page.locator('a[href*="/review"]').first().click()

    // Solo disponible si el item está en draft o in_correction
    const btn = page.getByRole('button', { name: /enviar a revision/i })

    if (await btn.isVisible()) {
      await btn.click()

      // Modal de confirmacion
      await expect(page.getByText(/enviar a revision/i).last()).toBeVisible()
      await page.getByRole('button', { name: /enviar a revision/i }).last().click()

      // Badge actualizado
      await expect(page.getByText(/en revision/i)).toBeVisible()
    } else {
      test.info().annotations.push({
        type: 'skip-reason',
        description: 'No hay items en estado draft disponibles para este test',
      })
    }
  })

})

test.describe('Happy path — navegacion general', () => {

  test.beforeEach(async ({ page }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.clear())
    await login(page)
  })

  test('sidebar tiene los enlaces principales', async ({ page }) => {
    await expect(page.getByRole('link', { name: /inicio/i })).toBeVisible()
    await expect(page.getByRole('link', { name: /contenidos/i })).toBeVisible()
  })

  test('pagina de revision muestra assets y comentarios', async ({ page }) => {
    await page.goto('/content-items')

    const reviewLink = page.getByRole('link', { name: /revisar/i }).first()
    if (await reviewLink.isVisible()) {
      await reviewLink.click()
      await expect(page.getByText(/archivos/i)).toBeVisible()
      await expect(page.getByText(/comentarios/i)).toBeVisible()
      await expect(page.getByText(/historial de estados/i)).toBeVisible()
    }
  })

})
