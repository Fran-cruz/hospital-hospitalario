<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import Modal from '@/Components/Modal.vue'
import { ref, reactive } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
    appointments: Array,
    doctors:      Array,
    filters:      Object,
    // filters: { status, doctor_id, from, to }
})

const detail  = ref(null)

const f = reactive({
    status:    props.filters?.status    ?? '',
    doctor_id: props.filters?.doctor_id ?? '',
    from:      props.filters?.from      ?? '',
    to:        props.filters?.to        ?? '',
})

const applyFilters = () =>
    router.get(route('admin.appointments.index'), f, { preserveState: true, replace: true })

const clearFilters = () => {
    f.status = ''; f.doctor_id = ''; f.from = ''; f.to = ''
    applyFilters()
}
</script>

<template>
    <AdminLayout>
        <template #title>Citas — Vista global</template>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-5 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Estado</label>
                <select v-model="f.status"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Todos</option>
                    <option value="pending">Pendiente</option>
                    <option value="confirmed">Confirmada</option>
                    <option value="cancelled">Cancelada</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Médico</label>
                <select v-model="f.doctor_id"
                        class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                    <option value="">Todos</option>
                    <option v-for="d in doctors" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Desde</label>
                <input type="date" v-model="f.from"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Hasta</label>
                <input type="date" v-model="f.to"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <button @click="applyFilters"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                Filtrar
            </button>
            <button @click="clearFilters"
                    class="border border-gray-300 text-gray-500 hover:bg-gray-50 px-4 py-2 rounded-lg text-sm transition">
                Limpiar
            </button>
            <span class="ml-auto text-xs text-gray-400">{{ appointments.length }} resultado(s)</span>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Fecha y hora</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Paciente</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Médico</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Especialidad</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Motivo</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Detalle</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="a in appointments" :key="a.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700 whitespace-nowrap">
                        {{ new Date(a.start_time).toLocaleString('es-HN') }}
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ a.patient }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ a.doctor }}</td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">{{ a.speciality }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ a.reason }}</td>
                    <td class="px-4 py-3 text-center"><StatusBadge :status="a.status" /></td>
                    <td class="px-4 py-3 text-center">
                        <button @click="detail = a"
                                class="text-blue-600 hover:text-blue-800 text-xs font-medium">Ver</button>
                    </td>
                </tr>
                <tr v-if="!appointments.length">
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">Sin citas para los filtros aplicados.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal detalle -->
        <Modal :show="!!detail" title="Detalle de cita" max-width="max-w-md" @close="detail = null">
            <div v-if="detail" class="space-y-3 text-sm text-gray-700">
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Paciente</span>
                    <span>{{ detail.patient }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Médico</span>
                    <span>{{ detail.doctor }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Especialidad</span>
                    <span>{{ detail.speciality }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Motivo</span>
                    <span>{{ detail.reason }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Inicio</span>
                    <span>{{ new Date(detail.start_time).toLocaleString('es-HN') }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Fin</span>
                    <span>{{ new Date(detail.end_time).toLocaleString('es-HN') }}</span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="font-medium text-gray-500">Estado</span>
                    <StatusBadge :status="detail.status" />
                </div>
                <div v-if="detail.notes">
                    <span class="font-medium text-gray-500">Notas</span>
                    <p class="mt-1 text-gray-600 italic">{{ detail.notes }}</p>
                </div>
            </div>
        </Modal>

    </AdminLayout>
</template>
