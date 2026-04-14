<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import axios from 'axios'
import { computed, ref, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
    reasons: Array,
})

const form = useForm({
    reason_id: '',
    date: '',
    time_from: '',
    time_to: '',
    doctor_id: '',
})

const loading = ref(false)
const availableDoctors = ref([])
const availabilityChecked = ref(false)
const noAvailability = ref(false)
const axiosError = ref('')

const grouped = computed(() => {
    const result = {}
    ;(props.reasons ?? []).forEach((reason) => {
        const speciality = typeof reason.speciality === 'string'
            ? reason.speciality
            : (reason.speciality?.name ?? 'Sin especialidad')

        if (!result[speciality]) result[speciality] = []
        result[speciality].push(reason)
    })
    return result
})

const selectedSpeciality = computed(() => {
    if (!form.reason_id) return ''
    const reason = props.reasons?.find((item) => String(item.id) === String(form.reason_id))
    if (!reason) return ''
    return typeof reason.speciality === 'string' ? reason.speciality : (reason.speciality?.name ?? '')
})

const today = new Date().toISOString().split('T')[0]

const toMinutes = (time) => {
    const [hour, minute] = String(time).split(':').map(Number)
    if (Number.isNaN(hour) || Number.isNaN(minute)) return null
    return (hour * 60) + minute
}

const timeErrors = computed(() => {
    const errors = {}
    const fromMinutes = toMinutes(form.time_from)
    const toMinutesValue = toMinutes(form.time_to)

    if (fromMinutes === null || toMinutesValue === null) return errors

    if (fromMinutes >= toMinutesValue) {
        errors.time_to = 'La hora desde debe ser menor que la hora hasta.'
    } else if ((toMinutesValue - fromMinutes) < 60) {
        errors.time_to = 'El rango mínimo debe ser de 1 hora.'
    }

    if (fromMinutes < 480 || toMinutesValue > 1020) {
        errors.time_from = 'El rango debe estar entre 08:00 y 17:00.'
    }

    return errors
})

const isDoctorSelectedFromAvailability = computed(() =>
    availableDoctors.value.some((doctor) => String(doctor.doctor_id) === String(form.doctor_id))
)

const canCheck = computed(() =>
    !!form.reason_id &&
    !!form.date &&
    !!form.time_from &&
    !!form.time_to &&
    !timeErrors.value.time_from &&
    !timeErrors.value.time_to &&
    !loading.value
)

const canSubmit = computed(() =>
    !!form.reason_id &&
    !!form.date &&
    !!form.time_from &&
    !!form.time_to &&
    !timeErrors.value.time_from &&
    !timeErrors.value.time_to &&
    availabilityChecked.value &&
    isDoctorSelectedFromAvailability.value &&
    !form.processing
)

watch(
    () => [form.reason_id, form.date, form.time_from, form.time_to],
    () => {
        availabilityChecked.value = false
        noAvailability.value = false
        axiosError.value = ''
        availableDoctors.value = []
        form.doctor_id = ''
    }
)

const checkAvailability = async () => {
    if (!canCheck.value) return

    loading.value = true
    availabilityChecked.value = false
    noAvailability.value = false
    axiosError.value = ''
    availableDoctors.value = []
    form.doctor_id = ''

    try {
        const { data } = await axios.get(route('api.availability'), {
            params: {
                reason_id: form.reason_id,
                date: form.date,
                time_from: form.time_from,
                time_to: form.time_to,
            },
        })

        availableDoctors.value = data.doctors ?? []
        noAvailability.value = !data.has_availability
        availabilityChecked.value = true
    } catch (error) {
        availabilityChecked.value = true
        noAvailability.value = true
        axiosError.value = error.response?.data?.message ?? 'Error al consultar disponibilidad.'
    } finally {
        loading.value = false
    }
}

const submit = () => {
    form.post(route('patient.appointments.store'))
}
</script>

