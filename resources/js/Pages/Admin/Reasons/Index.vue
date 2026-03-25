<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    reasons:    Array,      // [{ id, name, speciality: { id, name } }]
    specialities: Array,
})

const showModal = ref(false)
const isEditing = ref(false)
const search    = ref('')

const form = useForm({ id: null, name: '', speciality_id: '' })

const openCreate = () => { form.reset(); isEditing.value = false; showModal.value = true }
const openEdit   = (r)  => {
    form.id = r.id; form.name = r.name; form.speciality_id = r.speciality.id
    isEditing.value = true; showModal.value = true
}

const submit = () => {
    const opts = { onSuccess: () => { showModal.value = false } }
    isEditing.value
        ? form.put(route('admin.reasons.update', form.id), opts)
        : form.post(route('admin.reasons.store'), opts)
}

const del = (id) => {
    if (confirm('¿Eliminar este motivo?')) router.delete(route('admin.reasons.destroy', id))
}

import { computed } from 'vue'
const filtered = computed(() =>
    props.reasons.filter(r =>
        r.name.toLowerCase().includes(search.value.toLowerCase()) ||
        r.speciality.name.toLowerCase().includes(search.value.toLowerCase())
    )
)
</script>

<template>
    <AdminLayout>
        <template #title>Motivos de cita</template>

        <div class="flex items-center justify-between mb-4">
            <input v-model="search" type="text" placeholder="Buscar motivo o especialidad..."
                   class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-400" />
            <button @click="openCreate"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Nuevo motivo
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Motivo</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Especialidad</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="r in filtered" :key="r.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ r.name }}</td>
                    <td class="px-4 py-3">
                        <span class="bg-teal-100 text-teal-700 text-xs px-2 py-0.5 rounded-full">{{ r.speciality.name }}</span>
                    </td>
                    <td class="px-4 py-3 text-center flex justify-center gap-4">
                        <button @click="openEdit(r)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Editar</button>
                        <button @click="del(r.id)"  class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                    </td>
                </tr>
                <tr v-if="!filtered.length">
                    <td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin resultados.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Editar motivo' : 'Nuevo motivo'"
               max-width="max-w-sm" @close="showModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del motivo</label>
                    <input v-model="form.name" type="text" placeholder="Ej: Control de diabetes"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Especialidad</label>
                    <select v-model="form.speciality_id"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        <option value="" disabled>Selecciona...</option>
                        <option v-for="s in specialities" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                    <p v-if="form.errors.speciality_id" class="text-red-500 text-xs mt-1">{{ form.errors.speciality_id }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600">Cancelar</button>
                    <button @click="submit" :disabled="form.processing"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium disabled:opacity-50">
                        {{ isEditing ? 'Guardar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </Modal>

    </AdminLayout>
</template>
