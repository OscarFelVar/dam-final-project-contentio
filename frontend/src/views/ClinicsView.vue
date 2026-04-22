<script setup>
import { ref, onMounted } from 'vue'
import { useClinicStore } from '../stores/clinic'
import AppLayout from '../components/AppLayout.vue'

const store = useClinicStore()

// ─── Modal clínica ────────────────────────────────────────────
const showModal = ref(false)
const editing   = ref(null)
const saving    = ref(false)
const errorMsg  = ref('')
const form = ref({ name: '', description: '', contact_email: '' })

onMounted(() => store.fetchClinics())

function openCreate() {
  editing.value   = null
  form.value      = { name: '', description: '', contact_email: '' }
  errorMsg.value  = ''
  showModal.value = true
}

function openEdit(clinic) {
  editing.value   = clinic
  form.value      = { name: clinic.name, description: clinic.description ?? '', contact_email: clinic.contact_email ?? '' }
  errorMsg.value  = ''
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function handleSubmit() {
  saving.value   = true
  errorMsg.value = ''
  try {
    const payload = {
      name:          form.value.name.trim(),
      description:   form.value.description.trim() || null,
      contact_email: form.value.contact_email.trim() || null,
    }
    editing.value ? await store.updateClinic(editing.value.id, payload) : await store.createClinic(payload)
    closeModal()
  } catch (e) {
    errorMsg.value = e.response?.data?.message ?? 'Error al guardar.'
  } finally {
    saving.value = false
  }
}

async function handleDelete(clinic) {
  if (!confirm(`¿Eliminar la clínica "${clinic.name}"?`)) return
  try {
    await store.deleteClinic(clinic.id)
  } catch (e) {
    alert(e.response?.data?.message ?? 'No se pudo eliminar.')
  }
}

// ─── Modal usuarios ───────────────────────────────────────────
const showUserModal  = ref(false)
const activeClinic   = ref(null)
const clinicUsers    = ref([])
const loadingUsers   = ref(false)
const savingUser     = ref(false)
const userError      = ref('')
const userForm       = ref({ name: '', email: '', password: '' })

async function openUsers(clinic) {
  activeClinic.value  = clinic
  showUserModal.value = true
  userError.value     = ''
  userForm.value      = { name: '', email: '', password: '' }
  loadingUsers.value  = true
  try {
    clinicUsers.value = await store.fetchClinicUsers(clinic.id)
  } finally {
    loadingUsers.value = false
  }
}

function closeUserModal() { showUserModal.value = false }

async function handleAddUser() {
  savingUser.value = true
  userError.value  = ''
  try {
    const newUser = await store.createClinicUser(activeClinic.value.id, userForm.value)
    clinicUsers.value.push(newUser)
    userForm.value = { name: '', email: '', password: '' }
  } catch (e) {
    userError.value = e.response?.data?.message ?? e.response?.data?.errors?.email?.[0] ?? 'Error al crear usuario.'
  } finally {
    savingUser.value = false
  }
}

async function handleRemoveUser(user) {
  if (!confirm(`¿Eliminar acceso de ${user.name}?`)) return
  try {
    await store.deleteClinicUser(activeClinic.value.id, user.id)
    clinicUsers.value = clinicUsers.value.filter(u => u.id !== user.id)
  } catch (e) {
    alert('No se pudo eliminar el usuario.')
  }
}
</script>

<template>
  <AppLayout>

    <!-- Header -->
    <div class="flex items-center justify-between gap-3 flex-wrap mb-8">
      <div class="min-w-0">
        <p class="text-xs tracking-[0.12em] uppercase text-[#A08A7A] mb-1">Gestión</p>
        <h1 class="text-2xl text-[#4A342A] tracking-wide">Clínicas</h1>
      </div>
      <button
        @click="openCreate"
        class="flex items-center gap-2 bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg px-5 py-2.5 text-sm transition-colors"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Nueva clínica
      </button>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="text-center py-20 text-[#D7C9B8] text-sm">
      Cargando...
    </div>

    <!-- Empty state -->
    <div v-else-if="store.clinics.length === 0" class="text-center py-20">
      <div class="w-14 h-14 bg-[#F5F1EA] rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-6 h-6 text-[#D7C9B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
        </svg>
      </div>
      <p class="text-sm text-[#A08A7A]">Aún no tienes clínicas.</p>
      <button @click="openCreate" class="text-xs text-[#7D5A44] hover:underline mt-2 inline-block">
        Crear primera clínica →
      </button>
    </div>

    <!-- Clinic list -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <div
        v-for="clinic in store.clinics"
        :key="clinic.id"
        class="bg-white border border-[#E8E0D8] rounded-xl p-5 flex flex-col gap-3"
      >
        <!-- Name + actions -->
        <div class="flex items-start justify-between gap-3">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-[#F5F1EA] flex items-center justify-center flex-shrink-0">
              <svg class="w-4 h-4 text-[#A08A7A]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <p class="text-sm text-[#4A342A] truncate">{{ clinic.name }}</p>
          </div>
          <div class="flex gap-1 flex-shrink-0">
            <button
              @click="openUsers(clinic)"
              class="p-1.5 text-[#A08A7A] hover:text-[#4A342A] hover:bg-[#F5F1EA] rounded transition-colors"
              title="Gestionar usuarios"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
              </svg>
            </button>
            <button
              @click="openEdit(clinic)"
              class="p-1.5 text-[#A08A7A] hover:text-[#4A342A] hover:bg-[#F5F1EA] rounded transition-colors"
              title="Editar"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
              </svg>
            </button>
            <button
              @click="handleDelete(clinic)"
              class="p-1.5 text-[#A08A7A] hover:text-red-500 hover:bg-red-50 rounded transition-colors"
              title="Eliminar"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- Description -->
        <p v-if="clinic.description" class="text-xs text-[#A08A7A] line-clamp-2">
          {{ clinic.description }}
        </p>

        <!-- Contact email -->
        <div v-if="clinic.contact_email" class="flex items-center gap-1.5">
          <svg class="w-3 h-3 text-[#D7C9B8] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          <span class="text-[11px] text-[#A08A7A] truncate">{{ clinic.contact_email }}</span>
        </div>
      </div>
    </div>

    <!-- Modal crear/editar -->
    <Teleport to="body">
      <div
        v-if="showModal"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
          <!-- Modal header -->
          <div class="px-6 pt-6 pb-4 border-b border-[#E8E0D8]">
            <h2 class="text-base text-[#4A342A]">
              {{ editing ? 'Editar clínica' : 'Nueva clínica' }}
            </h2>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleSubmit" class="px-6 py-5 space-y-4">

            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
                Nombre <span class="text-red-400">*</span>
              </label>
              <input
                v-model="form.name"
                required
                type="text"
                placeholder="Clínica Estética..."
                class="input-contentio"
              />
            </div>

            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
                Descripción
              </label>
              <textarea
                v-model="form.description"
                rows="3"
                placeholder="Descripción opcional..."
                class="input-contentio resize-none"
              />
            </div>

            <div>
              <label class="block text-xs tracking-[0.1em] uppercase text-[#7D5A44] mb-2">
                Email de contacto
              </label>
              <input
                v-model="form.contact_email"
                type="email"
                placeholder="contacto@clinica.com"
                class="input-contentio"
              />
            </div>

            <p v-if="errorMsg" class="text-xs text-red-500">{{ errorMsg }}</p>

            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-2">
              <button
                type="button"
                @click="closeModal"
                class="px-4 py-2 text-sm text-[#A08A7A] hover:text-[#4A342A] transition-colors"
              >
                Cancelar
              </button>
              <button
                type="submit"
                :disabled="saving"
                class="px-5 py-2 bg-[#4A342A] hover:bg-[#7D5A44] disabled:opacity-50 text-white text-sm rounded-lg transition-colors"
              >
                {{ saving ? 'Guardando...' : (editing ? 'Guardar cambios' : 'Crear clínica') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>

    <!-- Modal usuarios de clínica -->
    <Teleport to="body">
      <div
        v-if="showUserModal"
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        @click.self="closeUserModal"
      >
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md flex flex-col max-h-[80vh]">
          <!-- Header -->
          <div class="px-6 pt-6 pb-4 border-b border-[#E8E0D8] flex items-center justify-between flex-shrink-0">
            <div>
              <h2 class="text-base text-[#4A342A]">Usuarios de clínica</h2>
              <p class="text-xs text-[#A08A7A] mt-0.5">{{ activeClinic?.name }}</p>
            </div>
            <button @click="closeUserModal" class="text-[#A08A7A] hover:text-[#4A342A] transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <!-- Lista de usuarios existentes -->
          <div class="px-6 py-4 overflow-y-auto flex-1">
            <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-3">Acceso activo</p>

            <div v-if="loadingUsers" class="text-xs text-[#D7C9B8] py-4 text-center">Cargando...</div>

            <div v-else-if="clinicUsers.length === 0" class="text-xs text-[#A08A7A] py-4 text-center">
              Sin usuarios asignados todavía.
            </div>

            <div v-else class="space-y-2 mb-4">
              <div
                v-for="u in clinicUsers"
                :key="u.id"
                class="flex items-center justify-between bg-[#F5F1EA] rounded-lg px-3 py-2"
              >
                <div>
                  <p class="text-xs text-[#4A342A]">{{ u.name }}</p>
                  <p class="text-[11px] text-[#A08A7A]">{{ u.email }}</p>
                </div>
                <button
                  @click="handleRemoveUser(u)"
                  class="text-[#A08A7A] hover:text-red-500 transition-colors ml-2"
                  title="Eliminar acceso"
                >
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Formulario nuevo usuario -->
            <div class="border-t border-[#E8E0D8] pt-4">
              <p class="text-xs tracking-[0.1em] uppercase text-[#A08A7A] mb-3">Añadir usuario</p>
              <form @submit.prevent="handleAddUser" class="space-y-3">
                <input
                  v-model="userForm.name"
                  required
                  type="text"
                  placeholder="Nombre"
                  class="input-contentio"
                />
                <input
                  v-model="userForm.email"
                  required
                  type="email"
                  placeholder="Email"
                  class="input-contentio"
                />
                <input
                  v-model="userForm.password"
                  required
                  type="password"
                  placeholder="Contraseña (mín. 8 caracteres)"
                  class="input-contentio"
                />
                <p v-if="userError" class="text-xs text-red-500">{{ userError }}</p>
                <button
                  type="submit"
                  :disabled="savingUser"
                  class="w-full py-2 bg-[#4A342A] hover:bg-[#7D5A44] disabled:opacity-50 text-white text-sm rounded-lg transition-colors"
                >
                  {{ savingUser ? 'Creando...' : 'Crear y asignar usuario' }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

  </AppLayout>
</template>
