<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import { useContentStore } from '../stores/content'

const props = defineProps({
  asset:               { type: Object, required: true },
  readonly:            { type: Boolean, default: false },
  initialAnnotations:  { type: Array,   default: null },
})

const auth  = useAuthStore()
const store = useContentStore()

const annotations = ref([])
const loading     = ref(true)

// Pin en proceso de creación
const pendingPin  = ref(null)  // { x, y }
const pinComment  = ref('')
const saving      = ref(false)

// Pin seleccionado para ver detalle
const activePin   = ref(null)

const imageRef = ref(null)

onMounted(async () => {
  if (props.initialAnnotations !== null) {
    annotations.value = props.initialAnnotations
    loading.value = false
  } else {
    annotations.value = await store.fetchAnnotations(props.asset.id)
    loading.value = false
  }
})

// Click sobre la imagen → crear pin en esa posición
function handleImageClick(e) {
  if (props.readonly) return

  // Cerrar cualquier pin activo o pendiente
  activePin.value  = null
  pendingPin.value = null

  const rect = imageRef.value.getBoundingClientRect()
  const x = ((e.clientX - rect.left) / rect.width)  * 100
  const y = ((e.clientY - rect.top)  / rect.height) * 100

  pendingPin.value = { x: parseFloat(x.toFixed(2)), y: parseFloat(y.toFixed(2)) }
  pinComment.value = ''
}

function cancelPending() {
  pendingPin.value = null
  pinComment.value = ''
}

async function savePendingPin() {
  if (!pinComment.value.trim()) return
  saving.value = true
  try {
    const annotation = await store.addAnnotation(props.asset.id, {
      x_position: pendingPin.value.x,
      y_position: pendingPin.value.y,
      comment:    pinComment.value.trim(),
    })
    annotations.value.push(annotation)
    pendingPin.value = null
    pinComment.value = ''
  } finally {
    saving.value = false
  }
}

function selectPin(pin, e) {
  e.stopPropagation()
  activePin.value = activePin.value?.id === pin.id ? null : pin
}

async function removePin(pin, e) {
  e.stopPropagation()
  if (!confirm('¿Eliminar este pin?')) return
  await store.deleteAnnotation(props.asset.id, pin.id)
  annotations.value = annotations.value.filter(a => a.id !== pin.id)
  if (activePin.value?.id === pin.id) activePin.value = null
}

function closePinDetail(e) {
  if (e.target === e.currentTarget) activePin.value = null
}
</script>

<template>
  <div class="relative select-none">

    <!-- Imagen con cursor crosshair si se puede anotar -->
    <img
      ref="imageRef"
      :src="`http://localhost:8080/storage/${asset.file_path}`"
      :alt="asset.file_name"
      :class="['w-full object-contain max-h-[500px] bg-[#F5F1EA]', !readonly ? 'cursor-crosshair' : '']"
      @click="handleImageClick"
      draggable="false"
    />

    <!-- Hint -->
    <div v-if="!readonly && !loading" class="absolute top-2 left-2 bg-black/40 text-white text-[10px] rounded px-2 py-1 pointer-events-none">
      Haz clic en la imagen para añadir un pin
    </div>

    <!-- Pines existentes -->
    <template v-if="!loading">
      <div
        v-for="pin in annotations"
        :key="pin.id"
        :style="{ left: pin.x_position + '%', top: pin.y_position + '%' }"
        class="absolute -translate-x-1/2 -translate-y-1/2"
        @click="selectPin(pin, $event)"
      >
        <!-- Círculo numerado -->
        <div
          :class="[
            'w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-medium border-2 border-white shadow-md cursor-pointer transition-transform hover:scale-110',
            activePin?.id === pin.id ? 'bg-[#4A342A] text-white' : 'bg-amber-400 text-[#4A342A]'
          ]"
        >
          {{ pin.pin_number }}
        </div>

        <!-- Popover del pin activo -->
        <div
          v-if="activePin?.id === pin.id"
          class="absolute z-20 w-52 bg-white border border-[#E8E0D8] rounded-xl shadow-lg p-3"
          :style="{
            top:       pin.y_position > 55 ? 'auto' : '100%',
            bottom:    pin.y_position > 55 ? '100%' : 'auto',
            left:      pin.x_position > 60 ? 'auto' : '50%',
            right:     pin.x_position > 60 ? '50%'  : 'auto',
            transform: pin.x_position > 60 ? 'none'  : 'translateX(-50%)',
            marginTop:    pin.y_position > 55 ? '0'    : '8px',
            marginBottom: pin.y_position > 55 ? '8px'  : '0',
          }"
          @click.stop
        >
          <div class="flex items-start justify-between gap-2 mb-1">
            <span class="text-[10px] font-medium text-[#4A342A]">{{ pin.user?.name }}</span>
            <button
              v-if="pin.user?.id === auth.user?.id"
              @click="removePin(pin, $event)"
              class="text-[#D7C9B8] hover:text-red-400 transition-colors shrink-0"
            >
              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <p class="text-xs text-[#7D5A44] leading-relaxed">{{ pin.comment }}</p>
        </div>
      </div>
    </template>

    <!-- Pin pendiente (posición donde se hizo click) -->
    <div
      v-if="pendingPin"
      :style="{ left: pendingPin.x + '%', top: pendingPin.y + '%' }"
      class="absolute -translate-x-1/2 -translate-y-1/2 z-20"
      @click.stop
    >
      <!-- Círculo punteado -->
      <div class="w-6 h-6 rounded-full border-2 border-dashed border-[#4A342A] bg-white/80 flex items-center justify-center">
        <div class="w-1.5 h-1.5 rounded-full bg-[#4A342A]"/>
      </div>

      <!-- Formulario -->
      <div class="absolute z-30 w-56 bg-white border border-[#E8E0D8] rounded-xl shadow-lg p-3"
        :style="{
          top:       pendingPin.y > 55 ? 'auto' : '100%',
          bottom:    pendingPin.y > 55 ? '100%' : 'auto',
          left:      pendingPin.x > 60 ? 'auto' : '50%',
          right:     pendingPin.x > 60 ? '50%'  : 'auto',
          transform: pendingPin.x > 60 ? 'none'  : 'translateX(-50%)',
          marginTop:    pendingPin.y > 55 ? '0'   : '8px',
          marginBottom: pendingPin.y > 55 ? '8px' : '0',
        }">
        <p class="text-[10px] text-[#A08A7A] mb-2">Nuevo pin</p>
        <textarea
          v-model="pinComment"
          rows="3"
          placeholder="Escribe tu anotación..."
          class="w-full text-xs text-[#4A342A] placeholder-[#D7C9B8] border border-[#E8E0D8] rounded-lg p-2 resize-none outline-none focus:border-[#7D5A44]"
          autofocus
        />
        <div class="flex gap-2 mt-2">
          <button
            @click="savePendingPin"
            :disabled="saving || !pinComment.trim()"
            class="flex-1 text-xs bg-[#4A342A] hover:bg-[#7D5A44] text-white rounded-lg py-1.5 transition-colors disabled:opacity-50"
          >
            Guardar
          </button>
          <button
            @click="cancelPending"
            class="text-xs text-[#A08A7A] hover:text-[#4A342A] px-2 transition-colors"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>

  </div>
</template>
