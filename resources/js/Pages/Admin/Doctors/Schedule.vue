<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin  from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'

const props = defineProps({
    doctor:       Object,   // { id, name, specialities }
    appointments: Array,
})

const selected = ref(null)

const events = computed(() => props.appointments.map(a => ({
    id:              String(a.id),
    title:           `${a.patient_name}`,
    start:           a.start,
    end:             a.end,
    backgroundColor: a.status === 'confirmed' ? '#16a34a' : a.status === 'pending' ? '#d97706' : '#9ca3af',
    borderColor:     'transparent',
    extendedProps:   a,
})))

const calendarOptions = computed(() => ({
    plugins:       [dayGridPlugin, timeGridPlugin],
    initialView:   'timeGridWeek',
    locale:        'es',
    headerToolbar: {
        left:   'prev,next today',
        center: 'title',
        right:  'dayGridMonth,timeGridWeek,timeGridDay',
    },
    slotMinTime:   '07:00:00',
    slotMaxTime:   '18:00:00',
    allDaySlot:    false,
    editable:      false,
    height:        'auto',
    events:        events.value,
    eventClick:    (info) => { selected.value = info.event.extendedProps },
}))
</script>

<template>
    <AdminLayout>
        <template #title>Agenda — {{ doctor.name }}</template>

        <!-- Especialidades -->
        <div class="flex items-center gap-2 mb-4">
    <span v-for="s in doctor.specialities" :key="s.id"
          class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full">{{ s.name }}</span>
            <Link :href="route('admin.doctors.index')"
                  class="ml-auto text-sm text-blue-500 hover:underline">← Volver</Link>
        </div>

        <!-- Leyenda -->
        <div class="flex gap-4 text-xs text-gray-500 mb-3">
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-600 inline-block"></span> Confirmada</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span> Pendiente</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span> Cancelada</span>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <FullCalendar v-bind="calendarOptions" />
        </div>

        <!-- Modal detalle -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-150" enter-from-class="opacity-0" enter-to-class="opacity-100">
                <div v-if="selected"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                     @click.self="selected = null">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                        <h3 class="font-bold text-gray-800 mb-4">Detalle de cita</h3>
                        <dl class="space-y-2 text-sm text-gray-700">
                            <div class="flex justify-between"><dt class="font-medium text-gray-500">Paciente</dt><dd>{{ selected.patient_name }}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-gray-500">Motivo</dt><dd>{{ selected.reason }}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-gray-500">Especialidad</dt><dd>{{ selected.speciality }}</dd></div>
                            <div class="flex justify-between"><dt class="font-medium text-gray-500">Inicio</dt>
                                <dd>{{ new Date(selected.start).toLocaleString('es-HN') }}</dd></div>
                        </dl>
                        <button @click="selected = null"
                                class="mt-5 w-full border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>
