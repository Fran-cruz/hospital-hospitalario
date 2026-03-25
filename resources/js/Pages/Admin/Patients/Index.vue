<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    patients: Array,
    // [{ id, name, email, phone, birth_date, gender, appointments_count }]
})

const search = ref('')

const filtered = computed(() =>
    props.patients.filter(p =>
        p.name.toLowerCase().includes(search.value.toLowerCase()) ||
        p.email.toLowerCase().includes(search.value.toLowerCase())
    )
)

import { computed } from 'vue'
</script>

<template>
    <AdminLayout>
        <template #title>Pacientes</template>

        <div class="flex items-center justify-between mb-4">
            <input v-model="search" type="text" placeholder="Buscar por nombre o email..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <p class="text-sm text-gray-400">{{ filtered.length }} paciente(s)</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Paciente</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Teléfono</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Fecha nacimiento</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Género</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Citas</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Historial</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="p in filtered" :key="p.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ p.name }}</div>
                        <div class="text-xs text-gray-400">{{ p.email }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ p.phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ p.birth_date ?? '—' }}</td>
                    <td class="px-4 py-3 text-center text-gray-600">
                        {{ p.gender === 'M' ? '♂ Masculino' : p.gender === 'F' ? '♀ Femenino' : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
            <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-medium">
              {{ p.appointments_count }}
            </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <Link :href="route('admin.patients.show', p.id)"
                              class="text-blue-600 hover:text-blue-800 text-xs font-medium">
                            Ver historial
                        </Link>
                    </td>
                </tr>
                <tr v-if="!filtered.length">
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">Sin resultados.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </AdminLayout>
</template>
