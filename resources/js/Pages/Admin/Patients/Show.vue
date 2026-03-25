<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    patient: Object,
    // { id, name, email, phone, birth_date, gender, appointments: [...] }
})
</script>

<template>
    <AdminLayout>
        <template #title>Historial de {{ patient.name }}</template>

        <!-- Info del paciente -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-400 block">Email</span><span class="font-medium">{{ patient.email }}</span></div>
                <div><span class="text-gray-400 block">Teléfono</span><span class="font-medium">{{ patient.phone ?? '—' }}</span></div>
                <div><span class="text-gray-400 block">Fecha nacimiento</span><span class="font-medium">{{ patient.birth_date ?? '—' }}</span></div>
                <div><span class="text-gray-400 block">Género</span>
                    <span class="font-medium">{{ patient.gender === 'M' ? 'Masculino' : patient.gender === 'F' ? 'Femenino' : '—' }}</span>
                </div>
            </div>
        </div>

        <!-- Historial de citas -->
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
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="a in patient.appointments" :key="a.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                        {{ new Date(a.start_time).toLocaleString('es-HN') }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">{{ a.doctor }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ a.speciality }}</td>
                    <td class="px-4 py-3 text-gray-500">{{ a.reason }}</td>
                    <td class="px-4 py-3 text-center"><StatusBadge :status="a.status" /></td>
                </tr>
                <tr v-if="!patient.appointments.length">
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Sin citas registradas.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <Link :href="route('admin.patients.index')"
              class="inline-block mt-4 text-sm text-blue-500 hover:underline">← Volver a pacientes</Link>
    </AdminLayout>
</template>
