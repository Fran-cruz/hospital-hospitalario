<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'

import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'

const props = defineProps({
    doctor: Object,
    appointments: Array,
})

const selected = ref(null)

const mappedEvents = (props.appointments ?? []).map(a => ({
    id: String(a.id),
    title: a.patient_name ?? 'Paciente',
    start: a.start,
    end: a.end,
    backgroundColor: a.status === 'confirmed' ? '#0d9488' : '#f59e0b',
    borderColor: a.status === 'confirmed' ? '#0f766e' : '#d97706',
    textColor: '#ffffff',
    extendedProps: a,
}))

const handleEventClick = (info) => {
    selected.value = info.event.extendedProps
}

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    locale: esLocale,
    initialView: 'timeGridWeek',
    timeZone: 'America/Tegucigalpa',
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'timeGridDay,timeGridWeek,dayGridMonth',
    },
    slotMinTime: '07:00:00',
    slotMaxTime: '18:00:00',
    allDaySlot: false,
    editable: false,
    selectable: false,
    nowIndicator: true,
    height: 'auto',
    expandRows: true,
    slotDuration: '00:30:00',
    slotLabelInterval: '00:30:00',
    noEventsContent: 'No hay citas en este período',
    events: mappedEvents,
    eventClick: handleEventClick,
}

const closeModal = () => { selected.value = null }
</script>

<template>
    <AdminLayout>
        <template #title>Agenda — {{ doctor.name }}</template>

        <!-- Especialidades + botón volver -->
        <div class="flex items-center gap-3 mb-4 flex-wrap">
        <span
            v-for="s in doctor.specialities"
            :key="s.id"
            class="bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full font-medium"
        >
            {{ s.name }}
        </span>
            <Link
                :href="route('admin.doctors.index')"
                class="ml-auto text-sm text-blue-500 hover:underline"
            >
                ← Volver a médicos
            </Link>
        </div>

        <!-- Leyenda -->
        <div class="flex gap-4 text-xs text-gray-500 mb-3">
        <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-teal-600 inline-block"></span>
            Confirmada
        </span>
            <span class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
            Pendiente
        </span>
        </div>

        <!-- Calendario -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <FullCalendar :options="calendarOptions" />
        </div>

        <!-- Modal detalle -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="selected"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4"
                    @click.self="closeModal"
                >
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800 text-lg">Detalle de cita</h3>
                            <button @click="closeModal"
                                    class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
                        </div>
                        <dl class="space-y-3 text-sm text-gray-700 mb-4">
                            <div class="flex justify-between border-b pb-2">
                                <dt class="font-medium text-gray-500">Paciente</dt>
                                <dd class="font-semibold">{{ selected.patient_name }}</dd>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <dt class="font-medium text-gray-500">Motivo</dt>
                                <dd>{{ selected.reason }}</dd>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <dt class="font-medium text-gray-500">Especialidad</dt>
                                <dd>{{ selected.speciality }}</dd>
                            </div>
                            <div class="flex justify-between border-b pb-2">
                                <dt class="font-medium text-gray-500">Fecha</dt>
                                <dd>{{ new Date(selected.start).toLocaleString('es-HN') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-500">Estado</dt>
                                <dd>
                                <span :class="[
                                    'text-xs font-semibold px-2.5 py-0.5 rounded-full',
                                    selected.status === 'confirmed'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700'
                                ]">
                                    {{ selected.status === 'confirmed' ? 'Confirmada' : 'Pendiente' }}
                                </span>
                                </dd>
                            </div>
                            <div v-if="selected.notes">
                                <dt class="font-medium text-gray-500 mb-1">Notas</dt>
                                <dd class="italic text-gray-500 text-xs">{{ selected.notes }}</dd>
                            </div>
                        </dl>
                        <button @click="closeModal"
                                class="w-full border border-gray-200 text-gray-600
                                   hover:bg-gray-50 py-2 rounded-xl text-sm">
                            Cerrar
                        </button>
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AdminLayout>
</template>
