<script setup>
import { ref, computed } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'

const page = usePage()
const user = computed(() => page.props.auth.user)
const rail = ref(false)

const nav = [
    { label: 'Mi Agenda', icon: 'mdi-calendar-month', route: 'doctor.appointments' },
]

const logout = () => router.post(route('logout'))
const isActive = (routeName) => route().current(routeName)
</script>

<template>
    <v-app>
        <!-- Sidebar -->
        <v-navigation-drawer
            v-model:rail="rail"
            permanent
            :width="240"
            :rail-width="60"
            color="teal-darken-4"
        >
            <!-- Logo -->
            <v-list nav>
                <v-list-item
                    prepend-icon="mdi-stethoscope"
                    nav
                    class=""
                    @click="rail = !rail"
                >
                    <v-label v-if="!rail">Portal Médico</v-label>
                    <template #append>
                        <v-btn
                            :icon="rail ? 'mdi-chevron-right' : 'mdi-chevron-left'"
                            variant="text"
                            color="white"
                            size="small"
                        />
                    </template>
                </v-list-item>
            </v-list>

            <v-divider />

            <!-- Navegación -->
            <v-list density="compact" nav class="mt-2">
                <Link
                    v-for="item in nav"
                    :key="item.route"
                    :href="route(item.route)"
                >
                    <v-list-item
                        :prepend-icon="item.icon"
                        :title="item.label"
                        :active="isActive(item.route)"
                        rounded="lg"
                        class="mb-1"
                    />
                </Link>
            </v-list>

            <template #append>
                <v-divider />
                <v-list density="compact" nav class="mb-2 mt-1">
                    <v-list-item
                        v-if="!rail"
                        :subtitle="user?.email"
                        :title="user?.name"
                        prepend-icon="mdi-account-circle"
                    />
                    <v-list-item
                        prepend-icon="mdi-logout"
                        title="Cerrar sesión"
                        rounded="lg"
                        class="text-red-300 cursor-pointer"
                        @click="logout"
                    />
                </v-list>
            </template>
        </v-navigation-drawer>

        <!-- Top bar -->
        <v-app-bar elevation="1" color="white">
            <v-app-bar-title>
                <span class="font-semibold text-gray-800">
                    <slot name="title">Mi Agenda</slot>
                </span>
                <span v-if="$slots.subtitle" class="text-sm text-gray-400 ml-2">
                    <slot name="subtitle" />
                </span>
            </v-app-bar-title>
            <template #append>
                <span class="text-sm text-gray-500 mr-4">{{ user?.name }}</span>
            </template>
        </v-app-bar>

        <!-- Contenido -->
        <v-main>
            <div class="px-6 pt-4">
                <v-alert
                    v-if="$page.props.flash?.success"
                    type="success"
                    variant="tonal"
                    closable
                    class="mb-0"
                >
                    {{ $page.props.flash.success }}
                </v-alert>

                <v-alert
                    v-if="$page.props.flash?.error"
                    type="error"
                    variant="tonal"
                    closable
                    class="mb-0"
                >
                    {{ $page.props.flash.error }}
                </v-alert>
            </div>

            <v-container fluid class="pa-6">
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>
