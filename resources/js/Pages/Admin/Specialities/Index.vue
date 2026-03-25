<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({ specialities: Array })

const showModal = ref(false)
const isEditing = ref(false)

const form = useForm({ id: null, name: '' })

const openCreate = () => { form.reset(); isEditing.value = false; showModal.value = true }
const openEdit   = (s)  => { form.id = s.id; form.name = s.name; isEditing.value = true; showModal.value = true }

const submit = () => {
    const opts = { onSuccess: () => { showModal.value = false } }
    isEditing.value
        ? form.put(route('admin.specialities.update', form.id), opts)
        : form.post(route('admin.specialities.store'), opts)
}

const del = (id) => {
    if (confirm('¿Eliminar especialidad?')) router.delete(route('admin.specialities.destroy', id))
}
</script>

<template>
    <AdminLayout>
        <template #title>Especialidades</template>

        <div class="flex justify-end mb-4">
            <button @click="openCreate"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                + Nueva especialidad
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Nombre</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="s in specialities" :key="s.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ s.name }}</td>
                    <td class="px-4 py-3 text-center flex justify-center gap-4">
                        <button @click="openEdit(s)" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Editar</button>
                        <button @click="del(s.id)"  class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <Modal :show="showModal" :title="isEditing ? 'Editar especialidad' : 'Nueva especialidad'"
               max-width="max-w-sm" @close="showModal = false">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                    <input v-model="form.name" type="text" placeholder="Ej: Neurología"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
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
