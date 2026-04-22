<script setup>
import { ref, onMounted } from 'vue'
import { useAdminStore } from '../stores/admin'
import AppLayout from '../components/AppLayout.vue'

const store = useAdminStore()

const showModal = ref(false)
const saving    = ref(false)
const errorMsg  = ref('')
const form      = ref({ name: '', email: '', password: '' })

onMounted(async () => {
  await Promise.all([store.fetchStats(), store.fetchUsers()])
})

function openModal() {
  form.value  = { name: '', email: '', password: '' }
  errorMsg.value = ''
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function handleCreate() {
  saving.value   = true
  errorMsg.value = ''
  try {
    await store.createUser(form.value)
    closeModal()
  } catch (e) {
    errorMsg.value = e.response?.data?.errors?.email?.[0]
      ?? e.response?.data?.message
      ?? 'Error al crear el usuario.'
  } finally {
    saving.value = false
  }
}

async function handleDelete(user) {
  if (!confirm(`¿Eliminar el usuario "${user.name}"? Esta acción no se puede deshacer.`)) return
  try {
    await store.deleteUser(user.id)
  } catch (e) {
    alert(e.response?.data?.message ?? 'No se pudo eliminar.')
  }
}

function roleLabel(role) {
  return { admin: 'Admin', agency: 'Agencia', clinic: 'Clínica' }[role] ?? role
}

function roleBadgeClass(role) {
  return {
    admin:  'bg-purple-50 text-purple-700 border-purple-200',
    agency: 'bg-amber-50 text-amber-700 border-amber-200',
    clinic: 'bg-emerald-50 text-emerald-700 border-emerald-200',
  }[role] ?? 'bg-[#F5F1EA] text-[#A08A7A] border-[#E8E0D8]'
}

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>

<template>
  <AppLayout>

    <!-- Header -->
    <div class="flex items-center justify-between gap-3 flex-wrap mb-8">
      <div class="min-w-0">
        <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">Administración</p>
        <h1 class="text-2xl text-[#4A342A] tracking-wide">Panel de control</h1>
      </div>
      <button
        @click="openModal"
        class="flex items-center gap-2 bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-5 py-2.5 text-sm transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva agencia
      </button>
    </div>

    <!-- Stats -->
    <div v-if="store.stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
      <div class="bg-white border border-[#E8E0D8] rounded-xl p-5">
        <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-1">Agencias</p>
        <p class="text-3xl text-[#4A342A]">{{ store.stats.agencies }}</p>
      </div>
      <div class="bg-white border border-[#E8E0D8] rounded-xl p-5">
        <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-1">Clínicas</p>
        <p class="text-3xl text-[#4A342A]">{{ store.stats.clinics }}</p>
      </div>
      <div class="bg-white border border-[#E8E0D8] rounded-xl p-5">
        <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-1">Usuarios clinic</p>
        <p class="text-3xl text-[#4A342A]">{{ store.stats.clinic_users }}</p>
      </div>
      <div class="bg-white border border-[#E8E0D8] rounded-xl p-5">
        <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-1">Contenidos</p>
        <p class="text-3xl text-[#4A342A]">{{ store.stats.content_items }}</p>
      </div>
    </div>

    <!-- Users table -->
    <div class="bg-white border border-[#E8E0D8] rounded-xl overflow-hidden">
      <div class="px-6 py-4 border-b border-[#E8E0D8]">
        <h2 class="text-sm text-[#4A342A]">Todos los usuarios</h2>
      </div>

      <div v-if="store.loading" class="text-center py-12 text-[#D7C9B8] text-sm">
        Cargando...
      </div>

      <div v-else class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-[#E8E0D8]">
            <th class="text-left px-6 py-3 text-xs tracking-[0.1em] uppercase text-[#A08A7A] font-normal">Nombre</th>
            <th class="text-left px-6 py-3 text-xs tracking-[0.1em] uppercase text-[#A08A7A] font-normal">Email</th>
            <th class="text-left px-6 py-3 text-xs tracking-[0.1em] uppercase text-[#A08A7A] font-normal">Rol</th>
            <th class="text-left px-6 py-3 text-xs tracking-[0.1em] uppercase text-[#A08A7A] font-normal">Alta</th>
            <th class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="user in store.users"
            :key="user.id"
            class="border-b border-[#F5F1EA] last:border-0"
          >
            <td class="px-6 py-3 text-[#4A342A]">{{ user.name }}</td>
            <td class="px-6 py-3 text-[#A08A7A]">{{ user.email }}</td>
            <td class="px-6 py-3">
              <span :class="['text-[10px] border rounded-full px-2.5 py-1', roleBadgeClass(user.role)]">
                {{ roleLabel(user.role) }}
              </span>
            </td>
            <td class="px-6 py-3 text-[#A08A7A] text-xs">{{ formatDate(user.created_at) }}</td>
            <td class="px-6 py-3 text-right">
              <button
                v-if="user.role !== 'admin'"
                @click="handleDelete(user)"
                class="text-[#A08A7A] hover:text-red-500 transition-colors"
                title="Eliminar"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      </div>
    </div>

    <!-- Modal nueva agencia -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <div class="px-6 pt-6 pb-4 border-b border-[#E8E0D8]">
            <h2 class="text-base text-[#4A342A]">Nueva agencia</h2>
          </div>
          <form @submit.prevent="handleCreate" class="px-6 py-5 space-y-4">
            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">Nombre <span class="text-red-400">*</span></label>
              <input v-model="form.name" required type="text" placeholder="Nombre de la agencia" class="input-contentio" />
            </div>
            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">Email <span class="text-red-400">*</span></label>
              <input v-model="form.email" required type="email" placeholder="agencia@email.com" class="input-contentio" />
            </div>
            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">Contraseña <span class="text-red-400">*</span></label>
              <input v-model="form.password" required type="password" placeholder="Mín. 8 caracteres" class="input-contentio" />
            </div>
            <p v-if="errorMsg" class="text-xs text-red-500">{{ errorMsg }}</p>
            <div class="flex justify-end gap-3 pt-2">
              <button type="button" @click="closeModal" class="px-4 py-2 text-sm text-[#A08A7A] hover:text-[#4A342A] transition-colors">
                Cancelar
              </button>
              <button type="submit" :disabled="saving" class="px-5 py-2 bg-[#4A342A] hover:bg-[#7D5A44] disabled:opacity-50 text-white text-sm rounded-lg transition-colors">
                {{ saving ? 'Creando...' : 'Crear agencia' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>
