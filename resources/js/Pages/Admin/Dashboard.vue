<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
    kpis:        Object,
    byDay:       Array,
    bySpeciality: Array,
    byDoctor:    Array,
})

const filters = reactive({
    from: new Date(Date.now() - 30*86400000).toISOString().split('T')[0],
    to:   new Date().toISOString().split('T')[0],
})

const applyFilters = () =>
    router.get(route('admin.dashboard'), filters, { preserveState: true, replace: true })

// ── Gráfico área: citas por día ──────────────────────────────────────────
const areaChart = computed(() => ({
    series:  [{ name: 'Citas', data: props.byDay.map(d => d.total) }],
    options: {
        chart:   { type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
        stroke:  { curve: 'smooth', width: 2 },
        fill:    { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05 } },
        xaxis:   { categories: props.byDay.map(d => d.date), labels: { rotate: -30, style: { fontSize: '11px' } } },
        colors:  ['#3b82f6'],
        tooltip: { x: { format: 'dd MMM' } },
        grid:    { borderColor: '#f0f0f0' },
    }
}))

// ── Gráfico torta: por especialidad ──────────────────────────────────────
const pieChart = computed(() => ({
    series:  props.bySpeciality.map(s => s.total),
    options: {
        chart:   { type: 'donut' },
        labels:  props.bySpeciality.map(s => s.speciality),
        legend:  { position: 'bottom', fontSize: '12px' },
        colors:  ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16'],
        plotOptions: { pie: { donut: { size: '65%' } } },
    }
}))

// ── Gráfico barras: por médico ────────────────────────────────────────────
const barChart = computed(() => ({
    series: [{ name: 'Citas', data: props.byDoctor.map(d => d.total) }],
    options: {
        chart:       { type: 'bar', toolbar: { show: false } },
        xaxis:       { categories: props.byDoctor.map(d => d.doctor), labels: { style: { fontSize: '11px' } } },
        colors:      ['#10b981'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%' } },
        dataLabels:  { enabled: true, style: { fontSize: '11px' } },
        grid:        { borderColor: '#f0f0f0' },
    }
}))

const cancelColor = computed(() =>
    props.kpis.cancellation_rate > 30 ? 'text-red-500' :
        props.kpis.cancellation_rate > 15 ? 'text-yellow-500' : 'text-green-500'
)

const kpiCards = computed(() => [
    { label: 'Pacientes',        value: props.kpis.total_patients,    icon: '🧑‍🤝‍🧑', color: 'text-blue-600'   },
    { label: 'Médicos',          value: props.kpis.total_doctors,     icon: '👨‍⚕️', color: 'text-teal-600'   },
    { label: 'Citas (período)',  value: props.kpis.total_appointments, icon: '📅', color: 'text-gray-800'   },
    { label: 'Tasa cancelación', value: props.kpis.cancellation_rate + '%', icon: '❌', color: cancelColor.value },
])
</script>

<template>
    <AdminLayout>
        <template #title>Dashboard</template>
        <template #subtitle>Resumen del período seleccionado</template>

        <!-- Filtros -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Desde</label>
                <input type="date" v-model="filters.from"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Hasta</label>
                <input type="date" v-model="filters.to"
                       class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" />
            </div>
            <button @click="applyFilters"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">
                Filtrar
            </button>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div v-for="k in kpiCards" :key="k.label"
                 class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center gap-4">
                <span class="text-3xl">{{ k.icon }}</span>
                <div>
                    <p :class="['text-2xl font-bold', k.color]">{{ k.value }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ k.label }}</p>
                </div>
            </div>
        </div>

        <!-- Estado badges -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-yellow-700">{{ kpis.pending }}</p>
                <p class="text-sm text-yellow-600">Pendientes</p>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-green-700">{{ kpis.confirmed }}</p>
                <p class="text-sm text-green-600">Confirmadas</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                <p class="text-2xl font-bold text-red-700">{{ kpis.cancelled }}</p>
                <p class="text-sm text-red-600">Canceladas</p>
            </div>
        </div>

        <!-- Gráficos fila 1 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-600 mb-4">📈 Citas por día</h2>
                <VueApexCharts type="area" height="240"
                               :series="areaChart.series" :options="areaChart.options" />
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-semibold text-gray-600 mb-4">🥧 Por especialidad</h2>
                <VueApexCharts type="donut" height="240"
                               :series="pieChart.series" :options="pieChart.options" />
            </div>
        </div>

        <!-- Gráfico barras -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <h2 class="text-sm font-semibold text-gray-600 mb-4">📊 Citas por médico</h2>
            <VueApexCharts type="bar" height="240"
                           :series="barChart.series" :options="barChart.options" />
        </div>

    </AdminLayout>
</template>
