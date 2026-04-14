<script setup>
const props = defineProps({ status: String })

const map = {
    pending:   { label: 'Pendiente',   cls: 'bg-yellow-100 text-yellow-800' },
    confirmed: { label: 'Confirmada',  cls: 'bg-green-100  text-green-800'  },
    cancelled: { label: 'Cancelada',   cls: 'bg-red-100    text-red-800'    },
    completed: { label: 'Completada',  cls: 'bg-slate-200  text-slate-700'  },
}
</script>

<template>
  <span :class="['inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full', map[status]?.cls]">
    {{ map[status]?.label ?? status }}
  </span>
</template>
