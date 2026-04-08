<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page   = usePage()
const user   = computed(() => page.props.auth.user)
const open   = ref(true)

const nav = [
    { label: 'Dashboard',    icon: '📊', route: 'admin.dashboard' },
    { label: 'Médicos',      icon: '👨‍⚕️', route: 'admin.doctors.index' },
    { label: 'Pacientes',    icon: '🧑‍🤝‍🧑', route: 'admin.patients.index' },
    { label: 'Especialidades', icon: '🏥', route: 'admin.specialities.index' },
    { label: 'Motivos',      icon: '📋', route: 'admin.reasons.index' },
    { label: 'Citas',        icon: '📅', route: 'admin.appointments.index' },
]
</script>

<template>
    <div class="flex min-h-screen bg-gray-100 font-sans">

        <!-- Sidebar -->
        <aside :class="[
    'flex flex-col bg-gray-900 text-white transition-all duration-300',
    open ? 'w-64' : 'w-20'
  ]">

            <!-- Logo / toggle -->
            <div class="flex items-center justify-between px-4 py-5 border-b border-gray-700">
                <span v-if="open" class="font-bold text-lg tracking-wide">🏨Hospital Hospitalario</span>
                <button @click="open = !open"
                        class="ml-auto text-gray-400 hover:text-white transition text-xl leading-none">
                    {{ open ? '<' : '🏨 >' }}
                </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 py-4 space-y-1 px-2">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    :class="[
          'flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition',
          $page.url.startsWith('/' + item.route.replace('admin.','').replace('.index',''))
            ? 'bg-blue-600 text-white font-semibold'
            : 'text-gray-300 hover:bg-gray-700'
        ]"
                >
                    <div v-if="!open">
                        <span class="text-lg">{{ item.icon }}</span>
                    </div>
                    <span v-if="open" class="text-lg">{{ item.icon }}</span>
                    <span v-if="open">{{ item.label }}</span>
                </Link>
            </nav>

            <!-- User -->
            <div class="border-t border-gray-700 px-4 py-4">
                <div v-if="open" class="text-xs text-gray-400 mb-1 truncate">{{ user.email }}</div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="flex items-center gap-2 text-sm text-gray-300 hover:text-red-400 transition"
                >
                    <span>🚪</span>
                    <span v-if="open">Cerrar sesión</span>
                </Link>
            </div>
        </aside>

        <!-- Contenido principal -->
        <div class="flex-1 flex flex-col overflow-hidden">

            <!-- Topbar -->
            <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                <div>
                    <h1 class="text-lg font-semibold text-gray-800">
                        <slot name="title">Panel Administrador</slot>
                    </h1>
                    <p v-if="$slots.subtitle" class="text-sm text-gray-400">
                        <slot name="subtitle" />
                    </p>
                </div>
                <div class="text-sm text-gray-500">{{ user.name }}</div>
            </header>

            <!-- Flash -->
            <div v-if="$page.props.flash?.success"
                 class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error"
                 class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                ❌ {{ $page.props.flash.error }}
            </div>

            <!-- Page content -->
            <main class="flex-1 overflow-y-auto p-6">
                <slot />
            </main>

        </div>
    </div>
</template>
