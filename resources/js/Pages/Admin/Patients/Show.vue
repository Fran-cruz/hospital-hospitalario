<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import Modal from '@/Components/Modal.vue'
import axios from 'axios'
import { computed, reactive, ref, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
    patient: Object,
    doctors: Array,
})

const reprogram = ref(null)
const reprogramSubmitting = ref(false)
const checkingAvailability = ref(false)
const availabilityChecked = ref(false)
const availabilityError = ref('')
const availableDoctors = ref([])

const reprogramForm = reactive({
    date: '',
    time_from: '',
    time_to: '',
    doctor_id: '',
    notes: '',
})

const actionRules = {
    pending: ['confirm', 'reprogram', 'cancel'],
    confirmed: ['cancel'],
    cancelled: ['reprogram'],
    completed: [],
}

const noAvailability = computed(() =>
    availabilityChecked.value && availableDoctors.value.length === 0 && !availabilityError.value
)

const canSubmitReprogram = computed(() =>
    !!reprogram.value &&
    !!reprogramForm.date &&
    !!reprogramForm.time_from &&
    !!reprogramForm.time_to &&
    !reprogramSubmitting.value
)

watch(
    () => [reprogramForm.date, reprogramForm.time_from, reprogramForm.time_to, reprogramForm.doctor_id],
    () => resetAvailability()
)

const can = (status, action) => actionRules[status]?.includes(action) ?? false

const pad = (value) => String(value).padStart(2, '0')
const toDateInput = (value) => `${value.getFullYear()}-${pad(value.getMonth() + 1)}-${pad(value.getDate())}`
const toTimeInput = (value) => `${pad(value.getHours())}:${pad(value.getMinutes())}`

const resetAvailability = () => {
    availableDoctors.value = []
    availabilityChecked.value = false
    availabilityError.value = ''
}

const openReprogramModal = (appointment) => {
    reprogram.value = appointment
    reprogramForm.notes = appointment.notes ?? ''
    reprogramForm.doctor_id = ''
    resetAvailability()

    const start = new Date(String(appointment.start_raw).replace(' ', 'T'))
    const end = new Date(String(appointment.end_raw).replace(' ', 'T'))

    reprogramForm.date = toDateInput(start)
    reprogramForm.time_from = toTimeInput(start)
    reprogramForm.time_to = toTimeInput(end)
}

const closeReprogramModal = () => {
    reprogram.value = null
    reprogramForm.date = ''
    reprogramForm.time_from = ''
    reprogramForm.time_to = ''
    reprogramForm.doctor_id = ''
    reprogramForm.notes = ''
    resetAvailability()
}

const checkAvailability = async () => {
    if (!reprogram.value || !reprogramForm.date || !reprogramForm.time_from || !reprogramForm.time_to) {
        return
    }

    checkingAvailability.value = true
    availabilityError.value = ''
    availableDoctors.value = []

    try {
        const params = {
            reason_id: reprogram.value.appointment_reason_id,
            date: reprogramForm.date,
            time_from: reprogramForm.time_from,
            time_to: reprogramForm.time_to,
            appointment_id: reprogram.value.id,
        }

        if (reprogramForm.doctor_id) {
            params.doctor_id = reprogramForm.doctor_id
        }

        const { data } = await axios.get(route('api.availability'), { params })
        availableDoctors.value = data.doctors ?? []
        availabilityChecked.value = true

        if (
            reprogramForm.doctor_id &&
            !availableDoctors.value.some((doctor) => String(doctor.doctor_id) === String(reprogramForm.doctor_id))
        ) {
            availabilityError.value = 'El médico seleccionado no tiene disponibilidad en ese rango.'
        }
    } catch (error) {
        availabilityChecked.value = true
        availabilityError.value = error.response?.data?.message ?? 'No se pudo consultar la disponibilidad.'
    } finally {
        checkingAvailability.value = false
    }
}

const submitReprogram = () => {
    if (!reprogram.value) return

    reprogramSubmitting.value = true

    router.post(route('admin.appointments.reprogram', reprogram.value.id), reprogramForm, {
        preserveScroll: true,
        onSuccess: () => closeReprogramModal(),
        onFinish: () => {
            reprogramSubmitting.value = false
        },
    })
}
</script>

