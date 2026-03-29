<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
    reasons: Array,
    // cada item: { id, name, speciality (string), speciality_id }
})

const form = useForm({
    reason_id: '',
    from: '',
    to: '',
})

// ── Agrupar por especialidad ──────────────────────────────────────────────
const grouped = computed(() => {
    const map = {}
    if (!props.reasons) return map

    props.reasons.forEach(r => {
        const key = typeof r.speciality === 'string'
            ? r.speciality
            : (r.speciality?.name ?? 'Sin especialidad')

        if (!map[key]) map[key] = []
        map[key].push(r)
    })

    return Object.keys(map)
        .sort()
        .reduce((acc, key) => {
            acc[key] = map[key]
            return acc
        }, {})
})

// Especialidad del motivo seleccionado
const selectedSpeciality = computed(() => {
    if (!form.reason_id) return ''
    const found = props.reasons?.find(r => r.id == form.reason_id)
    if (!found) return ''
    return typeof found.speciality === 'string'
        ? found.speciality
        : (found.speciality?.name ?? '')
})

// ── Preview de disponibilidad ─────────────────────────────────────────────
const loading        = ref(false)
const preview        = ref(null)
const noAvailability = ref(false)
const axiosError     = ref(null)

watch([() => form.reason_id, () => form.from, () => form.to], () => {
    preview.value        = null
    noAvailability.value = false
    axiosError.value     = null
})

const checkAvailability = async () => {
    if (!form.reason_id || !form.from || !form.to) return

    loading.value        = true
    preview.value        = null
    noAvailability.value = false
    axiosError.value     = null

    try {
        const { data } = await axios.get('/api/availability', {
            params: {
                reason_id: form.reason_id,
                from:      form.from,
                to:        form.to,
            },
            withCredentials: true,
        })

        preview.value        = data
        noAvailability.value = !data.has_availability

    } catch (err) {
        if (err.response?.status === 401 || err.response?.status === 403) {
            axiosError.value = 'No autorizado. Por favor recarga la página.'
        } else if (err.response?.status === 422) {
            axiosError.value = 'Verifica los datos ingresados.'
        } else {
            axiosError.value = 'Error al consultar disponibilidad. Intenta nuevamente.'
        }
        noAvailability.value = true
    } finally {
        loading.value = false
    }
}

// ── Fechas ────────────────────────────────────────────────────────────────
const today = new Date().toISOString().split('T')[0]

const dateErrors = computed(() => {
    const errors = {}
    if (form.from && form.from < today) {
        errors.from = 'La fecha de inicio no puede ser anterior a hoy.'
    }
    if (form.to && form.from && form.to < form.from) {
        errors.to = 'La fecha de fin no puede ser anterior a la fecha de inicio.'
    }
    return errors
})

const canSubmit = computed(() =>
    form.reason_id &&
    form.from &&
    form.to &&
    !dateErrors.value.from &&
    !dateErrors.value.to &&
    !form.processing
)

const canCheck = computed(() =>
    form.reason_id &&
    form.from &&
    form.to &&
    !dateErrors.value.from &&
    !dateErrors.value.to &&
    !loading.value
)

const submit = () => {
    form.post(route('patient.appointments.store'))
}
</script>

