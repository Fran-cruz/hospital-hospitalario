<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Modal from '@/Components/Modal.vue'
import { ref } from 'vue'
import { useForm, router, Link } from '@inertiajs/vue3'

const props = defineProps({
    doctors:    Array,   // [{ id, name, email, license_number, bio, specialities[], active }]
    specialities: Array,  // [{ id, name }]
})

// ── Modal ─────────────────────────────────────────────────────────────────
const showModal  = ref(false)
const isEditing  = ref(false)

const form = useForm({
    id:             null,
    name:           '',
    email:          '',
    password:       '',
    license_number: '',
    bio:            '',
    specialities:    [],
    active:         true,
})

const openCreate = () => {
    form.reset()
    isEditing.value = false
    showModal.value = true
}

const openEdit = (doc) => {
    form.id             = doc.id
    form.name           = doc.name
    form.email          = doc.email
    form.password       = ''
    form.license_number = doc.license_number
    form.bio            = doc.bio
    form.specialities    = doc.specialities.map(s => s.id)
    form.active         = doc.active
    isEditing.value     = true
    showModal.value     = true
}

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.doctors.update', form.id), {
            onSuccess: () => { showModal.value = false }
        })
    } else {
        form.post(route('admin.doctors.store'), {
            onSuccess: () => { showModal.value = false }
        })
    }
}

// ── Eliminar ──────────────────────────────────────────────────────────────
const confirmDelete = (id) => {
    if (confirm('¿Eliminar este médico? Esta acción no se puede deshacer.')) {
        router.delete(route('admin.doctors.destroy', id))
    }
}

// ── Toggle activo ─────────────────────────────────────────────────────────
const toggleActive = (doc) => {
    router.patch(route('admin.doctors.update', doc.id), { active: !doc.active },
        { preserveScroll: true })
}
</script>

<template>
    <AdminLayout>
        <template #title>Médicos</template>

        <!-- Acciones top -->
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-500">{{ doctors.length }} médico(s) registrado(s)</p>
            <button @click="openCreate"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                <span>+</span> Nuevo médico
            </button>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Médico</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Licencia</th>
                    <th class="px-4 py-3 text-left font-semibold text-gray-600">Especialidades</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Estado</th>
                    <th class="px-4 py-3 text-center font-semibold text-gray-600">Acciones</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <tr v-for="doc in doctors" :key="doc.id" class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="font-medium text-gray-800">{{ doc.name }}</div>
                        <div class="text-xs text-gray-400">{{ doc.email }}</div>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ doc.license_number }}</td>
                    <td class="px-4 py-3">
            <span v-for="s in doc.specialities" :key="s.id"
                  class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full mr-1 mb-1">
              {{ s.name }}
            </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button @click="toggleActive(doc)"
                                :class="['text-xs px-3 py-1 rounded-full font-medium transition',
                doc.active ? 'bg-green-100 text-green-700 hover:bg-green-200'
                           : 'bg-gray-100 text-gray-500 hover:bg-gray-200']">
                            {{ doc.active ? 'Activo' : 'Inactivo' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <Link :href="route('admin.doctors.schedule', doc.id)"
                                  class="text-teal-600 hover:text-teal-800 text-xs font-medium">Agenda</Link>
                            <button @click="openEdit(doc)"
                                    class="text-blue-600 hover:text-blue-800 text-xs font-medium">Editar</button>
                            <button @click="confirmDelete(doc.id)"
                                    class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                        </div>
                    </td>
                </tr>
                <tr v-if="!doctors.length">
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">Sin médicos registrados.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal crear/editar -->
        <Modal :show="showModal" :title="isEditing ? 'Editar médico' : 'Nuevo médico'" max-width="max-w-xl"
               @close="showModal = false">
            <div class="space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nombre completo</label>
                        <input v-model="form.name" type="text" placeholder="Dr. Juan Pérez"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input v-model="form.email" type="email"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">
                            Contraseña {{ isEditing ? '(dejar vacío = sin cambios)' : '' }}
                        </label>
                        <input v-model="form.password" type="password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Número de licencia</label>
                        <input v-model="form.license_number" type="text" placeholder="CMH-006"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
                        <p v-if="form.errors.license_number" class="text-red-500 text-xs mt-1">{{ form.errors.license_number }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Especialidades</label>
                    <div class="grid grid-cols-2 gap-2 max-h-36 overflow-y-auto border border-gray-200 rounded-lg p-2">
                        <label v-for="s in specialities" :key="s.id"
                               class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer hover:bg-gray-50 px-2 py-1 rounded">
                            <input type="checkbox" :value="s.id" v-model="form.specialities"
                                   class="rounded border-gray-300 text-blue-600" />
                            {{ s.name }}
                        </label>
                    </div>
                    <p v-if="form.errors.specialities" class="text-red-500 text-xs mt-1">{{ form.errors.specialities }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Biografía (opcional)</label>
                    <textarea v-model="form.bio" rows="2"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none" />
                </div>

                <div class="flex items-center gap-2">
                    <input type="checkbox" id="active" v-model="form.active" class="rounded border-gray-300 text-blue-600" />
                    <label for="active" class="text-sm text-gray-700">Médico activo</label>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button @click="showModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button @click="submit" :disabled="form.processing"
                            class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition disabled:opacity-50">
                        {{ isEditing ? 'Guardar cambios' : 'Crear médico' }}
                    </button>
                </div>
            </div>
        </Modal>

    </AdminLayout>
</template>
