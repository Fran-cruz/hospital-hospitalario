<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import axios from 'axios'
import { computed, ref, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({ appointment: Object })

const showReprogram = ref(false)
const checkingAvailability = ref(false)
const availabilityChecked = ref(false)
const availabilityError = ref('')
const availableDoctors = ref([])

const reprogram = useForm({
    reason_id: props.appointment.reason_id ?? '',
    date: '',
    time_from: '',
    time_to: '',
    doctor_id: '',
})

const confirmForm = useForm({})
const cancelForm = useForm({})

const today = new Date().toISOString().split('T')[0]

const actionRules = {
    pending: ['confirm', 'reprogram', 'cancel'],
    confirmed: ['cancel'],
    cancelled: ['reprogram'],
    completed: [],
}

const can = (status, action) => actionRules[status]?.includes(action) ?? false

const noAvailability = computed(() =>
    availabilityChecked.value && availableDoctors.value.length === 0 && !availabilityError.value
)

const toMinutes = (time) => {
    const [hour, minute] = String(time).split(':').map(Number)
    if (Number.isNaN(hour) || Number.isNaN(minute)) return null
    return (hour * 60) + minute
}

const timeErrors = computed(() => {
    const errors = {}
    const fromMinutes = toMinutes(reprogram.time_from)
    const toMinutesValue = toMinutes(reprogram.time_to)

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
    availableDoctors.value.some((doctor) => String(doctor.doctor_id) === String(reprogram.doctor_id))
)

const canCheckAvailability = computed(() =>
    !!reprogram.date &&
    !!reprogram.time_from &&
    !!reprogram.time_to &&
    !timeErrors.value.time_from &&
    !timeErrors.value.time_to &&
    !checkingAvailability.value
)

const canSubmitReprogram = computed(() =>
    can(props.appointment.status, 'reprogram') &&
    !!reprogram.date &&
    !!reprogram.time_from &&
    !!reprogram.time_to &&
    !timeErrors.value.time_from &&
    !timeErrors.value.time_to &&
    availabilityChecked.value &&
    isDoctorSelectedFromAvailability.value &&
    !reprogram.processing
)

watch(
    () => [reprogram.date, reprogram.time_from, reprogram.time_to],
    () => {
        availabilityChecked.value = false
        availabilityError.value = ''
        availableDoctors.value = []
        reprogram.doctor_id = ''
    }
)

const doConfirm = () => {
    if (window.confirm('¿Confirmar esta cita?')) {
        confirmForm.post(route('patient.appointments.confirm', props.appointment.id))
    }
}

const doCancel = () => {
    if (window.confirm('¿Cancelar esta cita?')) {
        cancelForm.post(route('patient.appointments.cancel', props.appointment.id))
    }
}

const checkAvailability = async () => {
    if (!canCheckAvailability.value) return

    checkingAvailability.value = true
    availabilityChecked.value = false
    availabilityError.value = ''
    availableDoctors.value = []
    reprogram.doctor_id = ''

    try {
        const { data } = await axios.get(route('api.availability'), {
            params: {
                reason_id: reprogram.reason_id,
                date: reprogram.date,
                time_from: reprogram.time_from,
                time_to: reprogram.time_to,
                appointment_id: props.appointment.id,
            },
        })

        availableDoctors.value = data.doctors ?? []
        availabilityChecked.value = true
    } catch (error) {
        availabilityChecked.value = true
        availabilityError.value = error.response?.data?.message ?? 'No se pudo consultar disponibilidad.'
    } finally {
        checkingAvailability.value = false
    }
}

const submitReprogram = () => {
    reprogram.post(route('patient.appointments.reprogram', props.appointment.id))
}
</script>

<template>
    <PatientLayout>
        <template #title>Detalle de cita</template>

        <div class="max-w-2xl space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ appointment.reason }}</h2>
                        <p class="text-sm text-gray-400 mt-0.5">{{ appointment.speciality }}</p>
                    </div>
                    <StatusBadge :status="appointment.status" />
                </div>

                <dl class="space-y-3 text-sm text-gray-700">
                    <div class="flex justify-between border-b pb-3">
                        <dt class="font-medium text-gray-500">Médico asignado</dt>
                        <dd class="font-semibold">{{ appointment.doctor }}</dd>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <dt class="font-medium text-gray-500">Fecha y hora</dt>
                        <dd>{{ new Date(appointment.start_time).toLocaleString('es-HN') }}</dd>
                    </div>
                    <div class="flex justify-between border-b pb-3">
                        <dt class="font-medium text-gray-500">Duración</dt>
                        <dd>30 minutos</dd>
                    </div>
                    <div v-if="appointment.notes" class="pt-1">
                        <dt class="font-medium text-gray-500 mb-1">Notas</dt>
                        <dd class="text-gray-500 italic text-sm">{{ appointment.notes }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Acciones</h3>
                <div class="flex flex-wrap items-center gap-2 text-sm font-medium">
                    <button
                        v-if="can(appointment.status, 'confirm')"
                        class="text-green-600 hover:text-green-800"
                        @click="doConfirm"
                    >
                        Confirmar
                    </button>
                    <span v-else class="text-gray-400 cursor-default">Confirmar</span>

                    <span class="text-gray-300">|</span>

                    <button
                        v-if="can(appointment.status, 'reprogram')"
                        class="text-blue-600 hover:text-blue-800"
                        @click="showReprogram = !showReprogram"
                    >
                        {{ showReprogram ? 'Cerrar reprogramación' : 'Reprogramar' }}
                    </button>
                    <span v-else class="text-gray-400 cursor-default">Reprogramar</span>

                    <span class="text-gray-300">|</span>

                    <button
                        v-if="can(appointment.status, 'cancel')"
                        class="text-red-600 hover:text-red-800"
                        @click="doCancel"
                    >
                        Cancelar
                    </button>
                    <span v-else class="text-gray-400 cursor-default">Cancelar</span>
                </div>

                <p v-if="$page.props.errors.action" class="text-red-600 text-xs mt-2">{{ $page.props.errors.action }}</p>
            </div>

            <div
                v-if="showReprogram && can(appointment.status, 'reprogram')"
                class="bg-blue-50 border border-blue-200 rounded-xl p-5 space-y-4"
            >
                <p class="text-sm text-blue-800 font-medium">
                    Selecciona fecha y rango horario, consulta disponibilidad y elige un médico.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha</label>
                        <input
                            v-model="reprogram.date"
                            type="date"
                            :min="today"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                        <p v-if="reprogram.errors.date" class="text-red-600 text-xs mt-1">{{ reprogram.errors.date }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hora desde</label>
                        <input
                            v-model="reprogram.time_from"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                        <p v-if="timeErrors.time_from || reprogram.errors.time_from" class="text-red-600 text-xs mt-1">
                            {{ timeErrors.time_from || reprogram.errors.time_from }}
                        </p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hora hasta</label>
                        <input
                            v-model="reprogram.time_to"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                        <p v-if="timeErrors.time_to || reprogram.errors.time_to" class="text-red-600 text-xs mt-1">
                            {{ timeErrors.time_to || reprogram.errors.time_to }}
                        </p>
                    </div>
                </div>

                <button
                    class="w-full border border-blue-300 text-blue-600 hover:bg-blue-100 font-medium py-2.5 rounded-lg text-sm transition disabled:opacity-50"
                    :disabled="!canCheckAvailability"
                    @click="checkAvailability"
                >
                    {{ checkingAvailability ? 'Consultando...' : 'Check availability' }}
                </button>

                <div v-if="availableDoctors.length" class="bg-green-50 border border-green-200 rounded-lg p-4">
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
                        v-model="reprogram.doctor_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white"
                    >
                        <option value="" disabled>- Selecciona un médico -</option>
                        <option v-for="doctor in availableDoctors" :key="doctor.doctor_id" :value="doctor.doctor_id">
                            {{ doctor.doctor_name }}
                        </option>
                    </select>
                </div>

                <p v-if="noAvailability" class="text-amber-800 text-xs bg-amber-50 border border-amber-200 rounded-lg p-3">
                    No hay disponibilidad en el rango seleccionado.
                </p>
                <p v-if="availabilityError" class="text-red-600 text-xs">{{ availabilityError }}</p>
                <p v-if="reprogram.errors.availability" class="text-red-600 text-xs">{{ reprogram.errors.availability }}</p>
                <p v-if="reprogram.errors.doctor_id" class="text-red-600 text-xs">{{ reprogram.errors.doctor_id }}</p>

                <button
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium transition disabled:opacity-50"
                    :disabled="!canSubmitReprogram"
                    @click="submitReprogram"
                >
                    {{ reprogram.processing ? 'Procesando...' : 'Confirmar reprogramación' }}
                </button>
            </div>

            <Link :href="route('patient.appointments')" class="block text-center text-sm text-indigo-500 hover:underline pt-1">
                Volver a mis citas
            </Link>
        </div>
    </PatientLayout>
</template>