<template>
    <AdminLayout>
        <template #title>Historial de {{ patient.name }}</template>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-400 block">Email</span><span class="font-medium">{{ patient.email }}</span></div>
                <div><span class="text-gray-400 block">Teléfono</span><span class="font-medium">{{ patient.phone ?? '-' }}</span></div>
                <div><span class="text-gray-400 block">Fecha nacimiento</span><span class="font-medium">{{ patient.birth_date ?? '-' }}</span></div>
                <div>
                    <span class="text-gray-400 block">Género</span>
                    <span class="font-medium">{{ patient.gender === 'M' ? 'Masculino' : patient.gender === 'F' ? 'Femenino' : '-' }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-700">Historial de citas ({{ patient.appointments.length }})</h2>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Fecha</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Médico</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Especialidad</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Motivo</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="appointment in patient.appointments" :key="appointment.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-700 whitespace-nowrap">{{ appointment.start_time }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ appointment.doctor }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ appointment.speciality }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ appointment.reason }}</td>
                        <td class="px-4 py-3 text-center"><StatusBadge :status="appointment.status" /></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center items-center gap-2 text-xs font-medium">
                                <button
                                    v-if="can(appointment.status, 'confirm')"
                                    class="text-green-600 hover:text-green-800"
                                    @click="router.post(route('admin.appointments.confirm', appointment.id))"
                                >
                                    Confirmar
                                </button>
                                <span v-else class="text-gray-400 cursor-default">Confirmar</span>

                                <span class="text-gray-300">|</span>

                                <button
                                    v-if="can(appointment.status, 'reprogram')"
                                    class="text-blue-600 hover:text-blue-800"
                                    @click="openReprogramModal(appointment)"
                                >
                                    Reprogramar
                                </button>
                                <span v-else class="text-gray-400 cursor-default">Reprogramar</span>

                                <span class="text-gray-300">|</span>

                                <button
                                    v-if="can(appointment.status, 'cancel')"
                                    class="text-red-600 hover:text-red-800"
                                    @click="router.post(route('admin.appointments.cancel', appointment.id))"
                                >
                                    Cancelar
                                </button>
                                <span v-else class="text-gray-400 cursor-default">Cancelar</span>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="!patient.appointments.length">
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Sin citas registradas.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p v-if="$page.props.errors.action" class="text-red-600 text-xs mt-3">{{ $page.props.errors.action }}</p>
        <p v-if="$page.props.errors.availability" class="text-red-600 text-xs mt-1">{{ $page.props.errors.availability }}</p>

        <Modal :show="!!reprogram" title="Reprogramar cita" max-width="max-w-md" @close="closeReprogramModal">
            <div v-if="reprogram" class="space-y-3 text-sm text-gray-700">
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Paciente</span>
                    <span>{{ patient.name }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Médico actual</span>
                    <span>{{ reprogram.doctor }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Especialidad</span>
                    <span>{{ reprogram.speciality }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-3">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Fecha</label>
                        <input
                            v-model="reprogramForm.date"
                            type="date"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hora desde</label>
                        <input
                            v-model="reprogramForm.time_from"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Hora hasta</label>
                        <input
                            v-model="reprogramForm.time_to"
                            type="time"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Médico (opcional)</label>
                        <select
                            v-model="reprogramForm.doctor_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                        >
                            <option value="">Auto asignar</option>
                            <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">{{ doctor.name }}</option>
                        </select>
                    </div>
                </div>

                <button
                    class="w-full border border-blue-300 text-blue-600 hover:bg-blue-50 font-medium py-2.5 rounded-lg text-sm transition disabled:opacity-50"
                    :disabled="checkingAvailability || !reprogramForm.date || !reprogramForm.time_from || !reprogramForm.time_to"
                    @click="checkAvailability"
                >
                    {{ checkingAvailability ? 'Consultando...' : 'Check availability' }}
                </button>

                <div v-if="availableDoctors.length" class="bg-green-50 border border-green-200 rounded-lg p-3">
                    <p class="text-green-700 text-xs font-semibold mb-2">Médicos disponibles en el rango:</p>
                    <ul class="space-y-1">
                        <li v-for="doctor in availableDoctors" :key="doctor.doctor_id" class="text-sm text-green-800">
                            {{ doctor.doctor_name }} - {{ new Date(doctor.first_slot).toLocaleString('es-HN') }}
                        </li>
                    </ul>
                </div>

                <p v-if="noAvailability" class="text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3 text-xs">
                    No hay médicos disponibles en el rango seleccionado.
                </p>
                <p v-if="availabilityError" class="text-red-600 text-xs">{{ availabilityError }}</p>

                <div class="mt-3">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Notas</label>
                    <textarea
                        v-model="reprogramForm.notes"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    ></textarea>
                </div>

                <button
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2.5 rounded-lg text-sm font-medium transition disabled:opacity-50"
                    :disabled="!canSubmitReprogram"
                    @click="submitReprogram"
                >
                    {{ reprogramSubmitting ? 'Procesando...' : 'Reprogramar cita' }}
                </button>
            </div>
        </Modal>

        <Link :href="route('admin.patients.index')" class="inline-block mt-4 text-sm text-blue-500 hover:underline">
            Volver a pacientes
        </Link>
    </AdminLayout>
</template>