<template>
    <PatientLayout>
        <template #title>Solicitar nueva cita</template>

        <div class="max-w-2xl space-y-4">

            <!-- ── Paso 1: Motivo ─────────────────────────────────────────── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6
                             rounded-full flex items-center justify-center">1</span>
                    ¿Cuál es el motivo de tu consulta?
                </h2>

                <select
                    v-model="form.reason_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                       focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"
                >
                    <option value="" disabled>— Selecciona un motivo —</option>
                    <optgroup
                        v-for="(items, specialityName) in grouped"
                        :key="specialityName"
                        :label="specialityName"
                    >
                        <option
                            v-for="r in items"
                            :key="r.id"
                            :value="r.id"
                        >
                            {{ r.name }}
                        </option>
                    </optgroup>
                </select>

                <p v-if="form.errors.reason_id" class="text-red-500 text-xs mt-1">
                    {{ form.errors.reason_id }}
                </p>

                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div
                        v-if="selectedSpeciality"
                        class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg
                           px-4 py-2 text-sm text-indigo-700 flex items-center gap-2"
                    >
                        <span>🏥</span>
                        <span>Especialidad detectada: <strong>{{ selectedSpeciality }}</strong></span>
                    </div>
                </Transition>
            </div>

            <!-- ── Paso 2: Rango de fechas ───────────────────────────────── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6
                             rounded-full flex items-center justify-center">2</span>
                    ¿En qué rango de fechas prefieres la cita?
                </h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                        <input
                            type="date"
                            v-model="form.from"
                            :min="today"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="dateErrors.from || form.errors.from"
                           class="text-red-500 text-xs mt-1">
                            {{ dateErrors.from || form.errors.from }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                        <input
                            type="date"
                            v-model="form.to"
                            :min="form.from || today"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="dateErrors.to || form.errors.to"
                           class="text-red-500 text-xs mt-1">
                            {{ dateErrors.to || form.errors.to }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Paso 3: Disponibilidad ────────────────────────────────── -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6
                             rounded-full flex items-center justify-center">3</span>
                    Ver disponibilidad
                    <span class="text-gray-400 font-normal text-xs">(opcional)</span>
                </h2>

                <button
                    @click="checkAvailability"
                    :disabled="!canCheck"
                    class="w-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50
                       font-medium py-2.5 rounded-lg text-sm transition disabled:opacity-50
                       flex items-center justify-center gap-2"
                >
                    <svg v-if="loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                    </svg>
                    <span>{{ loading ? 'Buscando...' : '🔍 Consultar disponibilidad' }}</span>
                </button>

                <!-- Hay disponibilidad -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 translate-y-1"
                    enter-to-class="opacity-100 translate-y-0"
                >
                    <div
                        v-if="preview && !noAvailability"
                        class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4"
                    >
                        <p class="text-green-700 font-semibold text-sm mb-3">
                            ✅ Hay disponibilidad en el rango seleccionado
                        </p>
                        <ul class="space-y-2">
                            <li
                                v-for="doc in preview.doctors.filter(d => d.available)"
                                :key="doc.doctor_id"
                                class="text-sm text-green-700 flex items-center gap-2
                                   bg-white border border-green-100 rounded-lg px-3 py-2"
                            >
                                <span class="text-base">👨‍⚕️</span>
                                <span class="font-medium">{{ doc.doctor_name }}</span>
                                <span class="text-gray-300 mx-1">|</span>
                                <span class="text-green-600">
                                {{ new Date(doc.first_slot).toLocaleString('es-HN') }}
                            </span>
                            </li>
                        </ul>
                        <p class="text-xs text-gray-400 mt-3">
                            * El médico se asignará aleatoriamente al confirmar la solicitud.
                        </p>
                    </div>
                </Transition>

                <!-- Sin disponibilidad -->
                <div
                    v-if="noAvailability && !axiosError"
                    class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800"
                >
                    <p>
                        ⚠️ No hay disponibilidad en el rango seleccionado.
                        Intenta ampliar las fechas o
                        <a
                            href="https://wa.me/+50494599288"
                            target="_blank"
                            class="underline font-medium"
                        >
                            contacta al administrador
                        </a>.
                    </p>
                </div>

                <!-- Error axios -->
                <div
                    v-if="axiosError"
                    class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700"
                >
                    ❌ {{ axiosError }}
                </div>

                <!-- Error Laravel -->
                <div
                    v-if="form.errors.availability"
                    class="mt-3 bg-red-50 border border-red-200 text-red-700
                       rounded-lg px-4 py-3 text-sm"
                >
                    {{ form.errors.availability }}
                </div>
            </div>

            <!-- ── Botón solicitar ────────────────────────────────────────── -->
            <button
                @click="submit"
                :disabled="!canSubmit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold
                   py-3.5 rounded-xl transition disabled:opacity-50
                   flex items-center justify-center gap-2 text-sm"
            >
                <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10"
                            stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span>{{ form.processing ? 'Procesando...' : '📅 Solicitar cita' }}</span>
            </button>

        </div>
    </PatientLayout>
</template>
