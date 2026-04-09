import './bootstrap'
import '../css/app.css'

import { createApp, h }       from 'vue'
import { createInertiaApp }   from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue }           from '../../vendor/tightenco/ziggy'
import vuetify                from './plugins/vuetify'

createInertiaApp({
    title: (title) => `${title} — Medi Plus`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(vuetify)       // ← Vuetify aquí
            .mount(el)
    },
    progress: {
        color: '#4f46e5',
    },
})
