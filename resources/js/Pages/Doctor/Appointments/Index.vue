<script setup>
import DoctorLayout from '@/Layouts/DoctorLayout.vue'
import { computed, ref } from 'vue'
import FullCalendar from '@fullcalendar/vue3'
import dayGridPlugin  from '@fullcalendar/daygrid'
import timeGridPlugin from '@fullcalendar/timegrid'

const props = defineProps({
    appointments:     Array,
    whatsapp_number:  String,
})

const selected = ref(null)

const events = computed(() => props.appointments.map(a => ({
    id:              String(a.id),
    title:           a.patient_name,
    start:           a.start,
    end:             a.end,
    backgroundColor: a.status === 'confirmed' ? '#0d9488' : '#f59e0b',
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
        right:  'timeGridDay,timeGridWeek,dayGridMonth',
    },
    slotMinTime:   '07:00:00',
    slotMaxTime:   '18:00:00',
    allDaySlot:    false,
    editable:      false,
    selectable:    false,
    height:        'auto',
    events:        events.value,
    eventClick:    (info) => { selected.value = info.event.extendedProps },
}))

const waLink = computed(() => {
    if (!selected.value) return '#'
    const d = new Date(selected.value.start)
    const date = d.toLocaleDateString('es-HN')
    const time = d.toLocaleTimeString('es-HN', { hour: '2-digit', minute: '2-digit' })
    const text = `Solicito revisar la cita del paciente ${selected.value.patient_name} el ${date} a las ${time}.`
    return `https://wa.me/${props.whatsapp_number}?text=${encodeURIComponent(text)}`
})
</script>

<template>
    <DoctorLayout>
        <template #title>Mi Agenda</template>

        <!-- Leyenda + contador -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex gap-4 text-xs text-gray-500">
      <span class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-full bg-teal-600 inline-block"></span> Confirmada
      </span>
                <span class="flex items-center gap-1.5">
        <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span> Pendiente
      </span>
            </div>
            <span class="text-xs text-gray-400">{{ appointments.length }} cita(s)</span>
        </div>

        <!-- Calendario -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <FullCalendar v-bind="calendarOptions" />
        </div>

        <!-- Drawer / modal de detalle -->
        <Teleport to="body">
            <Transition enter-active-class="transition duration-200"
                        enter-from-class="opacity-0 translate-x-4" enter-to-class="opacity-100 translate-x-0"
                        leave-active-class="transition duration-150"
                        leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="selected"
                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
                     @click.self="selected = null">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6">

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-800">Detalle de cita</h3>
                            <button @click="selected = null" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                        </div>

                        <dl class="space-y-3 text-sm text-gray-700 mb-6">
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
                <span :class="['text-xs font-medium px-2.5 py-0.5 rounded-full',
                  selected.status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700']">
                  {{ selected.status === 'confirmed' ? 'Confirmada' : 'Pendiente' }}
                </span>
                                </dd>
                            </div>
                            <div v-if="selected.notes" class="pt-1">
                                <dt class="font-medium text-gray-500 mb-1">Notas</dt>
                                <dd class="text-gray-500 italic text-xs">{{ selected.notes }}</dd>
                            </div>
                        </dl>

                        <a :href="waLink" target="_blank"
                           class="flex items-center justify-center gap-2 w-full bg-green-500 hover:bg-green-600 text-white font-medium py-2.5 rounded-xl transition text-sm">
                            📲 Solicitar modificación al admin
                        </a>

                    </div>
                </div>
            </Transition>
        </Teleport>

    </DoctorLayout>
</template>
