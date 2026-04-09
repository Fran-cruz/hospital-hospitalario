
<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { ref } from 'vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({ appointment: Object })

const showReprogram = ref(false)

const reprogram = useForm({
    reason_id: props.appointment.reason_id ?? '',
    from: '',
    to: '',
})

const confirmForm = useForm({})
const cancelForm  = useForm({})

const today = new Date().toISOString().split('T')[0]

const doConfirm = () => {
    if (confirm('¿Confirmar esta cita?')) {
        confirmForm.post(route('patient.appointments.confirm', props.appointment.id))
    }
}

const doCancel = () => {
    if (confirm('¿Cancelar esta cita? El slot quedará disponible para otros.')) {
        cancelForm.post(route('patient.appointments.cancel', props.appointment.id))
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

            <!-- Card principal -->
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

            <!-- Acciones según estado -->
            <div v-if="appointment.status === 'pending'" class="space-y-3">

                <!-- Confirmar -->
                <button @click="doConfirm" :disabled="confirmForm.processing"
                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2 disabled:opacity-50">
                     Confirmar esta cita
                </button>

                <!-- Reprogramar toggle -->
                <button @click="showReprogram = !showReprogram"
                        class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2">
                     {{ showReprogram ? 'Cancelar reprogramación' : 'Reprogramar' }}
                </button>

                <!-- Panel reprogramar -->
                <Transition
                    enter-active-class="transition duration-200 ease-out"
                    enter-from-class="opacity-0 -translate-y-2"
                    enter-to-class="opacity-100 translate-y-0">
                    <div v-if="showReprogram"
                         class="bg-amber-50 border border-amber-200 rounded-xl p-5 space-y-4">
                        <p class="text-sm text-amber-800 font-medium">
                            Indica el nuevo rango de fechas. La cita actual se cancelará y se asignará un nuevo médico disponible.
                        </p>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Desde</label>
                                <input type="date" v-model="reprogram.from" :min="today"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Hasta</label>
                                <input type="date" v-model="reprogram.to" :min="reprogram.from || today"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" />
                            </div>
                        </div>
                        <p v-if="reprogram.errors.availability"
                           class="text-red-600 text-xs">{{ reprogram.errors.availability }}</p>
                        <button @click="submitReprogram"
                                :disabled="reprogram.processing || !reprogram.from || !reprogram.to"
                                class="w-full bg-amber-500 hover:bg-amber-600 text-white py-2.5 rounded-lg text-sm font-medium transition disabled:opacity-50">
                            {{ reprogram.processing ? 'Procesando...' : 'Confirmar reprogramación' }}
                        </button>
                    </div>
                </Transition>

                <!-- Cancelar -->
                <button @click="doCancel" :disabled="cancelForm.processing"
                        class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-semibold py-3 rounded-xl transition disabled:opacity-50">
                    Cancelar cita
                </button>
            </div>

            <!-- Si está confirmada, solo puede cancelar -->
            <div v-else-if="appointment.status === 'confirmed'">
                <button @click="doCancel" :disabled="cancelForm.processing"
                        class="w-full border border-red-300 text-red-600 hover:bg-red-50 font-semibold py-3 rounded-xl transition disabled:opacity-50">
                    ❌ Cancelar cita confirmada
                </button>
            </div>

            <!-- Cancelada -->
            <div v-else
                 class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-5 text-center text-gray-400 text-sm">
                Esta cita fue cancelada y ya no puede modificarse.
            </div>

            <Link :href="route('patient.appointments')"
                  class="block text-center text-sm text-indigo-500 hover:underline pt-1">
                ← Volver a mis citas
            </Link>

        </div>
    </PatientLayout>
</template>