<template>
    <PatientLayout>
        <template #title>Solicitar nueva cita</template>

        <div class="max-w-2xl space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">1</span>
                    Selecciona el motivo de consulta
                </h2>

                <select
                    v-model="form.reason_id"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"
                >
                    <option value="" disabled>- Selecciona un motivo -</option>
                    <optgroup v-for="(items, specialityName) in grouped" :key="specialityName" :label="specialityName">
                        <option v-for="reason in items" :key="reason.id" :value="reason.id">
                            {{ reason.name }}
                        </option>
                    </optgroup>
                </select>

                <p v-if="form.errors.reason_id" class="text-red-500 text-xs mt-1">{{ form.errors.reason_id }}</p>

                <div
                    v-if="selectedSpeciality"
                    class="mt-3 bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-2 text-sm text-indigo-700"
                >
                    Especialidad: <strong>{{ selectedSpeciality }}</strong>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">2</span>
                    Selecciona fecha y rango horario
                </h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Fecha</label>
                        <input
                            v-model="form.date"
                            type="date"
                            :min="today"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="form.errors.date" class="text-red-500 text-xs mt-1">{{ form.errors.date }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hora desde</label>
                        <input
                            v-model="form.time_from"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="timeErrors.time_from || form.errors.time_from" class="text-red-500 text-xs mt-1">
                            {{ timeErrors.time_from || form.errors.time_from }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Hora hasta</label>
                        <input
                            v-model="form.time_to"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                        />
                        <p v-if="timeErrors.time_to || form.errors.time_to" class="text-red-500 text-xs mt-1">
                            {{ timeErrors.time_to || form.errors.time_to }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold w-6 h-6 rounded-full flex items-center justify-center">3</span>
                    Ver disponibilidad y seleccionar médico
                </h2>

                <button
                    class="w-full border border-indigo-300 text-indigo-600 hover:bg-indigo-50 font-medium py-2.5 rounded-lg text-sm transition disabled:opacity-50"
                    :disabled="!canCheck"
                    @click="checkAvailability"
                >
                    {{ loading ? 'Consultando...' : 'Check availability' }}
                </button>

                <div v-if="availableDoctors.length" class="mt-4 bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-green-700 font-semibold text-sm mb-3">Médicos disponibles</p>
                    <ul class="space-y-2 mb-3">
                        <li
                            v-for="doctor in availableDoctors"
                            :key="doctor.doctor_id"
                            class="text-sm text-green-700 bg-white border border-green-100 rounded-lg px-3 py-2"
                        >
                            {{ doctor.doctor_name }} - {{ new Date(doctor.first_slot).toLocaleString('es-HN') }}
                        </li>
                    </ul>

                    <label class="block text-xs font-medium text-gray-600 mb-1">Selecciona un médico</label>
                    <select
                        v-model="form.doctor_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white"
                    >
                        <option value="" disabled>- Selecciona un médico -</option>
                        <option v-for="doctor in availableDoctors" :key="doctor.doctor_id" :value="doctor.doctor_id">
                            {{ doctor.doctor_name }}
                        </option>
                    </select>
                </div>

                <div
                    v-if="noAvailability && !axiosError"
                    class="mt-4 bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800"
                >
                    No hay disponibilidad en el rango seleccionado.
                </div>

                <div v-if="axiosError" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
                    {{ axiosError }}
                </div>

                <div
                    v-if="form.errors.availability"
                    class="mt-3 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"
                >
                    {{ form.errors.availability }}
                </div>

                <p v-if="availabilityChecked && !form.doctor_id && availableDoctors.length" class="text-xs text-gray-500 mt-3">
                    Debes seleccionar un médico para continuar.
                </p>
                <p v-if="form.errors.doctor_id" class="text-red-500 text-xs mt-1">{{ form.errors.doctor_id }}</p>
            </div>

            <button
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3.5 rounded-xl transition disabled:opacity-50 text-sm"
                :disabled="!canSubmit"
                @click="submit"
            >
                {{ form.processing ? 'Procesando...' : 'Solicitar cita' }}
            </button>
        </div>
    </PatientLayout>
</template>
