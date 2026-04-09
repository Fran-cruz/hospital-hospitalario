<script setup>
import { useForm, Link } from '@inertiajs/vue3'

const form = useForm({
    name:                  '',
    email:                 '',
    password:              '',
    password_confirmation: '',
    phone:                 '',
    birth_date:            '',
    gender:                '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100
            flex items-center justify-center px-4 py-8">

        <div class="w-full max-w-lg">

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
                <p class="text-gray-500 text-sm mt-1">Crear cuenta de paciente</p>
            </div>

            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl p-8">

                <h2 class="text-xl font-semibold text-gray-800 mb-6">Registro de Paciente</h2>

                <div class="space-y-4">

                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre completo <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.name"
                            type="text"
                            placeholder="Juan García"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Correo electrónico <span class="text-red-500">*</span>
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            placeholder="correo@ejemplo.com"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password + Confirmación -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                placeholder="••••••••"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                            />
                            <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">
                                {{ form.errors.password }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar contraseña <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.password_confirmation"
                                type="password"
                                placeholder="••••••••"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                            />
                        </div>
                    </div>

                    <!-- Teléfono + Fecha nacimiento -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Teléfono
                            </label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                placeholder="+504 9999-9999"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                            />
                            <p v-if="form.errors.phone" class="text-red-500 text-xs mt-1">
                                {{ form.errors.phone }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fecha de nacimiento
                            </label>
                            <input
                                v-model="form.birth_date"
                                type="date"
                                :max="new Date().toISOString().split('T')[0]"
                                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                                   focus:outline-none focus:ring-2 focus:ring-indigo-400 transition"
                            />
                            <p v-if="form.errors.birth_date" class="text-red-500 text-xs mt-1">
                                {{ form.errors.birth_date }}
                            </p>
                        </div>
                    </div>

                    <!-- Género -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                        <select
                            v-model="form.gender"
                            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                               focus:outline-none focus:ring-2 focus:ring-indigo-400
                               bg-white transition"
                        >
                            <option value="">— Selecciona —</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                        <p v-if="form.errors.gender" class="text-red-500 text-xs mt-1">
                            {{ form.errors.gender }}
                        </p>
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
                        {{ form.processing ? 'Registrando...' : 'Crear cuenta' }}
                    </button>

                </div>

                <p class="text-center text-sm text-gray-500 mt-6">
                    ¿Ya tienes cuenta?
                    <Link :href="route('login')"
                          class="text-indigo-600 hover:text-indigo-800 font-medium transition">
                        Inicia sesión
                    </Link>
                </p>

            </div>
        </div>
    </div>
</template>
