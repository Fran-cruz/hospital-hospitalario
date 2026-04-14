<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

const props = defineProps({
    reasons: Array,
    specialities: Array,
})

const showModal = ref(false)
const isEditing = ref(false)
const search = ref('')

const form = useForm({ id: null, name: '', speciality_id: '' })

const filtered = computed(() =>
    props.reasons.filter((reason) =>
        reason.name.toLowerCase().includes(search.value.toLowerCase()) ||
        reason.speciality.name.toLowerCase().includes(search.value.toLowerCase())
    )
)

const openCreate = () => {
    form.reset()
    isEditing.value = false
    showModal.value = true
}

const openEdit = (reason) => {
    form.id = reason.id
    form.name = reason.name
    form.speciality_id = reason.speciality.id
    isEditing.value = true
    showModal.value = true
}

const submit = () => {
    const opts = { onSuccess: () => { showModal.value = false } }
    if (isEditing.value) {
        form.put(route('admin.reasons.update', form.id), opts)
        return
    }
    form.post(route('admin.reasons.store'), opts)
}

const del = (id) => {
    if (!window.confirm('¿Eliminar este motivo? Esto tambien eliminara sus citas asociadas.')) {
        return
    }
    router.delete(route('admin.reasons.destroy', id))
}
</script>

<template>
    <AdminLayout>
        <template #title>Motivos de cita</template>

        <div class="flex items-center justify-between mb-4">
            <input
                v-model="search"
                type="text"
                placeholder="Buscar motivo o especialidad..."
                class="border border-gray-300 rounded-lg px-4 py-2 text-sm w-72 focus:outline-none focus:ring-2 focus:ring-blue-400"
            />
            <button
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition"
                @click="openCreate"
            >
                + Nuevo motivo
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
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Motivo</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Especialidad</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="reason in filtered" :key="reason.id" class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ reason.name }}</td>
                        <td class="px-4 py-3">
                            <span class="bg-teal-100 text-teal-700 text-xs px-2 py-0.5 rounded-full">
                                {{ reason.speciality.name }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center flex justify-center gap-4">
                            <button class="text-blue-600 hover:text-blue-800 text-xs font-medium" @click="openEdit(reason)">
                                Editar
                            </button>
                            <button class="text-red-500 hover:text-red-700 text-xs font-medium" @click="del(reason.id)">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!filtered.length">
                        <td colspan="3" class="px-4 py-8 text-center text-gray-400">Sin resultados.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Modal
            :show="showModal"
            :title="isEditing ? 'Editar motivo' : 'Nuevo motivo'"
            max-width="max-w-sm"
            @close="showModal = false"
        >
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nombre del motivo</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="Ej: Control de diabetes"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Especialidad</label>
                    <select
                        v-model="form.speciality_id"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    >
                        <option value="" disabled>Selecciona...</option>
                        <option v-for="speciality in specialities" :key="speciality.id" :value="speciality.id">
                            {{ speciality.name }}
                        </option>
                    </select>
                    <p v-if="form.errors.speciality_id" class="text-red-500 text-xs mt-1">{{ form.errors.speciality_id }}</p>
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
