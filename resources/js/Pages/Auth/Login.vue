<script setup>
import { useForm, Link } from '@inertiajs/vue3'

defineProps({
    canResetPassword: Boolean,
    status:           String,
})

const form = useForm({
    email:    '',
    password: '',
    remember: false,
})

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    })
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100
            flex items-center justify-center px-4">

        <div class="w-full max-w-md">

            <!-- Logo / Título -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16
                        bg-indigo-600 rounded-2xl shadow-lg mb-4">
                    <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2
                             0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5
                             10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Medi Plus</h1>
                <p class="text-gray-500 text-sm mt-1">Sistema de Gestión de Citas</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">

                <h2 class="text-xl font-semibold text-gray-800 mb-6">Iniciar Sesión</h2>

                <!-- Status -->
                <div v-if="status"
                     class="mb-4 bg-green-50 border border-green-200 text-green-700
                        rounded-lg px-4 py-3 text-sm">
                    {{ status }}
                </div>

                <div class="space-y-4">

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Correo electrónico
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="username"
                            placeholder="correo@ejemplo.com"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                               focus:border-transparent transition"
                            @keyup.enter="submit"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Contraseña
                        </label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                               focus:border-transparent transition"
                            @keyup.enter="submit"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="rounded border-gray-300 text-indigo-600"
                            />
                            <span class="text-sm text-gray-600">Recordarme</span>
                        </label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="text-sm text-indigo-600 hover:text-indigo-800 transition"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    </div>

                    <!-- Botón -->
                    <button
                        @click="submit"
                        :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold
                           py-2.5 rounded-lg transition disabled:opacity-50
                           flex items-center justify-center gap-2 text-sm mt-2"
                    >
                        <svg v-if="form.processing" class="animate-spin w-4 h-4"
                             fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor"
                                  d="M4 12a8 8 0 018-8v8H4z"/>
                        </svg>
                        {{ form.processing ? 'Ingresando...' : 'Ingresar' }}
                    </button>

                </div>

                <!-- Registro -->
                <p class="text-center text-sm text-gray-500 mt-6">
                    ¿No tienes cuenta?
                    <Link :href="route('register')"
                          class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                        Regístrate aquí
                    </Link>
                </p>

            </div>
        </div>
    </div>
</template>
