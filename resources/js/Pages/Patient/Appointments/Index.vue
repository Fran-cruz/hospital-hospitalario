<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    appointments: Array,
})

const actionRules = {
    pending: ['confirm', 'reprogram', 'cancel'],
    confirmed: ['cancel'],
    cancelled: ['reprogram'],
    completed: [],
}

const can = (status, action) => actionRules[status]?.includes(action) ?? false

const doConfirm = (appointmentId) => {
    if (window.confirm('¿Confirmar esta cita?')) {
        router.post(route('patient.appointments.confirm', appointmentId))
    }
}

const doCancel = (appointmentId) => {
    if (window.confirm('¿Cancelar esta cita?')) {
        router.post(route('patient.appointments.cancel', appointmentId))
    }
}
</script>

<template>
    <PatientLayout>
        <template #title>Mis Citas</template>

        <div class="mb-5">
            <Link
                :href="route('patient.appointments.create')"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition"
            >
                Solicitar nueva cita
            </Link>
        </div>

        <div
            v-if="!appointments.length"
            class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-400"
        >
            <p class="font-medium">No tienes citas registradas.</p>
            <p class="text-sm mt-1">Solicita tu primera cita usando el botón de arriba.</p>
        </div>

        <div v-else class="space-y-3">
            <div
                v-for="appointment in appointments"
                :key="appointment.id"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-indigo-200 transition"
            >
                <div class="text-center min-w-[70px]">
                    <div class="text-2xl font-bold text-indigo-600 leading-none">
                        {{ new Date(appointment.start_time).getDate() }}
                    </div>
                    <div class="text-xs text-gray-400 uppercase">
                        {{ new Date(appointment.start_time).toLocaleString('es-HN', { month: 'short' }) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 font-medium">
                        {{ new Date(appointment.start_time).toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                </div>

                <div class="hidden sm:block w-px bg-gray-200 self-stretch"></div>

                <div class="flex-1">
                    <div class="font-semibold text-gray-800">{{ appointment.reason }}</div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        <span>{{ appointment.doctor }}</span>
                        <span class="mx-2 text-gray-300">·</span>
                        <span>{{ appointment.speciality }}</span>
                    </div>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs font-medium">
                        <button
                            v-if="can(appointment.status, 'confirm')"
                            class="text-green-600 hover:text-green-800"
                            @click="doConfirm(appointment.id)"
                        >
                            Confirmar
                        </button>
                        <span v-else class="text-gray-400 cursor-default">Confirmar</span>

                        <span class="text-gray-300">|</span>

                        <Link
                            v-if="can(appointment.status, 'reprogram')"
                            :href="route('patient.appointments.show', appointment.id)"
                            class="text-blue-600 hover:text-blue-800"
                        >
                            Reprogramar
                        </Link>
                        <span v-else class="text-gray-400 cursor-default">Reprogramar</span>

                        <span class="text-gray-300">|</span>

                        <button
                            v-if="can(appointment.status, 'cancel')"
                            class="text-red-600 hover:text-red-800"
                            @click="doCancel(appointment.id)"
                        >
                            Cancelar
                        </button>
                        <span v-else class="text-gray-400 cursor-default">Cancelar</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <StatusBadge :status="appointment.status" />
                    <Link
                        :href="route('patient.appointments.show', appointment.id)"
                        class="text-indigo-600 hover:text-indigo-800 text-sm font-medium whitespace-nowrap"
                    >
                        Ver
                    </Link>
                </div>
            </div>
        </div>
    </PatientLayout>
</template>
