<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

defineProps({ specialities: Array })

const showModal = ref(false)
const isEditing = ref(false)

const form = useForm({ id: null, name: '' })

const openCreate = () => {
    form.reset()
    isEditing.value = false
    showModal.value = true
}

const openEdit = (speciality) => {
    form.id = speciality.id
    form.name = speciality.name
    isEditing.value = true
    showModal.value = true
}

const submit = () => {
    const opts = { onSuccess: () => { showModal.value = false } }
    if (isEditing.value) {
        form.put(route('admin.specialities.update', form.id), opts)
        return
    }
    form.post(route('admin.specialities.store'), opts)
}

const del = (id) => {
    if (!window.confirm('¿Eliminar especialidad? Esto tambien eliminara sus motivos y citas asociadas.')) {
        return
    }
    router.delete(route('admin.specialities.destroy', id))
}
</script>

<template>
    <AdminLayout>
        <template #title>Especialidades</template>

        <div class="flex justify-end mb-4">
            <button
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                @click="openCreate"
            >
                + Nueva especialidad
            </button>
        </div>

        <div
            v-if="$page.props.errors.delete"
            class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm"
        >
            {{ $page.props.errors.delete }}
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
                    <tr v-for="speciality in specialities" :key="speciality.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ speciality.name }}</td>
                        <td class="px-4 py-3 text-center flex justify-center gap-4">
                            <button class="text-blue-600 hover:text-blue-800 text-xs font-medium" @click="openEdit(speciality)">
                                Editar
                            </button>
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium" @click="del(speciality.id)">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal
            :show="showModal"
            :title="isEditing ? 'Editar especialidad' : 'Nueva especialidad'"
            max-width="max-w-sm"
            @close="showModal = false"
        >
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Ej: Neurologia"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600"
                        @click="showModal = false"
                    >
                        Cancelar
                    </button>
                    <button
                        :disabled="form.processing"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium disabled:opacity-50"
                        @click="submit"
                    >
                        {{ isEditing ? 'Guardar' : 'Crear' }}
                    </button>
                </div>
            </div>
        </Modal>
    </AdminLayout>
</template>
