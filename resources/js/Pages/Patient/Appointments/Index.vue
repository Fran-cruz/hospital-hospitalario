<script setup>
import PatientLayout from '@/Layouts/PatientLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    appointments: Array,
})

const statusOrder = { pending: 0, confirmed: 1, cancelled: 2 }
</script>

<template>
    <PatientLayout>
        <template #title>Mis Citas</template>

        <!-- Acceso rápido -->
        <div class="mb-5">
            <Link :href="route('patient.appointments.create')"
                  class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                ➕ Solicitar nueva cita
            </Link>
        </div>

        <!-- Sin citas -->
        <div v-if="!appointments.length"
             class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-400">
            <p class="text-4xl mb-3">📭</p>
            <p class="font-medium">No tienes citas registradas.</p>
            <p class="text-sm mt-1">Solicita tu primera cita usando el botón de arriba.</p>
        </div>

        <!-- Lista de citas -->
        <div v-else class="space-y-3">
            <div
                v-for="a in appointments"
                :key="a.id"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex flex-col sm:flex-row sm:items-center gap-4 hover:border-indigo-200 transition"
            >
                <!-- Fecha -->
                <div class="text-center min-w-[70px]">
                    <div class="text-2xl font-bold text-indigo-600 leading-none">
                        {{ new Date(a.start_time).getDate() }}
                    </div>
                    <div class="text-xs text-gray-400 uppercase">
                        {{ new Date(a.start_time).toLocaleString('es-HN', { month: 'short' }) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1 font-medium">
                        {{ new Date(a.start_time).toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit' }) }}
                    </div>
                </div>

                <!-- Separador vertical -->
                <div class="hidden sm:block w-px bg-gray-200 self-stretch"></div>

                <!-- Info -->
                <div class="flex-1">
                    <div class="font-semibold text-gray-800">{{ a.reason }}</div>
                    <div class="text-sm text-gray-500 mt-0.5">
                        <span>👨‍⚕️ {{ a.doctor }}</span>
                        <span class="mx-2 text-gray-300">·</span>
                        <span>🏥 {{ a.speciality }}</span>
                    </div>
                </div>

                <!-- Estado + acción -->
                <div class="flex items-center gap-3">
                    <StatusBadge :status="a.status" />
                    <Link :href="route('patient.appointments.show', a.id)"
                          class="text-indigo-600 hover:text-indigo-800 text-sm font-medium whitespace-nowrap">
                        Ver →
                    </Link>
                </div>
            </div>
        </div>

    </PatientLayout>
</template>
