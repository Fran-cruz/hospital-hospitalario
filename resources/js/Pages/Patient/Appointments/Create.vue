<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({ reasons: Array })

const form = useForm({
    reason_id: '',
    from: '',
    to: '',
})

// Agrupar motivos por especialidad
const grouped = computed(() => {
    const map = {}
    props.reasons.forEach(r => {
        if (!map[r.speciality]) map[r.speciality] = []
        map[r.speciality].push(r)
    })
    return map
})

const selectedSpeciality = computed(() =>
    props.reasons.find(r => r.id == form.reason_id)?.speciality ?? ''
)

// Preview disponibilidad
const loading        = ref(false)
const preview        = ref(null)
const noAvailability = ref(false)

watch([() => form.reason_id, () => form.from, () => form.to], () => {
    preview.value       = null
    noAvailability.value = false
})

const checkAvailability = async () => {
    if (!form.reason_id || !form.from || !form.to) return
    loading.value        = true
    preview.value        = null
    noAvailability.value = false
    try {
        const { data } = await axios.get('/api/availability', {
            params: { reason_id: form.reason_id, from: form.from, to: form.to }
        })
        preview.value        = data
        noAvailability.value = !data.has_availability
    } catch {
        noAvailability.value = true
    } finally {
        loading.value = false
    }
}

const today = new Date().toISOString().split('T')[0]
</script>

<template>
    <PatientLayout>
        <template #title>Solicitar nueva cita</template>

        <div class="max-w-2xl">

            <!-- Paso 1: Motivo -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">1</span>
                    ¿Cuál es el motivo de tu consulta?
                </h2>

                <select v-model="form.reason_id"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                    <option value="" disabled>Selecciona un motivo...</option>
                    <optgroup v-for="(items, speciality) in grouped" :key="speciality" :label="speciality">
                        <option v-for="r in items" :key="r.id" :value="r.id">{{ r.name }}</option>
                    </optgroup>
                </select>
                <p v-if="form.errors.reason_id" class="text-red-500 text-xs mt-1">{{ form.errors.reason_id }}</p>

                <!-- Especialidad detectada -->
                <div v-if="selectedSpeciality"
                     class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2 text-sm text-indigo-700 flex items-center gap-2">
                    🏥 Especialidad: <strong>{{ selectedSpeciality }}</strong>
                </div>
            </div>

            <!-- Paso 2: Rango de fechas -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">2</span>
                    ¿En qué rango de fechas prefieres la cita?
                </h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                        <input type="date" v-model="form.from" :min="today"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                        <p v-if="form.errors.from" class="text-red-500 text-xs mt-1">{{ form.errors.from }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                        <input type="date" v-model="form.to" :min="form.from || today"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                        <p v-if="form.errors.to" class="text-red-500 text-xs mt-1">{{ form.errors.to }}</p>
                    </div>
                </div>
            </div>

            <!-- Paso 3: Verificar disponibilidad (opcional) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">3</span>
                    Ver disponibilidad (opcional)
                </h2>

                <button @click="checkAvailability"
                        :disabled="!form.reason_id || !form.from || !form.to || loading"
                        class="w-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50 font-medium py-2.5 rounded-lg text-sm transition disabled:opacity-50 flex items-center justify-center gap-2">
                    <span v-if="loading" class="animate-spin">⏳</span>
                    <span v-else>🔍</span>
                    {{ loading ? 'Buscando...' : 'Consultar disponibilidad' }}
                </button>

                <!-- Resultado disponibilidad -->
                <div v-if="preview && !noAvailability"
                     class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-700 font-medium text-sm mb-2">✅ Hay disponibilidad</p>
                    <ul class="space-y-1">
                        <li v-for="doc in preview.doctors.filter(d => d.available)"
                            :key="doc.doctor_id"
                            class="text-sm text-green-600 flex items-center gap-2">
                            <span>👨‍⚕️</span>
                            <span>{{ doc.doctor_name }}</span>
                            <span class="text-gray-400">—</span>
                            <span class="font-medium">{{ new Date(doc.first_slot).toLocaleString('es-HN') }}</span>
                        </li>
                    </ul>
                    <p class="text-xs text-gray-400 mt-2">El médico se asignará al azar entre los disponibles.</p>
                </div>

                <div v-if="noAvailability"
                     class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                    ⚠️ Sin disponibilidad en ese rango. Intenta ampliar las fechas o
                    <a href="https://wa.me/+50498765432" target="_blank" class="underline font-medium">contacta al admin</a>.
                </div>

                <div v-if="form.errors.availability"
                     class="mt-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    {{ form.errors.availability }}
                </div>
            </div>

            <!-- Botón confirmar -->
            <button @click="form.post(route('patient.appointments.store'))"
                    :disabled="form.processing || !form.reason_id || !form.from || !form.to"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl transition disabled:opacity-50 flex items-center justify-center gap-2 text-sm">
                <span v-if="form.processing" class="animate-spin">⏳</span>
                <span v-else>📅</span>
                {{ form.processing ? 'Procesando...' : 'Solicitar cita' }}
            </button>

        </div>
    </PatientLayout>
</template>
