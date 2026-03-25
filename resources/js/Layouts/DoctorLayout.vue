<script setup>
import { ref, computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const open = ref(true)

const nav = [
    { label: 'Mi Agenda', icon: '📅', route: 'doctor.appointments' },
]
</script>

<template>
    <div class="flex min-h-screen bg-gray-100 font-sans">

        <aside :class="['flex flex-col bg-teal-900 text-white transition-all duration-300', open ? 'w-60' : 'w-16']">
            <div class="flex items-center justify-between px-4 py-5 border-b border-teal-700">
                <span v-if="open" class="font-bold text-lg">🩺 Portal Médico</span>
                <button @click="open = !open" class="ml-auto text-teal-300 hover:text-white text-xl">
                    {{ open ? '←' : '→' }}
                </button>
            </div>

            <nav class="flex-1 py-4 space-y-1 px-2">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                    class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-teal-200 hover:bg-teal-700 transition"
                >
                    <span class="text-lg">{{ item.icon }}</span>
                    <span v-if="open">{{ item.label }}</span>
                </Link>
            </nav>

            <div class="border-t border-teal-700 px-4 py-4">
                <div v-if="open" class="text-xs text-teal-400 mb-1 truncate">{{ user.name }}</div>
                <Link href="/logout" method="post" as="button"
                      class="flex items-center gap-2 text-sm text-teal-300 hover:text-red-400 transition">
                    <span>🚪</span><span v-if="open">Salir</span>
                </Link>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm px-6 py-4 flex items-center justify-between">
                <h1 class="text-lg font-semibold text-gray-800">
                    <slot name="title">Mi Agenda</slot>
                </h1>
                <span class="text-sm text-gray-500">{{ user.name }}</span>
            </header>
            <div v-if="$page.props.flash?.success"
                 class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                ✅ {{ $page.props.flash.success }}
            </div>
            <main class="flex-1 overflow-y-auto p-6"><slot /></main>
        </div>
    </div>
</template>
